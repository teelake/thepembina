<?php
use App\Core\Helper;
$content = ob_start();

$billingAddress = json_decode($order['billing_address'], true);
$shippingAddress = $order['shipping_address'] ? json_decode($order['shipping_address'], true) : null;
?>

<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="mb-6">
        <a href="<?= BASE_URL ?>/account/orders" class="text-brand hover:underline mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
        </a>
        <h1 class="text-3xl font-bold mt-4">Order <?= htmlspecialchars($order['order_number']) ?></h1>
        <p class="text-gray-600">Placed on <?= date('M d, Y g:i A', strtotime($order['created_at'])) ?></p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="font-semibold text-gray-700 mb-2">Order Status</h3>
            <p class="text-2xl font-bold <?= 
                $order['status'] === 'confirmed' ? 'text-green-600' : 
                ($order['status'] === 'pending' ? 'text-yellow-600' : 'text-gray-600')
            ?>">
                <?= ucfirst($order['status']) ?>
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="font-semibold text-gray-700 mb-2">Payment Status</h3>
            <p class="text-2xl font-bold <?= 
                $order['payment_status'] === 'paid' ? 'text-green-600' : 
                ($order['payment_status'] === 'pending' ? 'text-yellow-600' : 'text-red-600')
            ?>">
                <?= ucfirst($order['payment_status']) ?>
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="font-semibold text-gray-700 mb-2">Order Total</h3>
            <p class="text-2xl font-bold text-brand">
                <?= Helper::formatCurrency($order['total']) ?>
            </p>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Order Items</h2>
        <div class="space-y-4">
            <?php foreach ($order['items'] as $item): ?>
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-0">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($item['product_name']) ?></h4>
                    <p class="text-sm text-gray-600">Quantity: <?= $item['quantity'] ?></p>
                    <?php if (!empty($item['options'])): ?>
                        <?php 
                        $options = is_string($item['options']) ? json_decode($item['options'], true) : $item['options'];
                        if (is_array($options) && !empty($options)):
                        ?>
                        <p class="text-xs text-gray-500 mt-1">
                            <?= implode(', ', array_map(function($k, $v) { return ucfirst($k) . ': ' . $v; }, array_keys($options), $options)) ?>
                        </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <span class="font-semibold text-gray-900 ml-4">
                    <?= Helper::formatCurrency($item['subtotal']) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-6 pt-6 border-t border-gray-200 space-y-2">
            <div class="flex justify-between text-gray-700">
                <span>Subtotal</span>
                <span class="font-semibold"><?= Helper::formatCurrency($order['subtotal']) ?></span>
            </div>
            <div class="flex justify-between text-gray-700">
                <span>Tax</span>
                <span class="font-semibold"><?= Helper::formatCurrency($order['tax_amount'] ?? 0) ?></span>
            </div>
            <?php if (!empty($order['shipping_amount'])): ?>
            <div class="flex justify-between text-gray-700">
                <span>Delivery Fee</span>
                <span class="font-semibold"><?= Helper::formatCurrency($order['shipping_amount']) ?></span>
            </div>
            <?php endif; ?>
            <div class="flex justify-between pt-2 border-t border-gray-200">
                <span class="text-lg font-bold text-gray-900">Total</span>
                <span class="text-lg font-bold text-brand"><?= Helper::formatCurrency($order['total']) ?></span>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Billing Address</h2>
            <?php if ($billingAddress): ?>
            <div class="text-gray-700">
                <p class="font-semibold"><?= htmlspecialchars($billingAddress['first_name'] ?? '') ?> <?= htmlspecialchars($billingAddress['last_name'] ?? '') ?></p>
                <p><?= htmlspecialchars($billingAddress['address_line1'] ?? '') ?></p>
                <?php if (!empty($billingAddress['address_line2'])): ?>
                <p><?= htmlspecialchars($billingAddress['address_line2']) ?></p>
                <?php endif; ?>
                <p><?= htmlspecialchars($billingAddress['city'] ?? '') ?>, <?= htmlspecialchars($billingAddress['province'] ?? '') ?> <?= htmlspecialchars($billingAddress['postal_code'] ?? '') ?></p>
                <?php if (!empty($billingAddress['phone'])): ?>
                <p class="mt-2">Phone: <?= htmlspecialchars($billingAddress['phone']) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($shippingAddress && $order['order_type'] === 'delivery'): ?>
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Delivery Address</h2>
            <div class="text-gray-700">
                <p class="font-semibold"><?= htmlspecialchars($shippingAddress['first_name'] ?? '') ?> <?= htmlspecialchars($shippingAddress['last_name'] ?? '') ?></p>
                <p><?= htmlspecialchars($shippingAddress['address_line1'] ?? '') ?></p>
                <?php if (!empty($shippingAddress['address_line2'])): ?>
                <p><?= htmlspecialchars($shippingAddress['address_line2']) ?></p>
                <?php endif; ?>
                <p><?= htmlspecialchars($shippingAddress['city'] ?? '') ?>, <?= htmlspecialchars($shippingAddress['province'] ?? '') ?> <?= htmlspecialchars($shippingAddress['postal_code'] ?? '') ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($order['delivery_instructions'])): ?>
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold mb-2">Delivery Instructions</h2>
        <p class="text-gray-700"><?= htmlspecialchars($order['delivery_instructions']) ?></p>
    </div>
    <?php endif; ?>
    
    <div class="flex flex-wrap gap-4">
        <?php if ($order['payment_status'] === 'paid'): ?>
        <a href="<?= BASE_URL ?>/account/orders/<?= $order['id'] ?>/receipt" 
           class="bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-dark transition inline-flex items-center"
           target="_blank">
            <i class="fas fa-download mr-2"></i> Download Receipt
        </a>
        <?php elseif ($order['payment_status'] === 'pending'): ?>
        <a href="<?= BASE_URL ?>/payment?order_id=<?= $order['id'] ?>" 
           class="bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-dark transition inline-flex items-center">
            <i class="fas fa-credit-card mr-2"></i> Complete Payment
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/account/orders" 
           class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition inline-flex items-center">
            Back to Orders
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
$page_title = 'Order Details';
require_once APP_PATH . '/views/layouts/main.php';
?>


