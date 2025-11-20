<?php
/**
 * Cart Controller
 * Shopping cart functionality
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Core\Helper;
use App\Core\AuditTrail;

class CartController extends Controller
{
    private $cartModel;
    private $productModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->cartModel = new Cart();
        $this->productModel = new Product();
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
     * Cart page
     */
    public function index()
    {
        $userId = $this->getUserId();
        $sessionId = $userId ? null : $this->getSessionId();
        
        $items = $this->cartModel->getItems($userId, $sessionId);
        
        // Calculate totals
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $data = [
            'items' => $items,
            'subtotal' => $subtotal,
            'page_title' => 'Shopping Cart'
        ];

        $this->render('cart/index', $data);
    }

    /**
     * Add item to cart
     */
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }

        $productId = (int)$this->post('product_id');
        $quantity = (int)$this->post('quantity', 1);
        $options = $this->post('options', []);

        $product = $this->productModel->find($productId);
        
        if (!$product || $product['status'] !== 'active') {
            $this->jsonResponse(['success' => false, 'message' => 'Product not available'], 404);
            return;
        }

        // Check stock
        if ($product['manage_stock'] && $product['stock_quantity'] < $quantity) {
            $this->jsonResponse(['success' => false, 'message' => 'Insufficient stock'], 400);
            return;
        }

        $userId = $this->getUserId();
        $sessionId = $userId ? null : $this->getSessionId();

        $cartData = [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'options' => $options
        ];

        $result = $this->cartModel->addItem($cartData);
        
        if ($result) {
            AuditTrail::log('cart_add', 'product', $productId, "Added {$product['name']} to cart", [
                'quantity' => $quantity,
                'options' => $options
            ]);
            
            $this->updateCartCount();
            $this->jsonResponse([
                'success' => true,
                'message' => 'Item added to cart',
                'cart_count' => $this->getCartCount()
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to add item'], 500);
        }
    }

    /**
     * Update cart item
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }

        $cartId = (int)$this->post('cart_id');
        $quantity = (int)$this->post('quantity', 1);

        if ($quantity <= 0) {
            $this->remove();
            return;
        }

        $userId = $this->getUserId();
        $sessionId = $userId ? null : $this->getSessionId();

        // Verify cart item belongs to user
        $cartItem = $this->cartModel->find($cartId);
        if (!$cartItem) {
            $this->jsonResponse(['success' => false, 'message' => 'Cart item not found'], 404);
            return;
        }

        if (($userId && $cartItem['user_id'] != $userId) || (!$userId && $cartItem['session_id'] != $sessionId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $this->cartModel->update($cartId, ['quantity' => $quantity]);
        AuditTrail::log('cart_update', 'cart', $cartId, "Updated cart item quantity to {$quantity}");
        
        $this->updateCartCount();
        $this->jsonResponse(['success' => true, 'message' => 'Cart updated']);
    }

    /**
     * Remove item from cart
     */
    public function remove()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }

        $cartId = (int)$this->post('cart_id');
        $userId = $this->getUserId();
        $sessionId = $userId ? null : $this->getSessionId();

        $cartItem = $this->cartModel->find($cartId);
        if (!$cartItem) {
            $this->jsonResponse(['success' => false, 'message' => 'Cart item not found'], 404);
            return;
        }

        if (($userId && $cartItem['user_id'] != $userId) || (!$userId && $cartItem['session_id'] != $sessionId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $this->cartModel->delete($cartId);
        AuditTrail::log('cart_remove', 'cart', $cartId, 'Removed item from cart');
        
        $this->updateCartCount();
        $this->jsonResponse(['success' => true, 'message' => 'Item removed from cart']);
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }

        $userId = $this->getUserId();
        $sessionId = $userId ? null : $this->getSessionId();

        $this->cartModel->clear($userId, $sessionId);
        AuditTrail::log('cart_clear', 'cart', null, 'Cleared shopping cart');
        
        $this->updateCartCount();
        $this->jsonResponse(['success' => true, 'message' => 'Cart cleared']);
    }

    /**
     * Update cart count in session
     */
    private function updateCartCount()
    {
        $userId = $this->getUserId();
        $sessionId = $userId ? null : $this->getSessionId();
        $items = $this->cartModel->getItems($userId, $sessionId);
        $_SESSION['cart_count'] = count($items);
    }

    /**
     * Get cart count
     */
    private function getCartCount()
    {
        return $_SESSION['cart_count'] ?? 0;
    }
}

