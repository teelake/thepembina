<?php
use App\Core\Helper;
$content = ob_start();
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Total Orders</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_orders']) ?></p>
            </div>
            <i class="fas fa-shopping-cart text-4xl text-blue-500"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Pending Orders</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['pending_orders']) ?></p>
            </div>
            <i class="fas fa-clock text-4xl text-yellow-500"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Total Products</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_products']) ?></p>
            </div>
            <i class="fas fa-box text-4xl text-green-500"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Total Customers</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_customers']) ?></p>
            </div>
            <i class="fas fa-users text-4xl text-purple-500"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Today's Stats -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">Today's Activity</h2>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Orders Today</span>
                <span class="text-2xl font-bold text-brand"><?= number_format($stats['today_orders']) ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Revenue Today</span>
                <span class="text-2xl font-bold text-green-600"><?= Helper::formatCurrency($stats['today_revenue']) ?></span>
            </div>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">Top Selling Products</h2>
        <?php if (!empty($topProducts)): ?>
            <div class="space-y-3">
                <?php foreach ($topProducts as $product): ?>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-semibold"><?= htmlspecialchars($product['name']) ?></p>
                            <p class="text-sm text-gray-600"><?= number_format($product['total_sold']) ?> sold</p>
                        </div>
                        <span class="font-bold text-brand"><?= Helper::formatCurrency($product['revenue']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No sales data yet</p>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Orders -->
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Recent Orders</h2>
        <a href="<?= BASE_URL ?>/admin/orders" class="text-brand hover:text-brand-dark font-semibold">
            View All <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    
    <?php if (!empty($recentOrders)): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Order #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($recentOrders as $order): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>" class="text-brand hover:underline font-semibold">
                                    <?= htmlspecialchars($order['order_number']) ?>
                                </a>
                            </td>
                            <td class="px-4 py-3"><?= htmlspecialchars($order['email']) ?></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold <?= $order['order_type'] === 'pickup' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>">
                                    <?= ucfirst($order['order_type']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold"><?= Helper::formatCurrency($order['total']) ?></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold 
                                    <?php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'confirmed' => 'bg-green-100 text-green-800',
                                        'delivered' => 'bg-gray-100 text-gray-800',
                                        'cancelled' => 'bg-red-100 text-red-800'
                                    ];
                                    echo $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                ">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                            <td class="px-4 py-3">
                                <a href="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>" class="text-brand hover:text-brand-dark">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-500 text-center py-8">No orders yet</p>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

