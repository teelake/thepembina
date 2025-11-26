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
    <h1 class="text-3xl font-bold mb-6">WhatsApp Chatbot Settings</h1>

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
            <i class="fab fa-whatsapp text-blue-600 mt-1 mr-3 text-2xl"></i>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">WhatsApp Chatbot Configuration</p>
                <p>Configure your WhatsApp number to enable the floating chat button on your website. Customers can click the button to start a conversation with you on WhatsApp.</p>
            </div>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/settings/whatsapp" class="space-y-6">
        <?= $csrfField ?? '' ?>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">WhatsApp Configuration</h2>
            
            <div class="form-group mb-4">
                <label class="form-label">
                    <input type="checkbox" name="whatsapp_enabled" value="1" 
                           <?= settingVal($settings, 'whatsapp_enabled', '1') === '1' ? 'checked' : '' ?>
                           class="mr-2">
                    Enable WhatsApp Chatbot
                </label>
                <p class="text-xs text-gray-500 mt-1">Show/hide the WhatsApp chat button on the website</p>
            </div>

            <div class="form-group">
                <label class="form-label">WhatsApp Phone Number</label>
                <input type="text" name="whatsapp_number" class="form-input" 
                       value="<?= htmlspecialchars(settingVal($settings, 'whatsapp_number', '')) ?>"
                       placeholder="1234567890 or +1234567890">
                <p class="text-xs text-gray-500 mt-1">
                    Enter your WhatsApp number with country code (e.g., 12045551234 for US or 1234567890 for Canada).
                    <br>Do not include spaces, dashes, or parentheses. The system will automatically format it.
                </p>
            </div>

            <div class="form-group">
                <label class="form-label">Default Message</label>
                <textarea name="whatsapp_message" class="form-textarea" rows="3"
                          placeholder="Hello! I need help with my order."><?= htmlspecialchars(settingVal($settings, 'whatsapp_message', 'Hello! I need help with my order.')) ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Pre-filled message that appears when customers click the WhatsApp button</p>
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                <div class="text-sm text-yellow-800">
                    <p class="font-semibold mb-1">Important Notes</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Make sure your WhatsApp number is active and can receive messages</li>
                        <li>The chat button will appear in the bottom-right corner of your website</li>
                        <li>If the number is invalid, customers won't be able to contact you</li>
                        <li>Test the button after saving to ensure it works correctly</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary">
                <i class="fab fa-whatsapp mr-2"></i> Save WhatsApp Settings
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

