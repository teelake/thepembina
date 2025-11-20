<?php
use App\Core\Helper;
$content = ob_start();
?>

<section class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-8 text-gray-900">Shopping Cart</h1>
        
        <?php if (!empty($items)): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="space-y-4" id="cart-items">
                            <?php foreach ($items as $item): ?>
                                <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg hover:shadow-md transition" data-cart-id="<?= $item['id'] ?>">
                                    <?php if ($item['image']): ?>
                                        <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($item['image']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>" 
                                             class="w-20 h-20 object-cover rounded-lg">
                                    <?php else: ?>
                                        <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-utensils text-2xl text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-lg text-gray-900"><?= htmlspecialchars($item['name']) ?></h3>
                                        <p class="text-brand font-bold text-lg"><?= Helper::formatCurrency($item['price']) ?></p>
                                        <?php if ($item['options']): ?>
                                            <?php $options = json_decode($item['options'], true); ?>
                                            <?php if ($options): ?>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    <?php foreach ($options as $opt): ?>
                                                        <span class="inline-block bg-gray-100 px-2 py-1 rounded text-xs mr-1"><?= htmlspecialchars($opt) ?></span>
                                                    <?php endforeach; ?>
                                                </p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center border border-gray-300 rounded-lg">
                                            <button type="button" class="qty-decrease px-3 py-1 hover:bg-gray-100" data-cart-id="<?= $item['id'] ?>">-</button>
                                            <span class="quantity px-4 py-1 border-x border-gray-300"><?= $item['quantity'] ?></span>
                                            <button type="button" class="qty-increase px-3 py-1 hover:bg-gray-100" data-cart-id="<?= $item['id'] ?>">+</button>
                                        </div>
                                        
                                        <span class="item-total font-bold text-lg text-gray-900 w-24 text-right">
                                            <?= Helper::formatCurrency($item['price'] * $item['quantity']) ?>
                                        </span>
                                        
                                        <button type="button" class="remove-item text-red-500 hover:text-red-700 p-2" data-cart-id="<?= $item['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <a href="<?= BASE_URL ?>/menu" class="text-brand hover:text-brand-dark font-semibold">
                            <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                        </a>
                    </div>
                </div>
                
                <!-- Cart Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md p-6 sticky top-4">
                        <h2 class="text-2xl font-bold mb-6">Order Summary</h2>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span id="cart-subtotal"><?= Helper::formatCurrency($subtotal) ?></span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Tax</span>
                                <span>Calculated at checkout</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Delivery</span>
                                <span>Calculated at checkout</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between text-xl font-bold">
                                <span>Total</span>
                                <span id="cart-total" class="text-brand"><?= Helper::formatCurrency($subtotal) ?></span>
                            </div>
                        </div>
                        
                        <a href="<?= BASE_URL ?>/checkout" 
                           class="block w-full bg-brand text-white text-center py-4 rounded-lg font-bold text-lg hover:bg-brand-dark transition transform hover:scale-105 shadow-lg mb-4">
                            <i class="fas fa-lock mr-2"></i> Proceed to Checkout
                        </a>
                        
                        <p class="text-sm text-gray-500 text-center">
                            <i class="fas fa-shield-alt mr-1"></i> Secure checkout
                        </p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-700 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-6">Start adding delicious items to your cart!</p>
                <a href="<?= BASE_URL ?>/menu" 
                   class="inline-block bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-brand-dark transition transform hover:scale-105">
                    Browse Menu
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
    
    // Update quantity
    document.querySelectorAll('.qty-increase, .qty-decrease').forEach(btn => {
        btn.addEventListener('click', function() {
            const cartId = this.dataset.cartId;
            const isIncrease = this.classList.contains('qty-increase');
            const quantityEl = this.closest('.flex').querySelector('.quantity');
            let qty = parseInt(quantityEl.textContent);
            
            if (isIncrease) {
                qty++;
            } else {
                if (qty > 1) qty--;
            }
            
            updateCartItem(cartId, qty);
        });
    });
    
    // Remove item
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Remove this item from cart?')) {
                const cartId = this.dataset.cartId;
                removeCartItem(cartId);
            }
        });
    });
    
    function updateCartItem(cartId, quantity) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('cart_id', cartId);
        formData.append('quantity', quantity);
        
        fetch('<?= BASE_URL ?>/cart/update', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update cart');
            }
        });
    }
    
    function removeCartItem(cartId) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('cart_id', cartId);
        
        fetch('<?= BASE_URL ?>/cart/remove', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to remove item');
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
$page_title = 'Shopping Cart';
require_once APP_PATH . '/views/layouts/main.php';
?>

