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
$defaultGateway = settingVal($settings, 'payment_default_gateway', 'square');
$squareEnabled = settingVal($settings, 'payment_square_enabled', '1') === '1';
$squareSandbox = settingVal($settings, 'payment_square_sandbox', '1') === '1';
$paystackEnabled = settingVal($settings, 'payment_paystack_enabled', '0') === '1';
?>

<div class="max-w-4xl">
    <h1 class="text-3xl font-bold mb-6">Payment Integrations</h1>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/admin/settings/payment" class="space-y-6">
        <?= $csrfField ?? '' ?>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Default Gateway</h2>
            <p class="text-sm text-gray-500 mb-4">Select which payment processor should be used at checkout.</p>
            <select name="payment_default_gateway" class="form-select w-full md:w-1/2">
                <option value="square" <?= $defaultGateway === 'square' ? 'selected' : '' ?>>Square</option>
                <option value="paystack" <?= $defaultGateway === 'paystack' ? 'selected' : '' ?>>Paystack</option>
                <option value="manual" <?= $defaultGateway === 'manual' ? 'selected' : '' ?>>Manual / Offline</option>
            </select>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Square Settings</h2>
                    <p class="text-sm text-gray-500">Enable Square for in-person and online card payments.</p>
                </div>
                <select name="payment_square_enabled" class="form-select w-32">
                    <option value="1" <?= $squareEnabled ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= !$squareEnabled ? 'selected' : '' ?>>Disabled</option>
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Application ID</label>
                    <input type="text" name="payment_square_app_id" class="form-input" value="<?= htmlspecialchars(settingVal($settings, 'payment_square_app_id')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Location ID</label>
                    <input type="text" name="payment_square_location_id" class="form-input" value="<?= htmlspecialchars(settingVal($settings, 'payment_square_location_id')) ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Access Token</label>
                <input type="text" name="payment_square_access_token" class="form-input" value="<?= htmlspecialchars(settingVal($settings, 'payment_square_access_token')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Mode</label>
                <select name="payment_square_sandbox" class="form-select">
                    <option value="1" <?= $squareSandbox ? 'selected' : '' ?>>Sandbox / Testing</option>
                    <option value="0" <?= !$squareSandbox ? 'selected' : '' ?>>Live / Production</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Paystack Settings</h2>
                    <p class="text-sm text-gray-500">Enable Paystack for Nigerian or international card payments.</p>
                </div>
                <select name="payment_paystack_enabled" class="form-select w-32">
                    <option value="1" <?= $paystackEnabled ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= !$paystackEnabled ? 'selected' : '' ?>>Disabled</option>
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Public Key</label>
                    <input type="text" name="payment_paystack_public_key" class="form-input" value="<?= htmlspecialchars(settingVal($settings, 'payment_paystack_public_key')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Secret Key</label>
                    <input type="text" name="payment_paystack_secret_key" class="form-input" value="<?= htmlspecialchars(settingVal($settings, 'payment_paystack_secret_key')) ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Merchant Email</label>
                <input type="email" name="payment_paystack_merchant_email" class="form-input" value="<?= htmlspecialchars(settingVal($settings, 'payment_paystack_merchant_email')) ?>">
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i> Save Payment Settings
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

