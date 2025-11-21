<?php
$content = ob_start();

function settingValue($settings, $key, $default = '')
{
    foreach ($settings as $setting) {
        if ($setting['key'] === $key) {
            return $setting['value'];
        }
    }
    return $default;
}
?>

<div class="max-w-3xl">
    <h1 class="text-3xl font-bold mb-6">General Settings</h1>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/admin/settings">
        <?= $csrfField ?? '' ?>
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="form-group">
                <label class="form-label">Site Name</label>
                <input type="text" name="site_name" class="form-input" value="<?= htmlspecialchars(settingValue($settings, 'site_name')) ?>">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Site Email</label>
                    <input type="email" name="site_email" class="form-input" value="<?= htmlspecialchars(settingValue($settings, 'site_email')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Site Phone</label>
                    <input type="text" name="site_phone" class="form-input" value="<?= htmlspecialchars(settingValue($settings, 'site_phone')) ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Business Address</label>
                <textarea name="site_address" class="form-textarea" rows="3"><?= htmlspecialchars(settingValue($settings, 'site_address')) ?></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> Save Settings
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

