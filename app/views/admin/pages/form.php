<?php
$isEdit = isset($pageData);
$pageData = $pageData ?? [];
$content = ob_start();
?>

<div class="max-w-4xl">
    <h1 class="text-3xl font-bold mb-6"><?= $isEdit ? 'Edit' : 'Create' ?> Page</h1>
    
    <form method="POST" action="<?= BASE_URL ?>/admin/pages<?= $isEdit ? '/' . $pageData['id'] : '' ?>" data-validate>
        <?= $this->csrf->getTokenField() ?>
        
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" required class="form-input" value="<?= htmlspecialchars($pageData['title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-input" value="<?= htmlspecialchars($pageData['slug'] ?? '') ?>" placeholder="auto-generated if left blank">
            </div>
            <div class="form-group">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-textarea tinymce" rows="10"><?= htmlspecialchars($pageData['content'] ?? '') ?></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-input" value="<?= htmlspecialchars($pageData['meta_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-textarea" rows="2"><?= htmlspecialchars($pageData['meta_description'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="published" <?= (isset($pageData['status']) && $pageData['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= (isset($pageData['status']) && $pageData['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                </select>
            </div>
            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="<?= BASE_URL ?>/admin/pages" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Update' : 'Create' ?> Page
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$additional_js[] = 'https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js';
$additional_js[] = BASE_URL . '/public/js/tinymce-init.js';
require_once APP_PATH . '/views/layouts/admin.php';
?>

