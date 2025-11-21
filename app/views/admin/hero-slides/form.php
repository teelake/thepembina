<?php
$isEdit = isset($slide);
$slide = $slide ?? [];
$content = ob_start();
?>

<div class="max-w-4xl">
    <h1 class="text-3xl font-bold mb-6"><?= $isEdit ? 'Edit' : 'Create' ?> Hero Slide</h1>
    
    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    
    <form method="POST" action="<?= BASE_URL ?>/admin/hero-slides<?= $isEdit ? '/' . $slide['id'] : '' ?>" enctype="multipart/form-data" data-validate>
        <?= $csrfField ?? '' ?>
        
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" required class="form-input" value="<?= htmlspecialchars($slide['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitle</label>
                    <input type="text" name="subtitle" class="form-input" value="<?= htmlspecialchars($slide['subtitle'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" rows="4"><?= htmlspecialchars($slide['description'] ?? '') ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Button Text</label>
                    <input type="text" name="button_text" class="form-input" value="<?= htmlspecialchars($slide['button_text'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Button Link</label>
                    <input type="text" name="button_link" class="form-input" value="<?= htmlspecialchars($slide['button_link'] ?? '') ?>">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-input" value="<?= htmlspecialchars($slide['sort_order'] ?? 0) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="published" <?= (isset($slide['status']) && $slide['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= (isset($slide['status']) && $slide['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Hero Image</label>
                <input type="file" name="image" accept="image/*" class="form-input" data-preview="slide-image-preview">
                <p class="text-sm text-gray-500 mt-1">Recommended size: 1600x900px</p>
                <?php if (!empty($slide['image'])): ?>
                    <img id="slide-image-preview" src="<?= BASE_URL ?>/public/<?= htmlspecialchars($slide['image']) ?>" alt="Slide image" class="image-preview">
                <?php else: ?>
                    <img id="slide-image-preview" src="" alt="Preview" class="image-preview" style="display: none;">
                <?php endif; ?>
            </div>
            
            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="<?= BASE_URL ?>/admin/hero-slides" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Update' : 'Create' ?> Slide
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

