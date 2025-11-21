<?php
use App\Core\Helper;
$content = ob_start();
?>

<?php
$statusFilter = $filters['status'] ?? '';
$typeFilter = $filters['order_type'] ?? '';
$paymentFilter = $filters['payment_status'] ?? '';
$fromFilter = $filters['from'] ?? '';
$toFilter = $filters['to'] ?? '';
$keyword = $filters['keyword'] ?? '';
$hasFilters = $statusFilter || $typeFilter || $paymentFilter || $fromFilter || $toFilter || $keyword;
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Orders</h1>
    <?php if ($hasFilters): ?>
        <a href="<?= BASE_URL ?>/admin/orders" class="text-sm text-brand hover:text-brand-dark">Clear filters</a>
    <?php endif; ?>
</div>

<form method="GET" class="bg-white rounded-lg shadow-md p-6 mb-6 grid grid-cols-1 lg:grid-cols-5 gap-4">
    <div>
        <label class="form-label text-xs uppercase">Search</label>
        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Order # or email" class="form-input">
    </div>
    <div>
        <label class="form-label text-xs uppercase">Status</label>
        <select name="status" class="form-select">
            <option value="">All</option>
            <?php foreach (['pending','processing','confirmed','preparing','ready','out_for_delivery','delivered','cancelled','refunded'] as $status): ?>
                <option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ', $status)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="form-label text-xs uppercase">Order Type</label>
        <select name="order_type" class="form-select">
            <option value="">All</option>
            <option value="pickup" <?= $typeFilter === 'pickup' ? 'selected' : '' ?>>Pickup</option>
            <option value="delivery" <?= $typeFilter === 'delivery' ? 'selected' : '' ?>>Delivery</option>
        </select>
    </div>
    <div>
        <label class="form-label text-xs uppercase">Payment Status</label>
        <select name="payment_status" class="form-select">
            <option value="">All</option>
            <option value="paid" <?= $paymentFilter === 'paid' ? 'selected' : '' ?>>Paid</option>
            <option value="pending" <?= $paymentFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="failed" <?= $paymentFilter === 'failed' ? 'selected' : '' ?>>Failed</option>
            <option value="refunded" <?= $paymentFilter === 'refunded' ? 'selected' : '' ?>>Refunded</option>
        </select>
    </div>
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="w-full sm:flex-1">
            <label class="form-label text-xs uppercase">From</label>
            <input type="date" name="from" value="<?= htmlspecialchars($fromFilter) ?>" class="form-input">
        </div>
        <div class="w-full sm:flex-1">
            <label class="form-label text-xs uppercase">To</label>
            <input type="date" name="to" value="<?= htmlspecialchars($toFilter) ?>" class="form-input">
        </div>
    </div>
    <div class="lg:col-span-5 flex justify-end gap-3">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-2"></i>Filter</button>
    </div>
</form>

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
                        <td class="px-4 py-4 text-right space-x-3">
                            <a href="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>" class="text-brand hover:text-brand-dark font-semibold">View</a>
                            <a href="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>/receipt" class="text-red-600 hover:text-red-800" target="_blank" title="Download receipt">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>/email-receipt" class="inline" data-confirm="Send receipt to <?= htmlspecialchars($order['email']) ?>?">
                                <?= $csrfField ?? '' ?>
                                <button type="submit" class="text-brand hover:text-brand-dark" title="Email receipt">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
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

