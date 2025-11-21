<?php
$isEdit = isset($testimonial);
$testimonial = $testimonial ?? [];
$content = ob_start();
?>

<div class="max-w-3xl">
    <h1 class="text-3xl font-bold mb-6"><?= $isEdit ? 'Edit' : 'Add' ?> Testimonial</h1>
    
    <form method="POST" action="<?= BASE_URL ?>/admin/testimonials<?= $isEdit ? '/' . $testimonial['id'] : '' ?>" data-validate>
        <?= $csrfField ?? '' ?>
        
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" required class="form-input" value="<?= htmlspecialchars($testimonial['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Location / Title</label>
                    <input type="text" name="title" class="form-input" value="<?= htmlspecialchars($testimonial['title'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Message *</label>
                <textarea name="message" required class="form-textarea" rows="4"><?= htmlspecialchars($testimonial['message'] ?? '') ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="form-label">Rating</label>
                    <select name="rating" class="form-select">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= (isset($testimonial['rating']) && (int)$testimonial['rating'] === $i) ? 'selected' : '' ?>><?= $i ?> Stars</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="published" <?= (isset($testimonial['status']) && $testimonial['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= (isset($testimonial['status']) && $testimonial['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-input" value="<?= htmlspecialchars($testimonial['sort_order'] ?? 0) ?>">
                </div>
            </div>
            
            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="<?= BASE_URL ?>/admin/testimonials" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Update' : 'Create' ?>
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>
<?php
$isEdit = isset($testimonial);
$testimonial = $testimonial ?? [];
$content = ob_start();
?>

<div class="max-w-3xl">
    <h1 class="text-3xl font-bold mb-6"><?= $isEdit ? 'Edit' : 'Create' ?> Testimonial</h1>
    
    <form method="POST" action="<?= BASE_URL ?>/admin/testimonials<?= $isEdit ? '/' . $testimonial['id'] : '' ?>" data-validate>
        <?= $csrfField ?? '' ?>
        
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" required class="form-input" value="<?= htmlspecialchars($testimonial['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Title / Location</label>
                    <input type="text" name="title" class="form-input" value="<?= htmlspecialchars($testimonial['title'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Testimonial *</label>
                <textarea name="message" required class="form-textarea" rows="4"><?= htmlspecialchars($testimonial['message'] ?? '') ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="form-label">Rating</label>
                    <select name="rating" class="form-select">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= (isset($testimonial['rating']) && $testimonial['rating'] == $i) ? 'selected' : '' ?>><?= $i ?> stars</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="published" <?= (isset($testimonial['status']) && $testimonial['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= (isset($testimonial['status']) && $testimonial['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-input" value="<?= htmlspecialchars($testimonial['sort_order'] ?? 0) ?>">
                </div>
            </div>
            
            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="<?= BASE_URL ?>/admin/testimonials" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Update' : 'Create' ?> Testimonial
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

