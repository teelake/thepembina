<?php
/**
 * Payment Model
 */

namespace App\Models;

use App\Core\Model;

class Payment extends Model
{
    protected $table = 'payments';

    /**
     * Find payment by transaction ID
     * 
     * @param string $transactionId
     * @return array|null
     */
    public function findByTransactionId($transactionId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE transaction_id = :transaction_id");
        $stmt->execute(['transaction_id' => $transactionId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get order payments
     * 
     * @param int $orderId
     * @return array
     */
    public function getByOrder($orderId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE order_id = :order_id ORDER BY created_at DESC");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Create payment record
     * 
     * @param array $data
     * @return int
     */
    public function createPayment($data)
    {
        if (isset($data['gateway_response']) && is_array($data['gateway_response'])) {
            $data['gateway_response'] = json_encode($data['gateway_response']);
        }
        return $this->create($data);
    }

    /**
     * Get all payments with order details and filters
     * 
     * @param array $filters
     * @param string $orderBy
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllWithOrders($filters = [], $orderBy = 'p.created_at DESC', $limit = null, $offset = null)
    {
        $sql = "SELECT 
                    p.*,
                    o.order_number,
                    o.email,
                    o.status as order_status,
                    o.payment_status as order_payment_status,
                    o.total as order_total
                FROM {$this->table} p
                LEFT JOIN orders o ON p.order_id = o.id
                WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['gateway'])) {
            $sql .= " AND p.gateway = :gateway";
            $params['gateway'] = $filters['gateway'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['from'])) {
            $sql .= " AND DATE(p.created_at) >= :from";
            $params['from'] = $filters['from'];
        }
        
        if (!empty($filters['to'])) {
            $sql .= " AND DATE(p.created_at) <= :to";
            $params['to'] = $filters['to'];
        }
        
        if (!empty($filters['transaction_id'])) {
            $sql .= " AND p.transaction_id LIKE :transaction_id";
            $params['transaction_id'] = '%' . $filters['transaction_id'] . '%';
        }
        
        if (!empty($filters['order_number'])) {
            $sql .= " AND o.order_number LIKE :order_number";
            $params['order_number'] = '%' . $filters['order_number'] . '%';
        }
        
        if (!empty($filters['email'])) {
            $sql .= " AND o.email LIKE :email";
            $params['email'] = '%' . $filters['email'] . '%';
        }
        
        $sql .= " ORDER BY {$orderBy}";
        
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

    /**
     * Get payment statistics
     * 
     * @param array $filters
     * @return array
     */
    public function getStatistics($filters = [])
    {
        $sql = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful_transactions,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_transactions,
                    SUM(CASE WHEN status = 'refunded' THEN amount ELSE 0 END) as total_refunded,
                    AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END) as average_transaction
                FROM {$this->table} p
                LEFT JOIN orders o ON p.order_id = o.id
                WHERE 1=1";
        
        $params = [];
        
        // Apply same filters as getAllWithOrders
        if (!empty($filters['gateway'])) {
            $sql .= " AND p.gateway = :gateway";
            $params['gateway'] = $filters['gateway'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['from'])) {
            $sql .= " AND DATE(p.created_at) >= :from";
            $params['from'] = $filters['from'];
        }
        
        if (!empty($filters['to'])) {
            $sql .= " AND DATE(p.created_at) <= :to";
            $params['to'] = $filters['to'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return [
            'total_transactions' => (int)($result['total_transactions'] ?? 0),
            'total_revenue' => (float)($result['total_revenue'] ?? 0),
            'successful_transactions' => (int)($result['successful_transactions'] ?? 0),
            'failed_transactions' => (int)($result['failed_transactions'] ?? 0),
            'total_refunded' => (float)($result['total_refunded'] ?? 0),
            'average_transaction' => (float)($result['average_transaction'] ?? 0)
        ];
    }
}

