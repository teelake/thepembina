<?php
$content = ob_start();
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold mb-2">Navigation Management</h1>
    <p class="text-gray-600">Manage which categories appear in the main navigation menu</p>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
        <p><?= htmlspecialchars($_GET['success']) ?></p>
    </div>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
        <p><?= htmlspecialchars($_GET['error']) ?></p>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-semibold mb-4">Main Navigation Categories</h2>
    <p class="text-sm text-gray-600 mb-6">Select which categories should appear as direct links in the header navigation.</p>
    
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
                <i class="fas fa-save mr-2"></i> Save Navigation Settings
            </button>
        </div>
    </form>
</div>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
    <div class="flex items-start">
        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
        <div class="text-sm text-blue-800">
            <p class="font-semibold mb-1">How it works:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>Checked categories will appear as direct links in the header navigation</li>
                <li>Unchecked categories will appear in the "More" dropdown menu</li>
                <li>Use the "Order" field to control the display order (lower numbers appear first)</li>
                <li>Only active categories with products are shown</li>
            </ul>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

