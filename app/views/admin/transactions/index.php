<?php
use App\Core\Helper;
$content = ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Transaction History</h1>
    <div class="flex gap-3">
        <a href="<?= BASE_URL ?>/admin/transactions/export?<?= http_build_query($filters) ?>" 
           class="btn btn-secondary inline-flex items-center">
            <i class="fas fa-download mr-2"></i> Export CSV
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Total Transactions</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($statistics['total_transactions']) ?></p>
            </div>
            <i class="fas fa-receipt text-4xl text-blue-500"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Total Revenue</p>
                <p class="text-3xl font-bold text-gray-900"><?= Helper::formatCurrency($statistics['total_revenue']) ?></p>
            </div>
            <i class="fas fa-dollar-sign text-4xl text-green-500"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Successful</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($statistics['successful_transactions']) ?></p>
            </div>
            <i class="fas fa-check-circle text-4xl text-yellow-500"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Failed</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($statistics['failed_transactions']) ?></p>
            </div>
            <i class="fas fa-times-circle text-4xl text-red-500"></i>
        </div>
    </div>
</div>

<!-- Additional Stats -->
<?php if ($statistics['total_revenue'] > 0): ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center">
            <span class="text-gray-600">Average Transaction</span>
            <span class="text-2xl font-bold text-brand"><?= Helper::formatCurrency($statistics['average_transaction']) ?></span>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center">
            <span class="text-gray-600">Total Refunded</span>
            <span class="text-2xl font-bold text-red-600"><?= Helper::formatCurrency($statistics['total_refunded']) ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filters -->
<?php
$hasFilters = !empty(array_filter($filters));
?>
<form method="GET" class="bg-white rounded-lg shadow-md p-6 mb-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
    <div>
        <label class="form-label text-xs uppercase">Transaction ID</label>
        <input type="text" name="transaction_id" value="<?= htmlspecialchars($filters['transaction_id'] ?? '') ?>" 
               placeholder="Search transaction ID" class="form-input">
    </div>
    <div>
        <label class="form-label text-xs uppercase">Order Number</label>
        <input type="text" name="order_number" value="<?= htmlspecialchars($filters['order_number'] ?? '') ?>" 
               placeholder="Search order number" class="form-input">
    </div>
    <div>
        <label class="form-label text-xs uppercase">Customer Email</label>
        <input type="text" name="email" value="<?= htmlspecialchars($filters['email'] ?? '') ?>" 
               placeholder="Search email" class="form-input">
    </div>
    <div>
        <label class="form-label text-xs uppercase">Gateway</label>
        <select name="gateway" class="form-select">
            <option value="">All Gateways</option>
            <?php foreach ($gateways as $gateway): ?>
                <option value="<?= htmlspecialchars($gateway) ?>" <?= ($filters['gateway'] ?? '') === $gateway ? 'selected' : '' ?>>
                    <?= ucfirst(htmlspecialchars($gateway)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="form-label text-xs uppercase">Status</label>
        <select name="status" class="form-select">
            <option value="">All Statuses</option>
            <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
            <option value="refunded" <?= ($filters['status'] ?? '') === 'refunded' ? 'selected' : '' ?>>Refunded</option>
        </select>
    </div>
    <div>
        <label class="form-label text-xs uppercase">From Date</label>
        <input type="date" name="from" value="<?= htmlspecialchars($filters['from'] ?? '') ?>" class="form-input">
    </div>
    <div>
        <label class="form-label text-xs uppercase">To Date</label>
        <input type="date" name="to" value="<?= htmlspecialchars($filters['to'] ?? '') ?>" class="form-input">
    </div>
    <div class="flex items-end gap-2">
        <button type="submit" class="btn btn-primary flex-1">
            <i class="fas fa-filter mr-2"></i> Filter
        </button>
        <?php if ($hasFilters): ?>
            <a href="<?= BASE_URL ?>/admin/transactions" class="btn btn-secondary">
                <i class="fas fa-times"></i>
            </a>
        <?php endif; ?>
    </div>
</form>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success mb-6"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error mb-6"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<!-- Transactions Table -->
<?php if (!empty($transactions)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Transaction ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Order #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Gateway</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Amount</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($transactions as $transaction): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4">
                            <span class="font-mono text-sm"><?= htmlspecialchars($transaction['transaction_id']) ?></span>
                        </td>
                        <td class="px-4 py-4">
                            <?php if ($transaction['order_number']): ?>
                                <a href="<?= BASE_URL ?>/admin/orders/<?= $transaction['order_id'] ?>" 
                                   class="text-brand hover:underline font-semibold">
                                    <?= htmlspecialchars($transaction['order_number']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-4 text-gray-700">
                            <?= htmlspecialchars($transaction['email'] ?? 'N/A') ?>
                        </td>
                        <td class="px-4 py-4">
                            <span class="badge badge-info"><?= ucfirst(htmlspecialchars($transaction['gateway'])) ?></span>
                        </td>
                        <td class="px-4 py-4 font-semibold text-brand">
                            <?= Helper::formatCurrency($transaction['amount']) ?>
                            <span class="text-xs text-gray-500"><?= htmlspecialchars($transaction['currency']) ?></span>
                        </td>
                        <td class="px-4 py-4">
                            <?php
                            $statusColors = [
                                'completed' => 'badge-success',
                                'pending' => 'badge-warning',
                                'failed' => 'badge-error',
                                'refunded' => 'badge-info'
                            ];
                            $statusClass = $statusColors[$transaction['status']] ?? 'badge';
                            ?>
                            <span class="badge <?= $statusClass ?>">
                                <?= ucfirst($transaction['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-500">
                            <?= date('M d, Y g:i A', strtotime($transaction['created_at'])) ?>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <?php if ($transaction['order_id']): ?>
                                <a href="<?= BASE_URL ?>/admin/orders/<?= $transaction['order_id'] ?>" 
                                   class="text-brand hover:text-brand-dark font-semibold">
                                    View Order
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex justify-center">
            <nav class="flex space-x-2">
                <?php if ($currentPage > 1): ?>
                    <a href="?<?= http_build_query(array_merge($filters, ['page' => $currentPage - 1])) ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $currentPage): ?>
                        <span class="px-4 py-2 bg-brand text-white rounded-lg font-semibold"><?= $i ?></span>
                    <?php elseif ($i == 1 || $i == $totalPages || ($i >= $currentPage - 2 && $i <= $currentPage + 2)): ?>
                        <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>" 
                           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($filters, ['page' => $currentPage + 1])) ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Next
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <i class="fas fa-receipt text-6xl text-gray-300 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">No transactions found</h2>
        <p class="text-gray-500"><?= $hasFilters ? 'Try adjusting your filters.' : 'Transactions will appear here once payments are processed.' ?></p>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

