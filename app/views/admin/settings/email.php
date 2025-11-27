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

    <form method="POST" action="<?= BASE_URL ?>/admin/settings/email" id="email-settings-form" class="space-y-6">
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
                           value="" 
                           placeholder="Enter password to update (leave blank to keep current)">
                    <p class="text-xs text-gray-500 mt-1">
                        <?php if (!empty(settingVal($settings, 'smtp_pass', ''))): ?>
                            <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Password is set</span> - Enter new password to update
                        <?php else: ?>
                            <span class="text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>Password not set</span> - Enter your email account password
                        <?php endif; ?>
                    </p>
                    <?php if (!empty(settingVal($settings, 'smtp_pass', ''))): ?>
                        <p class="text-xs text-yellow-600 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            If you're experiencing authentication errors, try re-entering the password to ensure it's correct.
                        </p>
                    <?php endif; ?>
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

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                <div class="text-sm text-yellow-800">
                    <p class="font-semibold mb-1">Important Notes</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>If you see "SMTP Authentication failed: 535 Incorrect authentication data", verify your password is correct and re-enter it.</li>
                        <li>Make sure there are no extra spaces before or after the password.</li>
                        <li>Some email providers require app-specific passwords instead of your regular password.</li>
                        <li>After saving, place a test order to verify emails are being sent correctly.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center pt-6 border-t border-gray-200">
            <div class="flex-1">
                <h3 class="text-lg font-semibold mb-2">Test Email Configuration</h3>
                <p class="text-sm text-gray-600 mb-4">Send a test email to verify your SMTP settings are working correctly.</p>
                <form method="POST" action="<?= BASE_URL ?>/admin/settings/email/test" class="flex gap-3">
                    <?= $csrfField ?? '' ?>
                    <input type="email" 
                           name="test_email" 
                           class="form-input flex-1" 
                           placeholder="Enter email address to test"
                           required>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-paper-plane mr-2"></i> Send Test Email
                    </button>
                </form>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" form="email-settings-form" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i> Save Email Settings
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>


