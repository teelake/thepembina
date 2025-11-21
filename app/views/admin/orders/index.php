<?php
$content = ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Orders</h1>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if (!empty($orders)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Order #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Created</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($orders as $order): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 font-semibold"><?= htmlspecialchars($order['order_number']) ?></td>
                        <td class="px-4 py-4 text-gray-700"><?= htmlspecialchars($order['email']) ?></td>
                        <td class="px-4 py-4">
                            <span class="badge <?= $order['order_type'] === 'pickup' ? 'badge-info' : 'badge-warning' ?>">
                                <?= ucfirst($order['order_type']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="badge badge-success"><?= ucfirst($order['status']) ?></span>
                        </td>
                        <td class="px-4 py-4 font-semibold text-brand"><?= Helper::formatCurrency($order['total']) ?></td>
                        <td class="px-4 py-4 text-sm text-gray-500"><?= date('M d, Y g:i A', strtotime($order['created_at'])) ?></td>
                        <td class="px-4 py-4 text-right space-x-4">
                            <a href="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>" class="text-brand hover:text-brand-dark font-semibold">View</a>
                            <a href="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>/receipt" class="text-red-600 hover:text-red-800" target="_blank" title="Download receipt">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <i class="fas fa-receipt text-6xl text-gray-200 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">No orders yet</h2>
        <p class="text-gray-500">Orders will appear here once customers start checkout.</p>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

