<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Payment;

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
        $orders = $this->orderModel->findAll([], 'created_at DESC', 50);

        $this->render('admin/orders/index', [
            'orders' => $orders,
            'page_title' => 'Orders',
            'current_page' => 'orders',
            'csrfField' => $this->csrf->getTokenField()
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
}


