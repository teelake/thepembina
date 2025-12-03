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
            error_log("PaymentController::process() - Invalid request method: " . $_SERVER['REQUEST_METHOD']);
            $this->redirect('/checkout?error=Invalid request');
            return;
        }

        $orderId = (int)($this->post('order_id', 0));
        error_log("PaymentController::process() - Processing payment for order ID: {$orderId}");
        
        if (!$orderId) {
            error_log("PaymentController::process() - No order ID provided");
            $this->redirect('/checkout?error=Order ID is required');
            return;
        }

        $order = $this->orderModel->getWithItems($orderId);
        
        if (!$order) {
            error_log("PaymentController::process() - Order not found: {$orderId}");
            $this->redirect('/checkout?error=Order not found');
            return;
        }

        // Check if order is already paid
        if ($order['payment_status'] === 'paid') {
            error_log("PaymentController::process() - Order already paid: {$orderId}");
            $this->redirect('/payment/success?order_id=' . $orderId);
            return;
        }

        // Validate source_id (payment token)
        $sourceId = $this->post('source_id', '');
        if (empty($sourceId)) {
            error_log("PaymentController::process() - Missing source_id (payment token) for order: {$orderId}");
            $this->redirect('/payment?order_id=' . $orderId . '&error=' . urlencode('Payment token is missing. Please try again.'));
            return;
        }

        // Get payment gateway (default to Square)
        $gatewayName = $this->post('gateway', 'square');
        error_log("PaymentController::process() - Using gateway: {$gatewayName}");
        
        $gateway = GatewayFactory::create($gatewayName);
        
        if (!$gateway) {
            error_log("PaymentController::process() - Gateway not found: {$gatewayName}");
            $this->redirect('/payment?order_id=' . $orderId . '&error=' . urlencode('Payment gateway not found'));
            return;
        }
        
        if (!$gateway->isEnabled()) {
            error_log("PaymentController::process() - Gateway not enabled: {$gatewayName}");
            $this->redirect('/payment?order_id=' . $orderId . '&error=' . urlencode('Payment gateway is not enabled'));
            return;
        }

        // Prepare payment data
        $paymentData = [
            'amount' => $order['total'],
            'currency' => $order['currency'] ?? 'CAD',
            'order_number' => $order['order_number'],
            'source_id' => $sourceId,
            'note' => "Order #{$order['order_number']}"
        ];

        error_log("PaymentController::process() - Payment data: " . json_encode([
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'],
            'order_number' => $paymentData['order_number'],
            'source_id_length' => strlen($sourceId)
        ]));

        // Process payment
        $result = $gateway->processPayment($paymentData);
        
        error_log("PaymentController::process() - Payment result: " . json_encode([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'No message',
            'transaction_id' => $result['transaction_id'] ?? 'No transaction ID'
        ]));

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

            // Send order confirmation email (email itself serves as receipt)
            try {
                $orderWithItems = $this->orderModel->getWithItems($orderId);
                if ($orderWithItems && !empty($orderWithItems['email'])) {
                    // Send email without PDF attachment - email body contains all receipt details
                    \App\Core\Email::sendOrderConfirmation($orderWithItems);
                }
            } catch (\Exception $e) {
                error_log("PaymentController::process() - Failed to send order confirmation email: " . $e->getMessage());
                // Don't fail the payment if email fails
            }

            // Send order notification email to admin (orders@thepembina.ca)
            try {
                $orderWithItems = $this->orderModel->getWithItems($orderId);
                if ($orderWithItems) {
                    $notificationSent = \App\Core\Email::sendOrderNotification($orderWithItems);
                    if (!$notificationSent) {
                        error_log("PaymentController::process() - Order notification email failed to send for order #{$order['order_number']} to orders@thepembina.ca");
                    } else {
                        error_log("PaymentController::process() - Order notification email sent successfully for order #{$order['order_number']} to orders@thepembina.ca");
                    }
                }
            } catch (\Exception $e) {
                error_log("PaymentController::process() - Failed to send order notification email: " . $e->getMessage());
                error_log("PaymentController::process() - Exception trace: " . $e->getTraceAsString());
                // Don't fail the payment if email fails
            }

            // Clear cart session
            if (isset($_SESSION['cart'])) {
                unset($_SESSION['cart']);
            }
            if (isset($_SESSION['pending_order_id'])) {
                unset($_SESSION['pending_order_id']);
            }

            // Redirect to success page
            error_log("PaymentController::process() - Payment successful, redirecting to success page for order: {$orderId}");
            $this->redirect('/payment/success?order_id=' . $orderId);
        } else {
            // Payment failed
            $errorMessage = $result['message'] ?? ($result['error'] ?? 'Payment processing failed. Please try again.');
            if (empty($errorMessage) || $errorMessage === 'undefined') {
                $errorMessage = 'Payment processing failed. Please check your card details and try again.';
            }
            error_log("PaymentController::process() - Payment failed for order {$orderId}: {$errorMessage}");
            error_log("PaymentController::process() - Full result: " . json_encode($result));
            
            $this->orderModel->update($orderId, [
                'payment_status' => 'failed'
            ]);

            // Create payment record
            $this->paymentModel->createPayment([
                'order_id' => $orderId,
                'gateway' => $gatewayName,
                'transaction_id' => $result['transaction_id'] ?? 'failed',
                'amount' => $order['total'],
                'currency' => $order['currency'] ?? 'CAD',
                'status' => 'failed',
                'gateway_response' => $result['data'] ?? []
            ]);

            AuditTrail::log('payment_failed', 'payment', $orderId, "Payment failed: {$errorMessage}", [
                'gateway' => $gatewayName,
                'error' => $errorMessage
            ]);

            // Redirect back to payment page with error
            $this->redirect('/payment?order_id=' . $orderId . '&error=' . urlencode($errorMessage));
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

