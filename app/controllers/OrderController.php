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
}


