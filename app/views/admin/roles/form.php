<?php
$isEdit = isset($role);
$role = $role ?? [];
$assignedPermissions = $assignedPermissions ?? [];
$content = ob_start();
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold"><?= $isEdit ? 'Edit Role' : 'Create Role' ?></h1>
        <a href="<?= BASE_URL ?>/admin/roles" class="text-brand hover:text-brand-dark"><i class="fas fa-arrow-left mr-2"></i> Back to Roles</a>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/roles<?= $isEdit ? '/' . $role['id'] : '' ?>" data-validate>
        <?= $csrfField ?? '' ?>

        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Role Name *</label>
                    <input type="text" name="name" required class="form-input" value="<?= htmlspecialchars($role['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-input" value="<?= htmlspecialchars($role['slug'] ?? '') ?>" placeholder="auto-generated if blank">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" rows="3"><?= htmlspecialchars($role['description'] ?? '') ?></textarea>
            </div>

            <div class="border rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xl font-semibold">Assign Permissions</h2>
                    <small class="text-gray-500">Control what this role can manage.</small>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach ($permissions as $permission): ?>
                        <label class="flex items-start gap-2 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="<?= $permission['id'] ?>"
                                <?= in_array($permission['id'], $assignedPermissions, true) ? 'checked' : '' ?>
                                class="mt-1">
                            <span>
                                <span class="font-semibold"><?= htmlspecialchars($permission['name']) ?></span>
                                <span class="block text-sm text-gray-500"><?= htmlspecialchars($permission['description'] ?? '') ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="<?= BASE_URL ?>/admin/roles" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Update Role' : 'Create Role' ?>
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>


