<?php
/**
 * Order Controller (Public)
 * Handles public order tracking
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Core\Helper;

class OrderController extends Controller
{
    private $orderModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->orderModel = new Order();
    }

    /**
     * Track order by order number and email
     */
    public function track()
    {
        $orderNumber = $this->get('order_number', '');
        $email = $this->get('email', '');
        
        // If form submitted via POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderNumber = $this->post('order_number', '');
            $email = $this->post('email', '');
        }
        
        $order = null;
        $error = null;
        
        if (!empty($orderNumber) && !empty($email)) {
            // Find order by order number
            $order = $this->orderModel->findByOrderNumber($orderNumber);
            
            if ($order) {
                // Verify email matches
                if (strtolower(trim($order['email'])) !== strtolower(trim($email))) {
                    $error = 'The email address does not match this order. Please check and try again.';
                    $order = null;
                } else {
                    // Get order items
                    $order = $this->orderModel->getWithItems($order['id']);
                }
            } else {
                $error = 'Order not found. Please check your order number and try again.';
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = 'Please provide both order number and email address.';
        }
        
        $data = [
            'order' => $order,
            'error' => $error,
            'order_number' => $orderNumber,
            'email' => $email,
            'page_title' => 'Track Your Order',
            'csrfField' => $this->csrf->getTokenField()
        ];
        
        $this->render('order/track', $data);
    }

    /**
     * Download receipt PDF (public - requires order_number + email verification)
     */
    public function receipt()
    {
        $orderNumber = $this->get('order_number', '');
        $email = $this->get('email', '');
        
        if (empty($orderNumber) || empty($email)) {
            $this->redirect('/track-order?error=' . urlencode('Order number and email are required to download receipt.'));
            return;
        }
        
        // Find order by order number
        $order = $this->orderModel->findByOrderNumber($orderNumber);
        
        if (!$order) {
            $this->redirect('/track-order?error=' . urlencode('Order not found. Please check your order number.'));
            return;
        }
        
        // Verify email matches
        if (strtolower(trim($order['email'])) !== strtolower(trim($email))) {
            $this->redirect('/track-order?error=' . urlencode('The email address does not match this order.'));
            return;
        }
        
        // Get order with items
        $orderWithItems = $this->orderModel->getWithItems($order['id']);
        
        if (!$orderWithItems) {
            $this->redirect('/track-order?error=' . urlencode('Unable to load order details.'));
            return;
        }
        
        try {
            // Get payments for receipt
            $paymentModel = new \App\Models\Payment();
            $payments = $paymentModel->getByOrder($order['id']);
            
            // Generate PDF receipt
            $receiptService = new \App\Services\ReceiptService();
            $pdf = $receiptService->generate($orderWithItems, $payments);
            $filename = $receiptService->getFilename($orderWithItems);
            
            // Clear any output buffers
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Output PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . htmlspecialchars($filename) . '"');
            header('Content-Length: ' . strlen($pdf));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $pdf;
            exit;
        } catch (\Exception $e) {
            error_log("OrderController::receipt() - Failed to generate receipt: " . $e->getMessage());
            $this->redirect('/track-order?order_number=' . urlencode($orderNumber) . '&email=' . urlencode($email) . '&error=' . urlencode('Failed to generate receipt. Please try again.'));
        }
    }
}






