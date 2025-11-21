<?php
$isEdit = isset($user);
$user = $user ?? [];
$content = ob_start();
?>

<div class="max-w-3xl">
    <h1 class="text-3xl font-bold mb-6"><?= $isEdit ? 'Edit' : 'Create' ?> User</h1>
    
    <form method="POST" action="<?= BASE_URL ?>/admin/users<?= $isEdit ? '/' . $user['id'] : '' ?>" data-validate>
        <?= $csrfField ?? '' ?>
        
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" required class="form-input" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" required class="form-input" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" required class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label"><?= $isEdit ? 'New Password (optional)' : 'Password *' ?></label>
                <input type="password" name="password" class="form-input" <?= $isEdit ? '' : 'required' ?>>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-select">
                        <option value="1" <?= (isset($user['role_id']) && $user['role_id'] == 1) ? 'selected' : '' ?>>Super Admin</option>
                        <option value="2" <?= (isset($user['role_id']) && $user['role_id'] == 2) ? 'selected' : '' ?>>Admin</option>
                        <option value="3" <?= (isset($user['role_id']) && $user['role_id'] == 3) ? 'selected' : '' ?>>Data Entry</option>
                        <option value="4" <?= (isset($user['role_id']) && $user['role_id'] == 4) ? 'selected' : '' ?>>Customer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (isset($user['status']) && $user['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (isset($user['status']) && $user['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        <option value="suspended" <?= (isset($user['status']) && $user['status'] === 'suspended') ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Update' : 'Create' ?> User
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

