<?php
/**
 * Payment Controller
 * Handles payment processing
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Core\Payment\GatewayFactory;
use App\Core\Helper;
use App\Core\AuditTrail;

class PaymentController extends Controller
{
    private $orderModel;
    private $paymentModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->orderModel = new Order();
        $this->paymentModel = new Payment();
    }

    /**
     * Payment page (shows payment form)
     */
    public function index()
    {
        $orderId = (int)($this->get('order_id', 0));
        
        // Get from session if not in URL
        if (!$orderId && isset($_SESSION['pending_order_id'])) {
            $orderId = $_SESSION['pending_order_id'];
        }
        
        $order = $this->orderModel->getWithItems($orderId);
        
        if (!$order) {
            $this->redirect('/checkout?error=Order not found');
            return;
        }

        // Check if already paid
        if ($order['payment_status'] === 'paid') {
            $this->redirect('/payment/success?order_id=' . $orderId);
            return;
        }

        // Ensure order has required fields with defaults
        if (!isset($order['total']) || $order['total'] <= 0) {
            error_log("Payment page: Order #{$orderId} has invalid total: " . ($order['total'] ?? 'null'));
            $this->redirect('/checkout?error=Order total is invalid');
            return;
        }

        $data = [
            'order' => $order,
            'page_title' => 'Complete Payment',
            'csrfField' => $this->csrf->getTokenField()
        ];

        $this->render('checkout/payment', $data);
    }

    /**
     * Process payment
     */
    public function process()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/checkout');
            return;
        }

        $orderId = (int)($this->get('order_id', 0));
        $order = $this->orderModel->getWithItems($orderId);
        
        if (!$order) {
            $this->redirect('/checkout?error=Order not found');
            return;
        }

        // Check if order is already paid
        if ($order['payment_status'] === 'paid') {
            $this->redirect('/payment/success?order_id=' . $orderId);
            return;
        }

        // Get payment gateway (default to Square)
        $gatewayName = $this->post('gateway', 'square');
        $gateway = GatewayFactory::create($gatewayName);
        
        if (!$gateway || !$gateway->isEnabled()) {
            $this->redirect('/checkout?error=Payment gateway not available');
            return;
        }

        // Prepare payment data
        $paymentData = [
            'amount' => $order['total'],
            'currency' => $order['currency'],
            'order_number' => $order['order_number'],
            'source_id' => $this->post('source_id'), // Payment token from Square
            'note' => "Order #{$order['order_number']}"
        ];

        // Process payment
        $result = $gateway->processPayment($paymentData);

        if ($result['success']) {
            // Update order
            $this->orderModel->update($orderId, [
                'payment_status' => 'paid',
                'payment_method' => $gatewayName,
                'payment_gateway' => $gatewayName,
                'payment_transaction_id' => $result['transaction_id'],
                'status' => 'confirmed'
            ]);

            // Create payment record
            $this->paymentModel->createPayment([
                'order_id' => $orderId,
                'gateway' => $gatewayName,
                'transaction_id' => $result['transaction_id'],
                'amount' => $order['total'],
                'currency' => $order['currency'],
                'status' => 'completed',
                'gateway_response' => $result['data'] ?? []
            ]);

            // Log activity
            AuditTrail::log('payment_success', 'payment', $orderId, "Payment successful: {$result['transaction_id']}", [
                'gateway' => $gatewayName,
                'amount' => $order['total']
            ]);

            // Send order confirmation email with receipt
            try {
                $orderWithItems = $this->orderModel->getWithItems($orderId);
                $payments = $this->paymentModel->getByOrder($orderId);
                
                // Generate receipt PDF
                $receiptService = new \App\Services\ReceiptService();
                $pdf = $receiptService->generate($orderWithItems, $payments);
                $filename = $receiptService->getFilename($orderWithItems);
                
                // Send email with receipt attachment
                \App\Core\Email::sendOrderConfirmation($orderWithItems, [
                    [
                        'name' => $filename,
                        'content' => $pdf,
                        'type' => 'application/pdf'
                    ]
                ]);
            } catch (\Exception $e) {
                error_log("Failed to send order confirmation email: " . $e->getMessage());
                // Don't fail the payment if email fails
            }

            // Redirect to success page
            $this->redirect('/payment/success?order_id=' . $orderId);
        } else {
            // Payment failed
            $this->orderModel->update($orderId, [
                'payment_status' => 'failed'
            ]);

            // Create payment record
            $this->paymentModel->createPayment([
                'order_id' => $orderId,
                'gateway' => $gatewayName,
                'transaction_id' => $result['transaction_id'] ?? 'failed',
                'amount' => $order['total'],
                'currency' => $order['currency'],
                'status' => 'failed',
                'gateway_response' => $result['data'] ?? []
            ]);

            AuditTrail::log('payment_failed', 'payment', $orderId, "Payment failed: {$result['message']}", [
                'gateway' => $gatewayName,
                'error' => $result['message']
            ]);

            $this->redirect('/checkout?error=' . urlencode($result['message']));
        }
    }

    /**
     * Payment success page
     */
    public function success()
    {
        $orderId = (int)($this->get('order_id', 0));
        $order = $this->orderModel->getWithItems($orderId);
        
        if (!$order) {
            throw new \Exception("Order not found", 404);
        }

        $data = [
            'order' => $order,
            'page_title' => 'Payment Successful'
        ];

        $this->render('payment/success', $data);
    }

    /**
     * Payment cancel page
     */
    public function cancel()
    {
        $orderId = (int)($this->get('order_id', 0));
        
        if ($orderId) {
            $order = $this->orderModel->find($orderId);
            if ($order && $order['payment_status'] === 'pending') {
                $this->orderModel->update($orderId, ['payment_status' => 'cancelled']);
                AuditTrail::log('payment_cancelled', 'payment', $orderId, 'Payment cancelled by user');
            }
        }

        $data = [
            'page_title' => 'Payment Cancelled'
        ];

        $this->render('payment/cancel', $data);
    }

    /**
     * Square webhook handler
     */
    public function squareWebhook()
    {
        // Square webhook verification and processing
        // This would handle Square's webhook notifications
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        // Verify webhook signature (implement Square's verification)
        // Process webhook events
        
        http_response_code(200);
        echo json_encode(['success' => true]);
        exit;
    }
}

