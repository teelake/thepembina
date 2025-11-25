<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\ReceiptService;

class OrderController extends Controller
{
    private $orderModel;
    private $paymentModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin']);
        $this->orderModel = new Order();
        $this->paymentModel = new Payment();
    }

    public function index()
    {
        $filters = [
            'status' => $this->get('status'),
            'order_type' => $this->get('order_type'),
            'payment_status' => $this->get('payment_status'),
            'from' => $this->get('from'),
            'to' => $this->get('to'),
            'keyword' => $this->get('keyword')
        ];

        $orders = $this->orderModel->filter($filters, 'created_at DESC', 50);

        $this->render('admin/orders/index', [
            'orders' => $orders,
            'page_title' => 'Orders',
            'current_page' => 'orders',
            'csrfField' => $this->csrf->getTokenField(),
            'filters' => $filters
        ]);
    }

    public function view()
    {
        $id = (int)($this->params['id'] ?? 0);
        $order = $this->orderModel->getWithItems($id);
        if (!$order) {
            $this->redirect('/admin/orders?error=Order not found');
            return;
        }
        $payments = $this->paymentModel->getByOrder($id);

        $this->render('admin/orders/view', [
            'order' => $order,
            'payments' => $payments,
            'page_title' => 'Order ' . htmlspecialchars($order['order_number']),
            'current_page' => 'orders',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/orders');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $order = $this->orderModel->find($id);
        if (!$order) {
            $this->redirect('/admin/orders?error=Order not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/orders/{$id}?error=Invalid security token");
            return;
        }

        $status = $this->post('status');
        $this->orderModel->updateStatus($id, $status);

        $this->redirect("/admin/orders/{$id}?success=Order status updated");
    }

    public function receipt()
    {
        $id = (int)($this->params['id'] ?? 0);
        $order = $this->orderModel->getWithItems($id);
        if (!$order) {
            $this->redirect('/admin/orders?error=Order not found');
            return;
        }
        
        // Ensure items array exists
        if (!isset($order['items']) || empty($order['items'])) {
            $order['items'] = [];
        }
        
        $payments = $this->paymentModel->getByOrder($id);
        if (!$payments) {
            $payments = [];
        }

        try {
            $service = new ReceiptService();
            $pdf = $service->generate($order, $payments);

            // Clear any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="receipt-' . htmlspecialchars($order['order_number']) . '.pdf"');
            header('Content-Length: ' . strlen($pdf));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $pdf;
            exit;
        } catch (\Exception $e) {
            error_log("Receipt generation error: " . $e->getMessage());
            error_log("Receipt generation trace: " . $e->getTraceAsString());
            $this->redirect("/admin/orders/{$id}?error=Failed to generate receipt: " . urlencode($e->getMessage()));
        }
    }

    public function emailReceipt()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/orders');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $order = $this->orderModel->getWithItems($id);
        if (!$order) {
            $this->redirect('/admin/orders?error=Order not found');
            return;
        }
        if (empty($order['email'])) {
            $this->redirect("/admin/orders/{$id}?error=Order has no email address");
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/orders/{$id}?error=Invalid security token");
            return;
        }

        // Ensure items array exists
        if (!isset($order['items']) || empty($order['items'])) {
            $order['items'] = [];
        }

        try {
            $payments = $this->paymentModel->getByOrder($id);
            if (!$payments) {
                $payments = [];
            }
            
            $service = new ReceiptService();
            $pdf = $service->generate($order, $payments);
            $filename = $service->getFilename($order);

            $message = "<p>Hello,</p>";
            $message .= "<p>Please find attached the receipt for order <strong>{$order['order_number']}</strong>.</p>";
            $message .= "<p>Thank you for choosing " . (defined('BUSINESS_NAME') ? BUSINESS_NAME : 'The Pembina Pint and Restaurant') . ".</p>";

            $sent = \App\Core\Email::send(
                $order['email'],
                'Your Receipt - ' . $order['order_number'],
                $message,
                null,
                null,
                [[
                    'name' => $filename,
                    'content' => $pdf,
                    'type' => 'application/pdf'
                ]]
            );

            if ($sent) {
                error_log("Receipt email sent successfully to {$order['email']} for order #{$order['order_number']}");
                $this->redirect("/admin/orders/{$id}?success=Receipt emailed to {$order['email']}");
            } else {
                error_log("Failed to send receipt email to {$order['email']} for order #{$order['order_number']}");
                $this->redirect("/admin/orders/{$id}?error=Unable to send receipt email. Please check email configuration.");
            }
        } catch (\Exception $e) {
            error_log("Email receipt error: " . $e->getMessage());
            error_log("Email receipt trace: " . $e->getTraceAsString());
            $this->redirect("/admin/orders/{$id}?error=Failed to generate or send receipt: " . urlencode($e->getMessage()));
        }
    }
}


