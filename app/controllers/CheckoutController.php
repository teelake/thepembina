<?php
/**
 * Checkout Controller
 * Handles checkout process (guest and registered users)
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Core\TaxCalculator;
use App\Core\Helper;
use App\Core\AuditTrail;

class CheckoutController extends Controller
{
    private $cartModel;
    private $orderModel;
    private $productModel;
    private $taxCalculator;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->cartModel = new Cart();
        $this->orderModel = new Order();
        $this->productModel = new Product();
        $this->taxCalculator = new TaxCalculator();
    }

    /**
     * Get session ID for guest users
     */
    private function getSessionId()
    {
        if (!isset($_SESSION['cart_session_id'])) {
            $_SESSION['cart_session_id'] = session_id();
        }
        return $_SESSION['cart_session_id'];
    }

    /**
     * Checkout page
     */
    public function index()
    {
        $userId = $this->getUserId();
        $sessionId = $userId ? null : $this->getSessionId();
        
        $items = $this->cartModel->getItems($userId, $sessionId);
        
        if (empty($items)) {
            $this->redirect('/cart');
            return;
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $data = [
            'items' => $items,
            'subtotal' => $subtotal,
            'isGuest' => !$userId,
            'page_title' => 'Checkout',
            'csrfField' => $this->csrf->getTokenField()
        ];

        $this->render('checkout/index', $data);
    }

    /**
     * Process checkout
     */
    public function process()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/checkout');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->render('checkout/index', ['error' => 'Invalid security token']);
            return;
        }

        $userId = $this->getUserId();
        $sessionId = $userId ? null : $this->getSessionId();
        
        $items = $this->cartModel->getItems($userId, $sessionId);
        
        if (empty($items)) {
            $this->redirect('/cart');
            return;
        }

        // Get form data
        $orderType = $this->post('order_type'); // 'pickup' or 'delivery'
        $email = $this->post('email');
        $phone = $this->post('phone');
        
        // Billing address
        $billingAddress = [
            'first_name' => $this->post('billing_first_name'),
            'last_name' => $this->post('billing_last_name'),
            'phone' => $phone,
            'address_line1' => $this->post('billing_address_line1'),
            'address_line2' => $this->post('billing_address_line2'),
            'city' => $this->post('billing_city'),
            'province' => $this->post('billing_province'),
            'postal_code' => $this->post('billing_postal_code'),
            'country' => 'Canada'
        ];

        // Shipping address (for delivery)
        $shippingAddress = null;
        if ($orderType === 'delivery') {
            $shippingAddress = [
                'first_name' => $this->post('shipping_first_name', $billingAddress['first_name']),
                'last_name' => $this->post('shipping_last_name', $billingAddress['last_name']),
                'phone' => $phone,
                'address_line1' => $this->post('shipping_address_line1', $billingAddress['address_line1']),
                'address_line2' => $this->post('shipping_address_line2', $billingAddress['address_line2']),
                'city' => $this->post('shipping_city', $billingAddress['city']),
                'province' => $this->post('shipping_province', $billingAddress['province']),
                'postal_code' => $this->post('shipping_postal_code', $billingAddress['postal_code']),
                'country' => 'Canada'
            ];
        }

        // Validation
        $rules = [
            'order_type' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'billing_first_name' => 'required',
            'billing_last_name' => 'required',
            'billing_address_line1' => 'required',
            'billing_city' => 'required',
            'billing_province' => 'required',
            'billing_postal_code' => 'required'
        ];

        if ($orderType === 'delivery') {
            $rules['shipping_first_name'] = 'required';
            $rules['shipping_last_name'] = 'required';
            $rules['shipping_address_line1'] = 'required';
            $rules['shipping_city'] = 'required';
            $rules['shipping_province'] = 'required';
            $rules['shipping_postal_code'] = 'required';
        }

        // If "same as billing" is checked for delivery, copy billing to shipping in $_POST
        if ($orderType === 'delivery' && $this->post('same_as_billing')) {
            $_POST['shipping_first_name'] = $billingAddress['first_name'];
            $_POST['shipping_last_name'] = $billingAddress['last_name'];
            $_POST['shipping_address_line1'] = $billingAddress['address_line1'];
            $_POST['shipping_address_line2'] = $billingAddress['address_line2'];
            $_POST['shipping_city'] = $billingAddress['city'];
            $_POST['shipping_province'] = $billingAddress['province'];
            $_POST['shipping_postal_code'] = $billingAddress['postal_code'];
            
            // Also update the shippingAddress array
            $shippingAddress = $billingAddress;
        }
        
        // Prepare formData for validation (use POST data directly - it has billing_ prefix)
        $formData = $_POST;
        
        // Calculate subtotal for error display
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        if (!$this->validator->validate($formData, $rules)) {
            $this->render('checkout/index', [
                'error' => $this->validator->getFirstError(),
                'items' => $items,
                'subtotal' => $subtotal,
                'isGuest' => !$userId,
                'formData' => $formData,
                'csrfField' => $this->csrf->getTokenField()
            ]);
            return;
        }

        // Calculate totals
        $subtotal = 0;
        $orderItems = [];
        
        foreach ($items as $item) {
            $itemSubtotal = $item['price'] * $item['quantity'];
            $subtotal += $itemSubtotal;
            
            $product = $this->productModel->find($item['product_id']);
            if (!$product) {
                error_log("Product not found: " . $item['product_id']);
                $this->render('checkout/index', [
                    'error' => 'One or more products in your cart are no longer available.',
                    'items' => $items,
                    'subtotal' => $subtotal,
                    'isGuest' => !$userId,
                    'formData' => $formData,
                    'csrfField' => $this->csrf->getTokenField()
                ]);
                return;
            }
            
            $orderItems[] = [
                'product_id' => $item['product_id'],
                'product_name' => $product['name'],
                'product_sku' => $product['sku'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $itemSubtotal,
                'options' => $item['options'] ? json_decode($item['options'], true) : null
            ];
        }

        // Calculate tax
        $provinceCode = $billingAddress['province'];
        $taxInfo = $this->taxCalculator->calculate($subtotal, $provinceCode);
        $taxAmount = $taxInfo['total_tax'];

        // Delivery fee (if delivery)
        $deliveryFee = 0;
        if ($orderType === 'delivery') {
            $deliveryFee = (float)Helper::getSetting('delivery_fee', 0);
        }

        // Total
        $total = $subtotal + $taxAmount + $deliveryFee;

        // Create order
        $orderData = [
            'user_id' => $userId,
            'email' => $email,
            'phone' => $phone,
            'order_type' => $orderType,
            'status' => 'pending',
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $deliveryFee,
            'discount_amount' => 0,
            'total' => $total,
            'currency' => 'CAD',
            'payment_status' => 'pending',
            'billing_address' => json_encode($billingAddress),
            'shipping_address' => $shippingAddress ? json_encode($shippingAddress) : null,
            'delivery_instructions' => $this->post('delivery_instructions'),
            'pickup_time' => $orderType === 'pickup' ? $this->post('pickup_time') : null,
            'items' => $orderItems
        ];

        $orderId = $this->orderModel->createOrder($orderData);
        
        if ($orderId) {
            // Clear cart
            $this->cartModel->clear($userId, $sessionId);
            $this->updateCartCount();
            
            // Log activity
            AuditTrail::log('order_create', 'order', $orderId, "Order created: {$orderData['order_type']}", [
                'order_type' => $orderType,
                'total' => $total,
                'is_guest' => !$userId
            ]);
            
            // Send order invoice email (before payment)
            try {
                $orderWithItems = $this->orderModel->getWithItems($orderId);
                if ($orderWithItems && !empty($orderWithItems['email'])) {
                    $emailSent = \App\Core\Email::sendOrderInvoice($orderWithItems);
                    $orderNumber = $orderWithItems['order_number'] ?? ($orderData['order_number'] ?? 'N/A');
                    if (!$emailSent) {
                        error_log("Order invoice email failed to send for order #{$orderNumber} to {$orderWithItems['email']}");
                    } else {
                        error_log("Order invoice email sent successfully for order #{$orderNumber} to {$orderWithItems['email']}");
                    }
                } else {
                    error_log("Cannot send invoice email: Order email is empty for order ID {$orderId}");
                }
            } catch (\Exception $e) {
                error_log("Failed to send order invoice email: " . $e->getMessage());
                error_log("Exception trace: " . $e->getTraceAsString());
                // Don't fail the order if email fails
            }
            
            // Send order notification email to admin (orders@thepembina.ca)
            try {
                $orderWithItems = $this->orderModel->getWithItems($orderId);
                if ($orderWithItems) {
                    $notificationSent = \App\Core\Email::sendOrderNotification($orderWithItems);
                    $orderNumber = $orderWithItems['order_number'] ?? ($orderData['order_number'] ?? 'N/A');
                    if (!$notificationSent) {
                        error_log("Order notification email failed to send for order #{$orderNumber} to orders@thepembina.ca");
                    } else {
                        error_log("Order notification email sent successfully for order #{$orderNumber} to orders@thepembina.ca");
                    }
                }
            } catch (\Exception $e) {
                error_log("Failed to send order notification email: " . $e->getMessage());
                error_log("Exception trace: " . $e->getTraceAsString());
                // Don't fail the order if email fails
            }
            
            // Store order ID in session for payment
            $_SESSION['pending_order_id'] = $orderId;
            
            // Redirect to payment page
            $this->redirect('/payment?order_id=' . $orderId);
        } else {
            $this->render('checkout/index', [
                'error' => 'Failed to create order. Please try again.',
                'items' => $items,
                'subtotal' => $subtotal,
                'isGuest' => !$userId,
                'formData' => $formData,
                'csrfField' => $this->csrf->getTokenField()
            ]);
        }
    }

    /**
     * Calculate tax (AJAX)
     */
    public function calculateTax()
    {
        $subtotal = (float)$this->post('subtotal', 0);
        $provinceCode = $this->post('province', 'MB');
        
        $taxInfo = $this->taxCalculator->calculate($subtotal, $provinceCode);
        
        $this->jsonResponse([
            'success' => true,
            'tax' => $taxInfo
        ]);
    }

    /**
     * Update cart count
     */
    private function updateCartCount()
    {
        $userId = $this->getUserId();
        $sessionId = $userId ? null : $this->getSessionId();
        $items = $this->cartModel->getItems($userId, $sessionId);
        $_SESSION['cart_count'] = count($items);
    }
}

