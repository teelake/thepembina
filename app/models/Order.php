<?php
/**
 * Order Model
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Helper;

class Order extends Model
{
    protected $table = 'orders';

    /**
     * Find order by order number
     * 
     * @param string $orderNumber
     * @return array|null
     */
    public function findByOrderNumber($orderNumber)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE order_number = :order_number");
        $stmt->execute(['order_number' => $orderNumber]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get order with items
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithItems($id)
    {
        $order = $this->find($id);
        if (!$order) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $id]);
        $order['items'] = $stmt->fetchAll();

        return $order;
    }

    /**
     * Get user orders
     * 
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getUserOrders($userId, $limit = null, $offset = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Create order
     * 
     * @param array $data
     * @return int Order ID
     */
    public function createOrder($data)
    {
        $data['order_number'] = Helper::generateOrderNumber();
        $orderId = $this->create($data);
        
        // Create order items
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $this->createOrderItem($orderId, $item);
            }
        }
        
        return $orderId;
    }

    /**
     * Create order item
     * 
     * @param int $orderId
     * @param array $item
     * @return int
     */
    public function createOrderItem($orderId, $item)
    {
        $stmt = $this->db->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, price, subtotal, options)
            VALUES (:order_id, :product_id, :product_name, :product_sku, :quantity, :price, :subtotal, :options)
        ");
        return $stmt->execute([
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'product_sku' => $item['product_sku'] ?? null,
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'subtotal' => $item['subtotal'],
            'options' => isset($item['options']) ? json_encode($item['options']) : null
        ]);
    }

    /**
     * Update order status
     * 
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }

    public function filter(array $filters = [], $orderBy = 'created_at DESC', $limit = null, $offset = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $conditions[] = 'status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['order_type'])) {
            $conditions[] = 'order_type = :order_type';
            $params['order_type'] = $filters['order_type'];
        }
        if (!empty($filters['payment_status'])) {
            $conditions[] = 'payment_status = :payment_status';
            $params['payment_status'] = $filters['payment_status'];
        }
        if (!empty($filters['from'])) {
            $conditions[] = 'DATE(created_at) >= :from_date';
            $params['from_date'] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $conditions[] = 'DATE(created_at) <= :to_date';
            $params['to_date'] = $filters['to'];
        }
        if (!empty($filters['keyword'])) {
            $conditions[] = '(order_number LIKE :keyword OR email LIKE :keyword)';
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

