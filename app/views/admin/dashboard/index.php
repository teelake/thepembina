<?php
use App\Core\Helper;
$content = ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard Overview</h1>
    <p class="text-gray-600">Welcome back! Here's what's happening with your business today.</p>
</div>

<!-- Stats Cards - Modern Design -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Orders Card -->
    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-blue-200">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-blue-500 rounded-lg p-3 shadow-md">
                <i class="fas fa-shopping-cart text-2xl text-white"></i>
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-1">Total Orders</p>
                <p class="text-3xl font-bold text-blue-900"><?= number_format($stats['total_orders']) ?></p>
            </div>
        </div>
        <div class="pt-3 border-t border-blue-200">
            <p class="text-xs text-blue-600">All time orders</p>
        </div>
    </div>
    
    <!-- Pending Orders Card -->
    <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-amber-200">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-amber-500 rounded-lg p-3 shadow-md">
                <i class="fas fa-clock text-2xl text-white"></i>
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-1">Pending Orders</p>
                <p class="text-3xl font-bold text-amber-900"><?= number_format($stats['pending_orders']) ?></p>
            </div>
        </div>
        <div class="pt-3 border-t border-amber-200">
            <p class="text-xs text-amber-600">Requires attention</p>
        </div>
    </div>
    
    <!-- Total Products Card -->
    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-emerald-200">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-emerald-500 rounded-lg p-3 shadow-md">
                <i class="fas fa-box text-2xl text-white"></i>
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wide mb-1">Total Products</p>
                <p class="text-3xl font-bold text-emerald-900"><?= number_format($stats['total_products']) ?></p>
            </div>
        </div>
        <div class="pt-3 border-t border-emerald-200">
            <p class="text-xs text-emerald-600">Active products</p>
        </div>
    </div>
    
    <!-- Total Customers Card -->
    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-purple-200">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-purple-500 rounded-lg p-3 shadow-md">
                <i class="fas fa-users text-2xl text-white"></i>
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold text-purple-700 uppercase tracking-wide mb-1">Total Customers</p>
                <p class="text-3xl font-bold text-purple-900"><?= number_format($stats['total_customers']) ?></p>
            </div>
        </div>
        <div class="pt-3 border-t border-purple-200">
            <p class="text-xs text-purple-600">Registered users</p>
        </div>
    </div>
</div>

<!-- Revenue & Orders Trend - Full Width Chart -->
<div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-8">
    <div class="bg-gradient-to-r from-brand to-brand-dark px-6 py-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-white">Revenue & Orders Trend</h2>
                <p class="text-sm text-white/80 mt-1">Showing <?= htmlspecialchars($chartData['rangeLabel']) ?> - Hover over data points to see details</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($rangeOptions as $key => $option): ?>
                    <a href="<?= BASE_URL ?>/admin?range=<?= $key ?>"
                       class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 <?= $range === $key ? 'bg-white text-brand shadow-md' : 'bg-white/20 text-white hover:bg-white/30' ?>">
                        <?= htmlspecialchars($option['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="p-6 bg-gray-50">
        <canvas id="ordersRevenueChart" class="w-full" style="height: 400px;"></canvas>
    </div>
</div>

<!-- Today's Activity & Order Type Breakdown - Side by Side -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Today's Activity Card -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center">
                <i class="fas fa-calendar-day mr-2"></i>
                Today's Activity
            </h2>
        </div>
        <div class="p-6">
            <div class="space-y-6">
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
                    <div class="flex items-center">
                        <div class="bg-blue-500 rounded-lg p-3 mr-4">
                            <i class="fas fa-shopping-bag text-white"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Orders Today</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($stats['today_orders']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-emerald-50 to-green-50 rounded-lg border border-emerald-100">
                    <div class="flex items-center">
                        <div class="bg-emerald-500 rounded-lg p-3 mr-4">
                            <i class="fas fa-dollar-sign text-white"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Revenue Today</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1"><?= Helper::formatCurrency($stats['today_revenue']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Type Breakdown Chart - Smaller -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white">Order Type Breakdown</h2>
                    <p class="text-sm text-white/80 mt-1"><?= htmlspecialchars($chartData['rangeLabel']) ?> - Hover over bars for details</p>
                </div>
                <div class="bg-white/20 rounded-lg p-2">
                    <i class="fas fa-chart-bar text-2xl text-white"></i>
                </div>
            </div>
        </div>
        <div class="p-6 bg-gray-50">
            <canvas id="orderTypeChart" class="w-full" style="height: 280px;"></canvas>
        </div>
    </div>
</div>

<!-- Top Selling Products -->
<div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-8">
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
        <h2 class="text-xl font-bold text-white flex items-center">
            <i class="fas fa-fire mr-2"></i>
            Top Selling Products
        </h2>
    </div>
    <div class="p-6">
        <?php if (!empty($topProducts)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($topProducts as $index => $product): ?>
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:shadow-md transition-all duration-200">
                        <div class="flex items-center flex-1 min-w-0">
                            <div class="flex-shrink-0 w-10 h-10 bg-brand rounded-lg flex items-center justify-center mr-3 font-bold text-white">
                                <?= $index + 1 ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 truncate"><?= htmlspecialchars($product['name']) ?></p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-check-circle text-emerald-500 mr-1"></i>
                                    <?= number_format($product['total_sold']) ?> sold
                                </p>
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <span class="font-bold text-lg text-brand"><?= Helper::formatCurrency($product['revenue']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">No sales data yet</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Orders Table - Enhanced Design -->
<div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-white flex items-center">
                <i class="fas fa-list-alt mr-2"></i>
                Recent Orders
            </h2>
            <a href="<?= BASE_URL ?>/admin/orders" class="text-white hover:text-brand transition-colors duration-200 font-semibold flex items-center group">
                View All
                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>
    </div>
    
    <?php if (!empty($recentOrders)): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Order #</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($recentOrders as $order): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>" class="text-brand hover:text-brand-dark font-semibold hover:underline">
                                    <?= htmlspecialchars($order['order_number']) ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center mr-2">
                                        <i class="fas fa-user text-gray-500 text-xs"></i>
                                    </div>
                                    <span class="text-gray-900"><?= htmlspecialchars($order['email']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $order['order_type'] === 'pickup' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' ?>">
                                    <i class="fas <?= $order['order_type'] === 'pickup' ? 'fa-hand-paper' : 'fa-truck' ?> mr-1"></i>
                                    <?= ucfirst($order['order_type']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-bold text-gray-900"><?= Helper::formatCurrency($order['total']) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                    <?php
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'confirmed' => 'bg-emerald-100 text-emerald-800',
                                        'ready' => 'bg-teal-100 text-teal-800',
                                        'out for delivery' => 'bg-indigo-100 text-indigo-800',
                                        'delivered' => 'bg-gray-100 text-gray-800',
                                        'cancelled' => 'bg-red-100 text-red-800'
                                    ];
                                    echo $statusColors[strtolower($order['status'])] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                ">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <i class="far fa-calendar mr-1"></i>
                                <?= date('M d, Y', strtotime($order['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>" 
                                   class="inline-flex items-center px-3 py-1.5 bg-brand text-white rounded-lg hover:bg-brand-dark transition-colors duration-200 text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i>
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">No orders yet</p>
            <p class="text-gray-400 text-sm mt-2">Orders will appear here once customers start placing them</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$chartPayload = json_encode($chartData ?? [
    'labels' => [],
    'revenue' => [],
    'orders' => [],
    'orderTypeLabels' => [],
    'orderTypeCounts' => []
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$chartScriptPath = BASE_URL . '/public/js/simple-charts.js';
$content .= <<<HTML
<script src="{$chartScriptPath}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var chartData = {$chartPayload};
    if (window.SimpleCharts) {
        var trendCanvas = document.getElementById('ordersRevenueChart');
        if (trendCanvas) {
            SimpleCharts.renderLineChart(trendCanvas, {
                labels: chartData.labels,
                datasets: [
                    { label: 'Revenue', data: chartData.revenue, color: '#8B4513' },
                    { label: 'Orders', data: chartData.orders, color: '#F4A460', secondary: true }
                ],
                ySuffix: '$',
                maxLabels: 15
            });
        }

        var typeCanvas = document.getElementById('orderTypeChart');
        if (typeCanvas) {
            SimpleCharts.renderBarChart(typeCanvas, {
                labels: chartData.orderTypeLabels,
                data: chartData.orderTypeCounts,
                color: '#2F855A'
            });
        }
    }
});
</script>
HTML;
require_once APP_PATH . '/views/layouts/admin.php';
?>
