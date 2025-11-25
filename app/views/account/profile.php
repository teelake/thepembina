<?php
$content = ob_start();
?>

<div class="max-w-2xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">My Profile</h1>
    
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
    
    <form method="POST" action="<?= BASE_URL ?>/account/profile" class="bg-white rounded-xl shadow-md p-6 space-y-6">
        <?= $csrfField ?? '' ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2 text-gray-700">
                    First Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="first_name" required
                       value="<?= htmlspecialchars($user['first_name'] ?? '') ?>"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2 text-gray-700">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="last_name" required
                       value="<?= htmlspecialchars($user['last_name'] ?? '') ?>"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-semibold mb-2 text-gray-700">
                Email Address <span class="text-red-500">*</span>
            </label>
            <input type="email" name="email" required
                   value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
        </div>
        
        <div>
            <label class="block text-sm font-semibold mb-2 text-gray-700">
                Phone Number
            </label>
            <input type="tel" name="phone"
                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
        </div>
        
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold mb-4">Change Password</h3>
            <p class="text-sm text-gray-600 mb-4">Leave blank to keep current password</p>
            <div>
                <label class="block text-sm font-semibold mb-2 text-gray-700">
                    New Password
                </label>
                <input type="password" name="password"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition"
                       placeholder="Enter new password">
            </div>
        </div>
        
        <div class="flex justify-end pt-4">
            <button type="submit" class="bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-dark transition">
                <i class="fas fa-save mr-2"></i> Update Profile
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$page_title = 'My Profile';
require_once APP_PATH . '/views/layouts/main.php';
?>

