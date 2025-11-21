<?php
$isEdit = isset($permission);
$permission = $permission ?? [];
$content = ob_start();
?>

<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold"><?= $isEdit ? 'Edit Permission' : 'Create Permission' ?></h1>
        <a href="<?= BASE_URL ?>/admin/permissions" class="text-brand hover:text-brand-dark"><i class="fas fa-arrow-left mr-2"></i> Back to Permissions</a>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/permissions<?= $isEdit ? '/' . $permission['id'] : '' ?>" data-validate>
        <?= $csrfField ?? '' ?>
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" required class="form-input" value="<?= htmlspecialchars($permission['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-input" value="<?= htmlspecialchars($permission['slug'] ?? '') ?>" placeholder="auto-generated if blank">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" rows="3"><?= htmlspecialchars($permission['description'] ?? '') ?></textarea>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="<?= BASE_URL ?>/admin/permissions" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Update Permission' : 'Create Permission' ?>
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>


