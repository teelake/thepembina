<?php
$content = ob_start();
function settingVal($settings, $key, $default = '')
{
    foreach ($settings as $setting) {
        if ($setting['key'] === $key) {
            return $setting['value'];
        }
    }
    return $default;
}
$enabled = settingVal($settings, 'payment_square_enabled', '0') === '1';
$sandbox = settingVal($settings, 'payment_square_sandbox', '1') === '1';
?>

<div class="max-w-3xl">
    <h1 class="text-3xl font-bold mb-6">Square Payment Settings</h1>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/admin/settings/payment">
        <?= $csrfField ?? '' ?>
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="form-group">
                <label class="form-label">Enable Square</label>
                <select name="payment_square_enabled" class="form-select">
                    <option value="1" <?= $enabled ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= !$enabled ? 'selected' : '' ?>>Disabled</option>
                </select.
            </div>
            <div class="form-group">
                <label class="form-label">Application ID</label>
                <input type="text" name="payment_square_app_id" class="form-input" value="<?= htmlspecialchars(settingVal($settings, 'payment_square_app_id')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Access Token</label>
                <input type="text" name="payment_square_access_token" class="form-input" value="<?= htmlspecialchars(settingVal($settings, 'payment_square_access_token')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Location ID</label>
                <input type="text" name="payment_square_location_id" class="form-input" value="<?= htmlspecialchars(settingVal($settings, 'payment_square_location_id')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Mode</label>
                <select name="payment_square_sandbox" class="form-select">
                    <option value="1" <?= $sandbox ? 'selected' : '' ?>>Sandbox (Testing)</option>
                    <option value="0" <?= !$sandbox ? 'selected' : '' ?>>Live</option>
                </select>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">Save Payment Settings</button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

