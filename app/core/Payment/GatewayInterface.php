<?php
/**
 * Payment Gateway Interface
 */

namespace App\Core\Payment;

interface GatewayInterface
{
    /**
     * Process payment
     * 
     * @param array $data Payment data
     * @return array ['success' => bool, 'transaction_id' => string, 'message' => string, 'data' => array]
     */
    public function processPayment($data);

    /**
     * Verify payment
     * 
     * @param string $transactionId
     * @return array
     */
    public function verifyPayment($transactionId);

    /**
     * Refund payment
     * 
     * @param string $transactionId
     * @param float $amount
     * @return array
     */
    public function refundPayment($transactionId, $amount = null);

    /**
     * Get gateway name
     * 
     * @return string
     */
    public function getName();

    /**
     * Check if gateway is enabled
     * 
     * @return bool
     */
    public function isEnabled();
}

