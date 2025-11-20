<?php
/**
 * Square Payment Gateway
 */

namespace App\Core\Payment;

use App\Core\Helper;

class SquareGateway extends BaseGateway
{
    private $apiUrl;
    private $accessToken;
    private $locationId;

    public function __construct($config = [])
    {
        parent::__construct($config);
        
        $this->sandbox = $config['sandbox'] ?? true;
        $this->apiUrl = $this->sandbox 
            ? 'https://connect.squareupsandbox.com' 
            : 'https://connect.squareup.com';
        
        $this->accessToken = $config['access_token'] ?? '';
        $this->locationId = $config['location_id'] ?? '';
    }

    /**
     * Process payment
     * 
     * @param array $data
     * @return array
     */
    public function processPayment($data)
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Square payment gateway is not configured'
            ];
        }

        $amount = (int)($data['amount'] * 100); // Convert to cents
        $idempotencyKey = uniqid();

        $paymentData = [
            'source_id' => $data['source_id'], // Payment token from Square
            'idempotency_key' => $idempotencyKey,
            'amount_money' => [
                'amount' => $amount,
                'currency' => $data['currency'] ?? 'CAD'
            ],
            'location_id' => $this->locationId,
            'reference_id' => $data['order_number'] ?? '',
            'note' => $data['note'] ?? ''
        ];

        $headers = [
            'Square-Version: 2023-10-18',
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ];

        $response = $this->makeRequest(
            $this->apiUrl . '/v2/payments',
            $paymentData,
            'POST',
            $headers
        );

        if ($response['success'] && isset($response['data']['payment'])) {
            $payment = $response['data']['payment'];
            return [
                'success' => $payment['status'] === 'COMPLETED',
                'transaction_id' => $payment['id'] ?? '',
                'message' => $payment['status'] === 'COMPLETED' ? 'Payment successful' : 'Payment failed',
                'data' => $payment
            ];
        }

        $error = $response['data']['errors'][0]['detail'] ?? 'Payment processing failed';
        return [
            'success' => false,
            'message' => $error,
            'data' => $response['data']
        ];
    }

    /**
     * Verify payment
     * 
     * @param string $transactionId
     * @return array
     */
    public function verifyPayment($transactionId)
    {
        $headers = [
            'Square-Version: 2023-10-18',
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ];

        $response = $this->makeRequest(
            $this->apiUrl . '/v2/payments/' . $transactionId,
            [],
            'GET',
            $headers
        );

        if ($response['success'] && isset($response['data']['payment'])) {
            $payment = $response['data']['payment'];
            return [
                'success' => true,
                'status' => $payment['status'],
                'data' => $payment
            ];
        }

        return [
            'success' => false,
            'message' => 'Payment verification failed'
        ];
    }

    /**
     * Refund payment
     * 
     * @param string $transactionId
     * @param float $amount
     * @return array
     */
    public function refundPayment($transactionId, $amount = null)
    {
        $refundData = [
            'idempotency_key' => uniqid(),
            'payment_id' => $transactionId
        ];

        if ($amount !== null) {
            $refundData['amount_money'] = [
                'amount' => (int)($amount * 100),
                'currency' => 'CAD'
            ];
        }

        $headers = [
            'Square-Version: 2023-10-18',
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ];

        $response = $this->makeRequest(
            $this->apiUrl . '/v2/refunds',
            $refundData,
            'POST',
            $headers
        );

        if ($response['success'] && isset($response['data']['refund'])) {
            return [
                'success' => true,
                'refund_id' => $response['data']['refund']['id'],
                'message' => 'Refund processed successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Refund failed'
        ];
    }

    /**
     * Get gateway name
     * 
     * @return string
     */
    public function getName()
    {
        return 'Square';
    }

    /**
     * Check if gateway is enabled
     * 
     * @return bool
     */
    public function isEnabled()
    {
        return !empty($this->accessToken) && !empty($this->locationId);
    }

    /**
     * Get Square application ID for frontend
     * 
     * @return string
     */
    public function getApplicationId()
    {
        return $this->getConfig('app_id', '');
    }
}

