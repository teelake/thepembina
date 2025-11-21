<?php
$content = ob_start();
?>

<div class="flex justify-between items-center mb  -6">
    <h1 class="text-3xl font-bold">Categories</h1>
    <a href="<?= BASE_URL ?>/admin/categories/create" class="btn btn-primary">
        <i class="fas fa-plus mr-2"></i> Add Category
    </a>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if (!empty($categories)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Sort Order</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($categories as $category): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <?php if (!empty($category['image'])): ?>
                                    <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($category['image']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="w-12 h-12 rounded mr-4 object-cover">
                                <?php endif; ?>
                                <div>
                                    <div class="font-semibold"><?= htmlspecialchars($category['name']) ?></div>
                                    <?php if ($category['description']): ?>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars(mb_strimwidth($category['description'], 0, 60, '...')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge <?= $category['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>">
                                <?= ucfirst($category['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4"><?= $category['sort_order'] ?></td>
                        <td class="px-6 py-4 text-right">
                            <a href="<?= BASE_URL ?>/admin/categories/<?= $category['id'] ?>/edit" class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/categories/<?= $category['id'] ?>/delete" class="inline" data-confirm-delete>
                                <?= $csrfField ?? '' ?>
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
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
        <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">No categories yet</h2>
        <p class="text-gray-500 mb-6">Start by adding categories to organise your menu.</p>
        <a href="<?= BASE_URL ?>/admin/categories/create" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Add Category
        </a>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

