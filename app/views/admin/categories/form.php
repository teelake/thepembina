<?php
$isEdit = isset($category);
$category = $category ?? [];
$content = ob_start();
?>

<div class="max-w-3xl">
    <h1 class="text-3xl font-bold mb-6"><?= $isEdit ? 'Edit' : 'Create' ?> Category</h1>
    
    <form method="POST" action="<?= BASE_URL ?>/admin/categories<?= $isEdit ? '/' . $category['id'] : '' ?>" enctype="multipart/form-data" data-validate>
        <?= $csrfField ?? '' ?>
        
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" required class="form-input" value="<?= htmlspecialchars($category['name'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" rows="4"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-input" value="<?= htmlspecialchars($category['sort_order'] ?? 0) ?>">
                    <p class="text-xs text-gray-500 mt-1">Controls display order in category lists</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (isset($category['status']) && $category['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (isset($category['status']) && $category['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" accept="image/*" class="form-input" data-preview="category-image-preview">
                    <?php if (!empty($category['image'])): ?>
                        <img id="category-image-preview" src="<?= BASE_URL ?>/public/<?= htmlspecialchars($category['image']) ?>" alt="Category image" class="image-preview">
                    <?php else: ?>
                        <img id="category-image-preview" src="" alt="Preview" class="image-preview" style="display:none;">
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Navigation Settings -->
            <div class="border-t pt-6 mt-6">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-bars mr-2 text-brand"></i>
                    Navigation Settings
                </h3>
                <p class="text-sm text-gray-600 mb-4">Control how this category appears in the main website navigation</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="show_in_nav" value="1" 
                                   class="w-5 h-5 text-brand border-gray-300 rounded focus:ring-brand" 
                                   <?= (isset($category['show_in_nav']) && $category['show_in_nav'] == 1) ? 'checked' : '' ?>>
                            <span class="ml-3 font-medium">Show in Main Navigation</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Display as a direct link in the header (max 3 categories)</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Navigation Order</label>
                        <input type="number" name="nav_order" class="form-input" 
                               value="<?= htmlspecialchars($category['nav_order'] ?? 0) ?>" 
                               min="0" max="100">
                        <p class="text-xs text-gray-500 mt-1">Lower numbers appear first (0-100)</p>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="<?= BASE_URL ?>/admin/categories" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Update' : 'Create' ?> Category
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

