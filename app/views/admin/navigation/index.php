<?php
$content = ob_start();
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold mb-2">Navigation Management</h1>
        <p class="text-gray-600">Manage your website navigation menu. Add categories, pages, or custom links.</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/navigation/create" class="btn btn-primary">
        <i class="fas fa-plus mr-2"></i> Add Menu Item
    </a>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success mb-6"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error mb-6"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold">Menu Items</h2>
        <p class="text-sm text-gray-600 mt-1">Drag to reorder or click to edit</p>
    </div>

    <?php if (!empty($menuItems)): ?>
        <div class="divide-y divide-gray-200" id="menu-items-list">
            <?php foreach ($menuItems as $item): ?>
                <div class="p-4 hover:bg-gray-50 transition" data-item-id="<?= $item['id'] ?>">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-brand text-white font-bold mr-4 cursor-move">
                                <i class="fas fa-grip-vertical"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <?php if ($item['icon']): ?>
                                        <i class="<?= htmlspecialchars($item['icon']) ?> text-brand"></i>
                                    <?php endif; ?>
                                    <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($item['label']) ?></h4>
                                    <span class="badge <?= 
                                        $item['type'] === 'category' ? 'badge-info' : 
                                        ($item['type'] === 'page' ? 'badge-success' : 'badge-warning') 
                                    ?>">
                                        <?= ucfirst($item['type']) ?>
                                    </span>
                                    <?php if ($item['status'] === 'inactive'): ?>
                                        <span class="badge badge-warning">Inactive</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    <?php
                                    if ($item['type'] === 'category' && !empty($item['category_name'])) {
                                        echo 'Category: ' . htmlspecialchars($item['category_name']);
                                    } elseif ($item['type'] === 'page' && !empty($item['page_title'])) {
                                        echo 'Page: ' . htmlspecialchars($item['page_title']);
                                    } elseif ($item['type'] === 'custom' && !empty($item['url'])) {
                                        echo 'URL: ' . htmlspecialchars($item['url']);
                                    }
                                    ?>
                                    <?php if ($item['target'] === '_blank'): ?>
                                        <span class="ml-2 text-xs">(Opens in new tab)</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-500">Order: <?= $item['order'] ?></span>
                            <a href="<?= BASE_URL ?>/admin/navigation/<?= $item['id'] ?>/edit" 
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" 
                                  action="<?= BASE_URL ?>/admin/navigation/<?= $item['id'] ?>/delete" 
                                  class="inline" 
                                  data-confirm-delete>
                                <?= $csrfField ?? '' ?>
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="p-12 text-center">
            <i class="fas fa-bars text-6xl text-gray-300 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-700 mb-2">No menu items yet</h2>
            <p class="text-gray-500 mb-6">Start by adding menu items to your navigation.</p>
            <a href="<?= BASE_URL ?>/admin/navigation/create" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Add Menu Item
            </a>
        </div>
    <?php endif; ?>
    <?php if (empty($menuItems)): ?>
        <div class="p-12 text-center">
            <i class="fas fa-bars text-6xl text-gray-300 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-700 mb-2">No menu items yet</h2>
            <p class="text-gray-500 mb-6">Start by adding menu items to your navigation.</p>
            <a href="<?= BASE_URL ?>/admin/navigation/create" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Add Menu Item
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="bg-white rounded-lg shadow-md p-6 mt-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-xl font-bold">Category Shortcuts</h2>
            <p class="text-sm text-gray-600">Select which product categories appear directly in the header (fallback if custom items are disabled).</p>
        </div>
        <span class="text-xs uppercase tracking-wider bg-gray-100 text-gray-600 px-3 py-1 rounded-full">Optional</span>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/navigation/update" class="space-y-4">
        <?= $csrfField ?? '' ?>

        <div class="space-y-3">
            <?php foreach ($categories as $category): ?>
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center space-x-4">
                        <input type="checkbox"
                               name="nav_categories[]"
                               value="<?= $category['id'] ?>"
                               id="nav_cat_<?= $category['id'] ?>"
                               <?= (isset($category['show_in_nav']) && $category['show_in_nav'] == 1) ? 'checked' : '' ?>
                               class="w-5 h-5 text-brand border-gray-300 rounded focus:ring-brand">
                        <label for="nav_cat_<?= $category['id'] ?>" class="font-medium text-gray-700 cursor-pointer">
                            <?= htmlspecialchars($category['name']) ?>
                            <span class="text-sm text-gray-500 ml-2">(<?= $category['product_count'] ?? 0 ?> products)</span>
                        </label>
                    </div>
                    <?php if (isset($category['show_in_nav']) && $category['show_in_nav'] == 1): ?>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm text-gray-600">Order:</label>
                            <input type="number"
                                   name="nav_order[<?= $category['id'] ?>]"
                                   value="<?= $category['nav_order'] ?? 0 ?>"
                                   min="0"
                                   class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="flex justify-end pt-4 border-t border-gray-200">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i> Save Category Links
            </button>
        </div>
    </form>
</div>

<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-start">
        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
        <div class="text-sm text-blue-800">
            <p class="font-semibold mb-1">Menu Item Types:</p>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>Category:</strong> Links to a product category (e.g., Food, Drinks)</li>
                <li><strong>Page:</strong> Links to a content page (e.g., About, Contact)</li>
                <li><strong>Custom:</strong> Links to any URL (internal or external)</li>
            </ul>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>
