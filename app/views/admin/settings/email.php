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
?>

<div class="max-w-4xl">
    <h1 class="text-3xl font-bold mb-6">Email Settings</h1>

    <?php if (!empty($_GET['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
            <p><?= htmlspecialchars($_GET['success']) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
            <p><?= htmlspecialchars($_GET['error']) ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">Email Configuration</p>
                <p>Configure SMTP settings for sending order confirmation emails and receipts. If SMTP is not configured, the system will use PHP's mail() function.</p>
            </div>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/settings/email" class="space-y-6">
        <?= $csrfField ?? '' ?>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">SMTP Server Settings</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" name="smtp_host" class="form-input" 
                           value="<?= htmlspecialchars(settingVal($settings, 'smtp_host', 'mail.thepembina.ca')) ?>"
                           placeholder="mail.thepembina.ca">
                    <p class="text-xs text-gray-500 mt-1">Your email server hostname</p>
                </div>
                <div class="form-group">
                    <label class="form-label">SMTP Port</label>
                    <input type="number" name="smtp_port" class="form-input" 
                           value="<?= htmlspecialchars(settingVal($settings, 'smtp_port', '587')) ?>"
                           placeholder="587">
                    <p class="text-xs text-gray-500 mt-1">Usually 587 (TLS) or 465 (SSL)</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">SMTP Authentication</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">SMTP Username</label>
                    <input type="text" name="smtp_user" class="form-input" 
                           value="<?= htmlspecialchars(settingVal($settings, 'smtp_user', 'no-reply@thepembina.ca')) ?>"
                           placeholder="no-reply@thepembina.ca">
                    <p class="text-xs text-gray-500 mt-1">Your email account username</p>
                </div>
                <div class="form-group">
                    <label class="form-label">SMTP Password</label>
                    <input type="password" name="smtp_pass" class="form-input" 
                           value="<?= htmlspecialchars(settingVal($settings, 'smtp_pass', '')) ?>"
                           placeholder="Enter password">
                    <p class="text-xs text-gray-500 mt-1">Your email account password</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Email From Settings</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">From Email Address</label>
                    <input type="email" name="smtp_from_email" class="form-input" 
                           value="<?= htmlspecialchars(settingVal($settings, 'smtp_from_email', 'no-reply@thepembina.ca')) ?>"
                           placeholder="no-reply@thepembina.ca">
                    <p class="text-xs text-gray-500 mt-1">Email address that appears as sender</p>
                </div>
                <div class="form-group">
                    <label class="form-label">From Name</label>
                    <input type="text" name="smtp_from_name" class="form-input" 
                           value="<?= htmlspecialchars(settingVal($settings, 'smtp_from_name', 'The Pembina Pint and Restaurant')) ?>"
                           placeholder="The Pembina Pint and Restaurant">
                    <p class="text-xs text-gray-500 mt-1">Name that appears as sender</p>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                <div class="text-sm text-yellow-800">
                    <p class="font-semibold mb-1">Testing Your Email Settings</p>
                    <p>After saving, place a test order to verify emails are being sent correctly. Check both inbox and spam folder.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i> Save Email Settings
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

