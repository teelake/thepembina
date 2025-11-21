<?php
use App\Core\Helper;
$content = ob_start();
?>

<div class="mb-6">
    <a href="<?= BASE_URL ?>/admin/orders" class="text-brand hover:text-brand-dark"><i class="fas fa-arrow-left mr-2"></i>Back to Orders</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-500">Order Number</p>
                    <h1 class="text-2xl font-bold"><?= htmlspecialchars($order['order_number']) ?></h1>
                </div>
                <span class="badge badge-info"><?= ucfirst($order['order_type']) ?></span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Customer</p>
                    <p class="font-semibold"><?= htmlspecialchars($order['email']) ?></p>
                    <?php if (!empty($order['phone'])): ?>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($order['phone']) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Payment Status</p>
                    <p class="font-semibold"><?= ucfirst($order['payment_status']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Items</h2>
            <div class="space-y-4">
                <?php foreach ($order['items'] as $item): ?>
                    <div class="flex justify-between border-b pb-3">
                        <div>
                            <p class="font-semibold"><?= htmlspecialchars($item['product_name']) ?></p>
                            <?php if (!empty($item['options'])): ?>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($item['options']) ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-500">Qty: <?= $item['quantity'] ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold"><?= Helper::formatCurrency($item['price']) ?></p>
                            <p class="text-sm text-gray-500">Subtotal: <?= Helper::formatCurrency($item['subtotal']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Totals</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span><?= Helper::formatCurrency($order['subtotal']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Tax</span>
                    <span><?= Helper::formatCurrency($order['tax_amount']) ?></span>
                </div>
            <?php if ($order['shipping_amount'] > 0): ?>
                <div class="flex justify-between">
                    <span>Delivery</span>
                    <span><?= Helper::formatCurrency($order['shipping_amount']) ?></span>
                </div>
            <?php endif; ?>
                <div class="flex justify-between font-bold border-t pt-3">
                    <span>Total</span>
                    <span><?= Helper::formatCurrency($order['total']) ?></span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Status</h2>
            <form method="POST" action="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>/status">
                <?= $csrfField ?? '' ?>
                <select name="status" class="form-select mb-4">
                    <?php
                    $statuses = ['pending','processing','confirmed','preparing','ready','out_for_delivery','delivered','cancelled','refunded'];
                    foreach ($statuses as $status): ?>
                        <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ', $status)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary w-full">Update Status</button>
            </form>
        </div>

        <?php if (!empty($payments)): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Payments</h2>
            <?php foreach ($payments as $payment): ?>
                <div class="border rounded-lg p-4 mb-3">
                    <p class="font-semibold"><?= ucfirst($payment['gateway']) ?> - <?= ucfirst($payment['status']) ?></p>
                    <p class="text-sm text-gray-600"><?= Helper::formatCurrency($payment['amount']) ?> • <?= date('M d, Y g:i A', strtotime($payment['created_at'])) ?></p>
                    <?php if ($payment['transaction_id']): ?>
                        <p class="text-sm text-gray-500">Txn ID: <?= htmlspecialchars($payment['transaction_id']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

