<?php
use App\Core\Helper;
$content = ob_start();
?>

<div class="max-w-7xl mx-auto py-8 px-4">
    <div class="mb-6">
        <h1 class="text-3xl font-bold mb-2">My Orders</h1>
        <p class="text-gray-600">View and manage your order history</p>
    </div>
    
    <?php if (!empty($orders)): ?>
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Order #</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Date</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Type</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Total</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Payment</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Status</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-4 px-6">
                            <a href="<?= BASE_URL ?>/account/orders/<?= $order['id'] ?>" class="text-brand font-semibold hover:underline">
                                <?= htmlspecialchars($order['order_number']) ?>
                            </a>
                        </td>
                        <td class="py-4 px-6 text-gray-600">
                            <?= date('M d, Y g:i A', strtotime($order['created_at'])) ?>
                        </td>
                        <td class="py-4 px-6 text-gray-600">
                            <?= ucfirst($order['order_type']) ?>
                        </td>
                        <td class="py-4 px-6 font-semibold">
                            <?= Helper::formatCurrency($order['total']) ?>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 rounded text-xs font-semibold <?= 
                                $order['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                                ($order['payment_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                'bg-red-100 text-red-800')
                            ?>">
                                <?= ucfirst($order['payment_status']) ?>
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 rounded text-xs font-semibold <?= 
                                $order['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                'bg-gray-100 text-gray-800')
                            ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center space-x-3">
                                <a href="<?= BASE_URL ?>/account/orders/<?= $order['id'] ?>" class="text-brand hover:underline text-sm">
                                    View
                                </a>
                                <?php if ($order['payment_status'] === 'paid'): ?>
                                <span class="text-gray-300">|</span>
                                <a href="<?= BASE_URL ?>/account/orders/<?= $order['id'] ?>/receipt" class="text-brand hover:underline text-sm" target="_blank">
                                    <i class="fas fa-download mr-1"></i> Receipt
                                </a>
                                <?php elseif ($order['payment_status'] === 'pending'): ?>
                                <span class="text-gray-300">|</span>
                                <a href="<?= BASE_URL ?>/payment?order_id=<?= $order['id'] ?>" class="text-brand hover:underline text-sm">
                                    Pay Now
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-md p-12 text-center">
        <i class="fas fa-shopping-bag text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No orders yet</h3>
        <p class="text-gray-600 mb-6">Start shopping to see your orders here</p>
        <a href="<?= BASE_URL ?>/menu" class="inline-block bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-dark transition">
            Browse Menu
        </a>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$page_title = 'My Orders';
require_once APP_PATH . '/views/layouts/main.php';
?>

