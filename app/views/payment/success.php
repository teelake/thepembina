<?php
use App\Core\Helper;
$content = ob_start();
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check text-4xl text-green-600"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
                <p class="text-gray-600">Thank you for your order</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                <h2 class="text-xl font-bold mb-4">Order Details</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order Number:</span>
                        <span class="font-semibold"><?= htmlspecialchars($order['order_number']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order Type:</span>
                        <span class="font-semibold"><?= ucfirst($order['order_type']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Amount:</span>
                        <span class="font-bold text-brand text-xl"><?= Helper::formatCurrency($order['total']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Payment Status:</span>
                        <span class="font-semibold text-green-600">Paid</span>
                    </div>
                </div>
            </div>
            
            <?php if ($order['order_type'] === 'pickup'): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        Your order will be ready for pickup. We'll notify you when it's ready!
                    </p>
                </div>
            <?php else: ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-blue-800">
                        <i class="fas fa-truck mr-2"></i>
                        Your order is being prepared and will be delivered via DoorDash. You'll receive tracking information shortly.
                    </p>
                </div>
            <?php endif; ?>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?= BASE_URL ?>/account/orders/<?= $order['id'] ?>" 
                   class="bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-dark transition">
                    View Order Details
                </a>
                <a href="<?= BASE_URL ?>/menu" 
                   class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$page_title = 'Payment Successful';
require_once APP_PATH . '/views/layouts/main.php';
?>

