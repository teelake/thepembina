<?php
use App\Core\Helper;
$content = ob_start();
?>

<div class="max-w-7xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">My Account</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="<?= BASE_URL ?>/account/orders" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center">
                <div class="bg-brand/10 rounded-full p-4 mr-4">
                    <i class="fas fa-shopping-bag text-2xl text-brand"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">My Orders</h3>
                    <p class="text-sm text-gray-600">View order history</p>
                </div>
            </div>
        </a>
        
        <a href="<?= BASE_URL ?>/account/profile" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center">
                <div class="bg-brand/10 rounded-full p-4 mr-4">
                    <i class="fas fa-user text-2xl text-brand"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">My Profile</h3>
                    <p class="text-sm text-gray-600">Update your information</p>
                </div>
            </div>
        </a>
        
        <a href="<?= BASE_URL ?>/account/addresses" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center">
                <div class="bg-brand/10 rounded-full p-4 mr-4">
                    <i class="fas fa-map-marker-alt text-2xl text-brand"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">My Addresses</h3>
                    <p class="text-sm text-gray-600">Manage addresses</p>
                </div>
            </div>
        </a>
    </div>
    
    <?php if (!empty($recentOrders)): ?>
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">Recent Orders</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Order #</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Type</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Total</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-4">
                            <a href="<?= BASE_URL ?>/account/orders/<?= $order['id'] ?>" class="text-brand font-semibold hover:underline">
                                <?= htmlspecialchars($order['order_number']) ?>
                            </a>
                        </td>
                        <td class="py-3 px-4 text-gray-600">
                            <?= date('M d, Y', strtotime($order['created_at'])) ?>
                        </td>
                        <td class="py-3 px-4 text-gray-600">
                            <?= ucfirst($order['order_type']) ?>
                        </td>
                        <td class="py-3 px-4 font-semibold">
                            <?= Helper::formatCurrency($order['total']) ?>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded text-xs font-semibold <?= 
                                $order['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                'bg-gray-100 text-gray-800')
                            ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <a href="<?= BASE_URL ?>/account/orders/<?= $order['id'] ?>" class="text-brand hover:underline text-sm">
                                View
                            </a>
                            <?php if ($order['payment_status'] === 'paid'): ?>
                            <span class="mx-2">|</span>
                            <a href="<?= BASE_URL ?>/account/orders/<?= $order['id'] ?>/receipt" class="text-brand hover:underline text-sm">
                                Receipt
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-center">
            <a href="<?= BASE_URL ?>/account/orders" class="text-brand hover:underline font-semibold">
                View All Orders <i class="fas fa-arrow-right ml-1"></i>
            </a>
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
$page_title = 'My Account';
require_once APP_PATH . '/views/layouts/main.php';
?>

