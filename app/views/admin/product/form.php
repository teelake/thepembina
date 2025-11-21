<?php
$isEdit = isset($product);
$product = $product ?? [];
$content = ob_start();
?>

<div class="max-w-4xl">
    <h1 class="text-3xl font-bold mb-6"><?= $isEdit ? 'Edit' : 'Create' ?> Product</h1>
    
    <form method="POST" action="<?= BASE_URL ?>/admin/products<?= $isEdit ? '/' . $product['id'] : '' ?>" 
          enctype="multipart/form-data" data-validate>
        <?= $csrfField ?? '' ?>
        
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <!-- Basic Information -->
            <div>
                <h2 class="text-xl font-bold mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="name" required class="form-input" 
                               value="<?= htmlspecialchars($product['name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-input" 
                               value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                        <?= (isset($product['category_id']) && $product['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Price (CAD) *</label>
                        <input type="number" name="price" step="0.01" required class="form-input" 
                               value="<?= htmlspecialchars($product['price'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Compare Price</label>
                        <input type="number" name="compare_price" step="0.01" class="form-input" 
                               value="<?= htmlspecialchars($product['compare_price'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" class="form-textarea" rows="2"><?= htmlspecialchars($product['short_description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-textarea" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Image -->
            <div>
                <h2 class="text-xl font-bold mb-4">Product Image</h2>
                <div class="form-group">
                    <label class="form-label">Upload Image</label>
                    <input type="file" name="image" accept="image/*" class="form-input" data-preview="image-preview">
                    <?php if (isset($product['image']) && $product['image']): ?>
                        <img id="image-preview" src="<?= BASE_URL ?>/public/<?= htmlspecialchars($product['image']) ?>" 
                             alt="Current image" class="image-preview">
                    <?php else: ?>
                        <img id="image-preview" src="" alt="Preview" class="image-preview" style="display: none;">
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Inventory -->
            <div>
                <h2 class="text-xl font-bold mb-4">Inventory</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="flex items-center">
                            <input type="checkbox" name="manage_stock" value="1" 
                                   <?= (isset($product['manage_stock']) && $product['manage_stock']) ? 'checked' : '' ?> 
                                   class="mr-2">
                            <span>Manage Stock</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-input" 
                               value="<?= htmlspecialchars($product['stock_quantity'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Stock Status</label>
                        <select name="stock_status" class="form-select">
                            <option value="in_stock" <?= (isset($product['stock_status']) && $product['stock_status'] === 'in_stock') ? 'selected' : '' ?>>In Stock</option>
                            <option value="out_of_stock" <?= (isset($product['stock_status']) && $product['stock_status'] === 'out_of_stock') ? 'selected' : '' ?>>Out of Stock</option>
                            <option value="on_backorder" <?= (isset($product['stock_status']) && $product['stock_status'] === 'on_backorder') ? 'selected' : '' ?>>On Backorder</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Settings -->
            <div>
                <h2 class="text-xl font-bold mb-4">Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= (isset($product['status']) && $product['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (isset($product['status']) && $product['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            <option value="draft" <?= (isset($product['status']) && $product['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-input" 
                               value="<?= htmlspecialchars($product['sort_order'] ?? 0) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" value="1" 
                                   <?= (isset($product['is_featured']) && $product['is_featured']) ? 'checked' : '' ?> 
                                   class="mr-2">
                            <span>Featured Product</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- SEO -->
            <div>
                <h2 class="text-xl font-bold mb-4">SEO Settings</h2>
                <div class="grid grid-cols-1 gap-4">
                    <div class="form-group">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-input" 
                               value="<?= htmlspecialchars($product['meta_title'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-textarea" rows="2"><?= htmlspecialchars($product['meta_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="<?= BASE_URL ?>/admin/products" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Update' : 'Create' ?> Product
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$page_title = ($isEdit ? 'Edit' : 'Create') . ' Product';
require_once APP_PATH . '/views/layouts/admin.php';
?>

