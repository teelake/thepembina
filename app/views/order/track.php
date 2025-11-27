<?php
$content = ob_start();
?>

<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Track Your Order</h1>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($order): ?>
                <!-- Order Found -->
                <div class="mb-6">
                    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
                        <p class="font-semibold">Order Found!</p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Order #<?= htmlspecialchars($order['order_number']) ?></h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <p class="text-sm text-gray-600">Order Date</p>
                                <p class="font-semibold"><?= date('M d, Y g:i A', strtotime($order['created_at'])) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Order Type</p>
                                <p class="font-semibold"><?= ucfirst($order['order_type']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Payment Status</p>
                                <p class="font-semibold">
                                    <span class="px-2 py-1 rounded <?= $order['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                        <?= ucfirst($order['payment_status']) ?>
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Order Status</p>
                                <p class="font-semibold">
                                    <span class="px-2 py-1 rounded 
                                        <?php
                                        $statusClass = 'bg-gray-100 text-gray-800';
                                        if ($order['status'] === 'completed') $statusClass = 'bg-green-100 text-green-800';
                                        elseif ($order['status'] === 'processing') $statusClass = 'bg-blue-100 text-blue-800';
                                        elseif ($order['status'] === 'cancelled') $statusClass = 'bg-red-100 text-red-800';
                                        echo $statusClass;
                                        ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <?php if (!empty($order['items'])): ?>
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-3">Order Items</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($order['items'] as $item): ?>
                                                <tr>
                                                    <td class="px-4 py-3 text-sm"><?= htmlspecialchars($item['product_name']) ?></td>
                                                    <td class="px-4 py-3 text-sm text-center"><?= $item['quantity'] ?></td>
                                                    <td class="px-4 py-3 text-sm text-right"><?= Helper::formatCurrency($item['subtotal']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="bg-gray-50">
                                            <tr>
                                                <td colspan="2" class="px-4 py-3 text-sm font-semibold text-right">Subtotal:</td>
                                                <td class="px-4 py-3 text-sm font-semibold text-right"><?= Helper::formatCurrency($order['subtotal']) ?></td>
                                            </tr>
                                            <?php if ($order['tax_amount'] > 0): ?>
                                                <tr>
                                                    <td colspan="2" class="px-4 py-3 text-sm font-semibold text-right">Tax:</td>
                                                    <td class="px-4 py-3 text-sm font-semibold text-right"><?= Helper::formatCurrency($order['tax_amount']) ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php if ($order['shipping_amount'] > 0): ?>
                                                <tr>
                                                    <td colspan="2" class="px-4 py-3 text-sm font-semibold text-right">Delivery Fee:</td>
                                                    <td class="px-4 py-3 text-sm font-semibold text-right"><?= Helper::formatCurrency($order['shipping_amount']) ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td colspan="2" class="px-4 py-3 text-lg font-bold text-right">Total:</td>
                                                <td class="px-4 py-3 text-lg font-bold text-right"><?= Helper::formatCurrency($order['total']) ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Status Timeline -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-3">Order Status Timeline</h3>
                            <div class="space-y-4">
                                <?php
                                $statuses = [
                                    'pending' => ['label' => 'Order Placed', 'icon' => 'fa-shopping-cart'],
                                    'processing' => ['label' => 'Processing', 'icon' => 'fa-cog'],
                                    'preparing' => ['label' => 'Preparing', 'icon' => 'fa-utensils'],
                                    'ready' => ['label' => 'Ready', 'icon' => 'fa-check-circle'],
                                    'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => 'fa-truck'],
                                    'completed' => ['label' => 'Completed', 'icon' => 'fa-check-double'],
                                    'cancelled' => ['label' => 'Cancelled', 'icon' => 'fa-times-circle']
                                ];
                                
                                $currentStatus = strtolower($order['status']);
                                $statusOrder = ['pending', 'processing', 'preparing', 'ready', 'out_for_delivery', 'completed'];
                                
                                foreach ($statusOrder as $status) {
                                    if (isset($statuses[$status])) {
                                        $isActive = false;
                                        $isCompleted = false;
                                        
                                        if ($currentStatus === $status) {
                                            $isActive = true;
                                        } elseif (array_search($currentStatus, $statusOrder) > array_search($status, $statusOrder)) {
                                            $isCompleted = true;
                                        } elseif ($currentStatus === 'cancelled') {
                                            $isCompleted = false;
                                            $isActive = ($status === 'cancelled');
                                        }
                                        
                                        $statusInfo = $statuses[$status];
                                        ?>
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center
                                                    <?= $isCompleted ? 'bg-green-500 text-white' : ($isActive ? 'bg-brand text-white' : 'bg-gray-300 text-gray-600') ?>">
                                                    <i class="fas <?= $statusInfo['icon'] ?>"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <p class="text-sm font-medium <?= $isActive || $isCompleted ? 'text-gray-900' : 'text-gray-500' ?>">
                                                    <?= $statusInfo['label'] ?>
                                                </p>
                                            </div>
                                        </div>
                                        <?php
                                        if ($status !== 'completed' && $status !== 'cancelled') {
                                            echo '<div class="ml-5 border-l-2 border-gray-300 h-6"></div>';
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="<?= BASE_URL ?>/menu" class="bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-dark transition text-center">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Track Order Form -->
                <form method="POST" action="<?= BASE_URL ?>/track-order" class="space-y-6">
                    <?= $csrfField ?? '' ?>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg mb-6">
                        <p class="text-sm">Enter your order number and email address to track your order status.</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="order_number" class="form-label">Order Number</label>
                        <input type="text" 
                               id="order_number" 
                               name="order_number" 
                               class="form-input" 
                               value="<?= htmlspecialchars($order_number ?? '') ?>" 
                               placeholder="e.g., PP-20251127-CDB22A" 
                               required>
                        <p class="text-xs text-gray-500 mt-1">You can find your order number in your confirmation email.</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               value="<?= htmlspecialchars($email ?? '') ?>" 
                               placeholder="your@email.com" 
                               required>
                        <p class="text-xs text-gray-500 mt-1">The email address you used when placing the order.</p>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i> Track Order
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$page_title = 'Track Your Order';
require_once APP_PATH . '/views/layouts/main.php';
?>

