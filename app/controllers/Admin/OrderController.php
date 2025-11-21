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
        $payments = $this->paymentModel->getByOrder($id);

        $service = new ReceiptService();
        $pdf = $service->generate($order, $payments);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="receipt-' . $order['order_number'] . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
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

        $payments = $this->paymentModel->getByOrder($id);
        $service = new ReceiptService();
        $pdf = $service->generate($order, $payments);
        $filename = $service->getFilename($order);

        $message = "<p>Hello,</p>";
        $message .= "<p>Please find attached the receipt for order <strong>{$order['order_number']}</strong>.</p>";
        $message .= "<p>Thank you for choosing " . BUSINESS_NAME . ".</p>";

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
            $this->redirect("/admin/orders/{$id}?success=Receipt emailed to {$order['email']}");
        }

        $this->redirect("/admin/orders/{$id}?error=Unable to send receipt email");
    }
}


