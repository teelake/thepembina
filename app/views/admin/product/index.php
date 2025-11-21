<?php
use App\Core\Helper;
$content = ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Products</h1>
    <div class="flex gap-4">
        <form method="POST" action="<?= BASE_URL ?>/admin/products/import" enctype="multipart/form-data" class="flex items-center gap-2">
            <?= $csrfField ?? '' ?>
            <label class="cursor-pointer text-sm font-semibold">
                <input type="file" name="import_file" accept=".csv" required class="hidden" onchange="this.form.submit()">
                <span class="btn btn-secondary inline-flex items-center">
                    <i class="fas fa-file-import mr-2"></i> Import CSV
                </span>
            </label>
        </form>
        <a href="<?= BASE_URL ?>/admin/products/create" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Add Product
        </a>
    </div>
</div>

<?php if (!empty($products)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($products as $product): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <?php if ($product['image']): ?>
                                <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($product['image']) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="w-16 h-16 object-cover rounded">
                            <?php else: ?>
                                <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold"><?= htmlspecialchars($product['name']) ?></div>
                            <?php if ($product['is_featured']): ?>
                                <span class="text-xs text-brand">⭐ Featured</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($product['sku'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 font-semibold"><?= Helper::formatCurrency($product['price']) ?></td>
                        <td class="px-6 py-4">
                            <?php if ($product['manage_stock']): ?>
                                <span class="text-sm"><?= $product['stock_quantity'] ?? 0 ?></span>
                            <?php else: ?>
                                <span class="text-sm text-gray-400">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge <?= $product['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>">
                                <?= ucfirst($product['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="<?= BASE_URL ?>/admin/products/<?= $product['id'] ?>/edit" 
                                   class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/admin/products/<?= $product['id'] ?>/delete" 
                                      class="inline" data-confirm-delete>
                                    <?= $csrfField ?? '' ?>
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
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
                    <a href="?page=<?= $currentPage - 1 ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $currentPage): ?>
                        <span class="px-4 py-2 bg-brand text-white rounded-lg font-semibold"><?= $i ?></span>
                    <?php elseif ($i == 1 || $i == $totalPages || ($i >= $currentPage - 2 && $i <= $currentPage + 2)): ?>
                        <a href="?page=<?= $i ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Next
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <i class="fas fa-box text-6xl text-gray-300 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">No products found</h2>
        <p class="text-gray-500 mb-6">Get started by adding your first product</p>
        <a href="<?= BASE_URL ?>/admin/products/create" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Add Product
        </a>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

