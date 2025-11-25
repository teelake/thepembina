<?php
$isEdit = isset($item);
$item = $item ?? [];
$content = ob_start();
?>

<div class="max-w-3xl">
    <h1 class="text-3xl font-bold mb-6"><?= $isEdit ? 'Edit' : 'Add' ?> Menu Item</h1>
    
    <form method="POST" action="<?= BASE_URL ?>/admin/navigation<?= $isEdit ? '/' . $item['id'] : '' ?>" id="menu-item-form">
        <?= $csrfField ?? '' ?>
        
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <!-- Basic Info -->
            <div class="form-group">
                <label class="form-label">Label *</label>
                <input type="text" name="label" required class="form-input" 
                       value="<?= htmlspecialchars($item['label'] ?? '') ?>"
                       placeholder="Menu item text (e.g., 'About Us', 'Food', 'Contact')">
                <p class="text-xs text-gray-500 mt-1">This is the text that appears in the navigation</p>
            </div>

            <!-- Type Selection -->
            <div class="form-group">
                <label class="form-label">Type *</label>
                <select name="type" id="menu-type" required class="form-select">
                    <option value="">Select Type</option>
                    <option value="category" <?= (isset($item['type']) && $item['type'] === 'category') ? 'selected' : '' ?>>Category</option>
                    <option value="page" <?= (isset($item['type']) && $item['type'] === 'page') ? 'selected' : '' ?>>Page</option>
                    <option value="custom" <?= (isset($item['type']) && $item['type'] === 'custom') ? 'selected' : '' ?>>Custom Link</option>
                </select>
            </div>

            <!-- Category Selection (shown when type=category) -->
            <div class="form-group" id="category-field" style="display: none;">
                <label class="form-label">Category *</label>
                <select name="category_id" class="form-select">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" 
                                <?= (isset($item['category_id']) && $item['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Page Selection (shown when type=page) -->
            <div class="form-group" id="page-field" style="display: none;">
                <label class="form-label">Page *</label>
                <select name="page_id" class="form-select">
                    <option value="">Select Page</option>
                    <?php foreach ($pages as $page): ?>
                        <option value="<?= $page['id'] ?>" 
                                <?= (isset($item['page_id']) && $item['page_id'] == $page['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($page['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Custom URL (shown when type=custom) -->
            <div class="form-group" id="url-field" style="display: none;">
                <label class="form-label">URL *</label>
                <input type="url" name="url" class="form-input" 
                       value="<?= htmlspecialchars($item['url'] ?? '') ?>"
                       placeholder="https://example.com or /page/slug">
                <p class="text-xs text-gray-500 mt-1">Enter full URL (https://...) or relative path (/page/about)</p>
            </div>

            <!-- Additional Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Order</label>
                    <input type="number" name="order" class="form-input" 
                           value="<?= htmlspecialchars($item['order'] ?? 0) ?>" 
                           min="0" max="100">
                    <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (isset($item['status']) && $item['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (isset($item['status']) && $item['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Link Target</label>
                    <select name="target" class="form-select">
                        <option value="_self" <?= (isset($item['target']) && $item['target'] === '_self') ? 'selected' : '' ?>>Same Window</option>
                        <option value="_blank" <?= (isset($item['target']) && $item['target'] === '_blank') ? 'selected' : '' ?>>New Tab</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Icon (Optional)</label>
                    <input type="text" name="icon" class="form-input" 
                           value="<?= htmlspecialchars($item['icon'] ?? '') ?>"
                           placeholder="fas fa-home">
                    <p class="text-xs text-gray-500 mt-1">Font Awesome icon class (e.g., fas fa-home)</p>
                </div>
            </div>
            
            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="<?= BASE_URL ?>/admin/navigation" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Update' : 'Create' ?> Menu Item
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('menu-type');
    const categoryField = document.getElementById('category-field');
    const pageField = document.getElementById('page-field');
    const urlField = document.getElementById('url-field');
    const form = document.getElementById('menu-item-form');

    function toggleFields() {
        const type = typeSelect.value;
        categoryField.style.display = type === 'category' ? 'block' : 'none';
        pageField.style.display = type === 'page' ? 'block' : 'none';
        urlField.style.display = type === 'custom' ? 'block' : 'none';
        
        // Update required attributes
        categoryField.querySelector('select').required = type === 'category';
        pageField.querySelector('select').required = type === 'page';
        urlField.querySelector('input').required = type === 'custom';
    }

    typeSelect.addEventListener('change', toggleFields);
    toggleFields(); // Initial call

    // Form validation
    form.addEventListener('submit', function(e) {
        const type = typeSelect.value;
        if (type === 'category' && !categoryField.querySelector('select').value) {
            e.preventDefault();
            alert('Please select a category');
            return false;
        }
        if (type === 'page' && !pageField.querySelector('select').value) {
            e.preventDefault();
            alert('Please select a page');
            return false;
        }
        if (type === 'custom' && !urlField.querySelector('input').value) {
            e.preventDefault();
            alert('Please enter a URL');
            return false;
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>


