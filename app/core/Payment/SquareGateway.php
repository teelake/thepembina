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

        // Extract detailed error information from Square response
        $errorMessage = 'Payment processing failed';
        $errorCode = '';
        
        if (isset($response['data']['errors']) && is_array($response['data']['errors']) && !empty($response['data']['errors'])) {
            $error = $response['data']['errors'][0];
            $errorCode = $error['code'] ?? '';
            $errorDetail = $error['detail'] ?? '';
            $errorCategory = $error['category'] ?? '';
            
            // Format user-friendly error messages
            if ($errorCode === 'GENERIC_DECLINE') {
                $errorMessage = "Authorization error: 'GENERIC_DECLINE'. This usually means the card was declined. Please check your card details or try a different card.";
            } elseif ($errorCode === 'CVV_FAILURE') {
                $errorMessage = "CVV verification failed. Please check your card's security code and try again.";
            } elseif ($errorCode === 'ADDRESS_VERIFICATION_FAILURE') {
                $errorMessage = "Address verification failed. Please check your billing address and try again.";
            } elseif ($errorCode === 'INSUFFICIENT_FUNDS') {
                $errorMessage = "Insufficient funds. Please use a different payment method.";
            } elseif ($errorCode === 'CARD_NOT_SUPPORTED') {
                $errorMessage = "This card type is not supported. Please use a different card.";
            } elseif ($errorCode === 'EXPIRED_CARD') {
                $errorMessage = "This card has expired. Please use a different card.";
            } elseif ($errorCode === 'INVALID_EXPIRATION') {
                $errorMessage = "Invalid card expiration date. Please check and try again.";
            } elseif ($errorCode === 'INVALID_CARD') {
                $errorMessage = "Invalid card number. Please check your card details and try again.";
            } else {
                $errorMessage = "Payment error: " . ($errorDetail ?: $errorCode ?: 'Unknown error');
            }
            
            error_log("SquareGateway::processPayment() - Error code: {$errorCode}, Category: {$errorCategory}, Detail: {$errorDetail}");
        } else {
            error_log("SquareGateway::processPayment() - Unexpected response format: " . json_encode($response['data'] ?? []));
        }
        
        return [
            'success' => false,
            'message' => $errorMessage,
            'error_code' => $errorCode,
            'data' => $response['data'] ?? []
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

