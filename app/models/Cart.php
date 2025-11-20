<?php
/**
 * Cart Model
 */

namespace App\Models;

use App\Core\Model;

class Cart extends Model
{
    protected $table = 'cart';

    /**
     * Get cart items
     * 
     * @param int|null $userId
     * @param string|null $sessionId
     * @return array
     */
    public function getItems($userId = null, $sessionId = null)
    {
        if ($userId) {
            $stmt = $this->db->prepare("
                SELECT c.*, p.name, p.price, p.image, p.stock_status, p.stock_quantity
                FROM {$this->table} c
                INNER JOIN products p ON c.product_id = p.id
                WHERE c.user_id = :user_id
                ORDER BY c.created_at
            ");
            $stmt->execute(['user_id' => $userId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT c.*, p.name, p.price, p.image, p.stock_status, p.stock_quantity
                FROM {$this->table} c
                INNER JOIN products p ON c.product_id = p.id
                WHERE c.session_id = :session_id
                ORDER BY c.created_at
            ");
            $stmt->execute(['session_id' => $sessionId]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Add item to cart
     * 
     * @param array $data
     * @return int
     */
    public function addItem($data)
    {
        // Check if item already exists
        $existing = $this->findExisting($data);
        if ($existing) {
            // Update quantity
            $newQuantity = $existing['quantity'] + ($data['quantity'] ?? 1);
            return $this->update($existing['id'], ['quantity' => $newQuantity]);
        }
        
        if (isset($data['options']) && is_array($data['options'])) {
            $data['options'] = json_encode($data['options']);
        }
        
        return $this->create($data);
    }

    /**
     * Find existing cart item
     * 
     * @param array $data
     * @return array|null
     */
    private function findExisting($data)
    {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = :product_id";
        $params = ['product_id' => $data['product_id']];
        
        if (isset($data['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $data['user_id'];
        } else {
            $sql .= " AND session_id = :session_id";
            $params['session_id'] = $data['session_id'];
        }
        
        if (isset($data['options'])) {
            $options = is_array($data['options']) ? json_encode($data['options']) : $data['options'];
            $sql .= " AND options = :options";
            $params['options'] = $options;
        } else {
            $sql .= " AND (options IS NULL OR options = '')";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    /**
     * Clear cart
     * 
     * @param int|null $userId
     * @param string|null $sessionId
     * @return bool
     */
    public function clear($userId = null, $sessionId = null)
    {
        if ($userId) {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = :user_id");
            return $stmt->execute(['user_id' => $userId]);
        } else {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE session_id = :session_id");
            return $stmt->execute(['session_id' => $sessionId]);
        }
    }
}

