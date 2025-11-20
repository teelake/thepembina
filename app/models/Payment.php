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
}

