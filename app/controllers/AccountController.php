<?php
/**
 * Account Controller
 * Handles customer account pages (orders, profile, addresses)
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\ReceiptService;
use App\Core\Helper;

class AccountController extends Controller
{
    private $orderModel;
    private $paymentModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireAuth();
        $this->orderModel = new Order();
        $this->paymentModel = new Payment();
    }

    /**
     * Account dashboard
     */
    public function index()
    {
        $userId = $this->getUserId();
        
        // Get recent orders
        $recentOrders = $this->orderModel->getUserOrders($userId, 5);
        
        $this->render('account/index', [
            'recentOrders' => $recentOrders,
            'page_title' => 'My Account',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    /**
     * Order history
     */
    public function orders()
    {
        $userId = $this->getUserId();
        $orders = $this->orderModel->getUserOrders($userId);
        
        $this->render('account/orders', [
            'orders' => $orders,
            'page_title' => 'My Orders',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    /**
     * View single order
     */
    public function viewOrder()
    {
        $userId = $this->getUserId();
        $orderId = (int)($this->params['id'] ?? 0);
        
        $order = $this->orderModel->getWithItems($orderId);
        
        if (!$order) {
            $this->redirect('/account/orders?error=Order not found');
            return;
        }
        
        // Verify order belongs to user
        if ($order['user_id'] != $userId) {
            $this->redirect('/account/orders?error=Access denied');
            return;
        }
        
        $payments = $this->paymentModel->getByOrder($orderId);
        
        $this->render('account/order-view', [
            'order' => $order,
            'payments' => $payments,
            'page_title' => 'Order ' . htmlspecialchars($order['order_number']),
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    /**
     * Download receipt
     */
    public function receipt()
    {
        $userId = $this->getUserId();
        $orderId = (int)($this->params['id'] ?? 0);
        
        $order = $this->orderModel->getWithItems($orderId);
        
        if (!$order) {
            $this->redirect('/account/orders?error=Order not found');
            return;
        }
        
        // Verify order belongs to user
        if ($order['user_id'] != $userId) {
            $this->redirect('/account/orders?error=Access denied');
            return;
        }
        
        try {
            $payments = $this->paymentModel->getByOrder($orderId);
            $service = new ReceiptService();
            $pdf = $service->generate($order, $payments);

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="receipt-' . $order['order_number'] . '.pdf"');
            header('Content-Length: ' . strlen($pdf));
            echo $pdf;
            exit;
        } catch (\Exception $e) {
            error_log("Receipt generation error: " . $e->getMessage());
            $this->redirect("/account/orders/{$orderId}?error=Failed to generate receipt");
        }
    }

    /**
     * Profile page
     */
    public function profile()
    {
        $userId = $this->getUserId();
        $userModel = new \App\Models\User();
        $user = $userModel->find($userId);
        
        if (!$user) {
            $this->redirect('/logout');
            return;
        }
        
        $this->render('account/profile', [
            'user' => $user,
            'page_title' => 'My Profile',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/account/profile');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/account/profile?error=Invalid security token');
            return;
        }

        $userId = $this->getUserId();
        $userModel = new \App\Models\User();
        
        $data = [
            'first_name' => $this->post('first_name'),
            'last_name' => $this->post('last_name'),
            'email' => $this->post('email'),
            'phone' => $this->post('phone')
        ];
        
        // Update password if provided
        $password = $this->post('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        if ($userModel->update($userId, $data)) {
            // Update session
            $_SESSION['user_email'] = $data['email'];
            $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
            
            $this->redirect('/account/profile?success=Profile updated successfully');
        } else {
            $this->redirect('/account/profile?error=Failed to update profile');
        }
    }

    /**
     * Addresses page
     */
    public function addresses()
    {
        $this->render('account/addresses', [
            'page_title' => 'My Addresses',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    /**
     * Save address
     */
    public function saveAddress()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/account/addresses');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/account/addresses?error=Invalid security token');
            return;
        }

        // Address saving logic here
        $this->redirect('/account/addresses?success=Address saved successfully');
    }
}


