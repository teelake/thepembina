<?php
$content = ob_start();
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Create your account</h2>
        </div>
        <form class="mt-8 space-y-6" method="POST" action="<?= BASE_URL ?>/register">
            <?= $csrfField ?? '' ?>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-2">First name</label>
                    <input id="first_name" name="first_name" type="text" required autocomplete="given-name"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-brand focus:ring-2 focus:ring-brand/20 transition-all duration-200" 
                           placeholder="Enter your first name" value="<?= htmlspecialchars($data['first_name'] ?? '') ?>">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-2">Last name</label>
                    <input id="last_name" name="last_name" type="text" required autocomplete="family-name"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-brand focus:ring-2 focus:ring-brand/20 transition-all duration-200" 
                           placeholder="Enter your last name" value="<?= htmlspecialchars($data['last_name'] ?? '') ?>">
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email address</label>
                    <input id="email" name="email" type="email" required autocomplete="email"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-brand focus:ring-2 focus:ring-brand/20 transition-all duration-200" 
                           placeholder="Enter your email" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone number</label>
                    <input id="phone" name="phone" type="tel" autocomplete="tel"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-brand focus:ring-2 focus:ring-brand/20 transition-all duration-200" 
                           placeholder="Enter your phone number" value="<?= htmlspecialchars($data['phone'] ?? '') ?>">
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-brand focus:ring-2 focus:ring-brand/20 transition-all duration-200" 
                           placeholder="Create a password">
                </div>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent text-base font-semibold rounded-lg text-white bg-brand hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                    <span class="button-text">Sign up</span>
                    <span class="button-loader hidden ml-2">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Already have an account? <a href="<?= BASE_URL ?>/login" class="font-medium text-brand hover:text-brand-dark">Sign in here</a>
                </p>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$page_title = 'Register';
require_once APP_PATH . '/views/layouts/main.php';
?>

