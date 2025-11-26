<?php
$content = ob_start();
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Sign in to your account</h2>
        </div>
        <form class="mt-8 space-y-6" method="POST" action="<?= BASE_URL ?>/login">
            <?= $csrfField ?? '' ?>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <i class="fas fa-check-circle mr-2"></i>Password reset successful! You can now login with your new password.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <i class="fas fa-check-circle mr-2"></i>Registration successful! Please login.
                </div>
            <?php endif; ?>
            
            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email address</label>
                    <input id="email" name="email" type="email" required autocomplete="email" 
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-brand focus:ring-2 focus:ring-brand/20 transition-all duration-200" 
                           placeholder="Enter your email">
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" 
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-brand focus:ring-2 focus:ring-brand/20 transition-all duration-200" 
                           placeholder="Enter your password">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-brand focus:ring-brand border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">Remember me</label>
                </div>

                <div class="text-sm">
                    <a href="<?= BASE_URL ?>/forgot-password" class="font-medium text-brand hover:text-brand-dark">Forgot your password?</a>
                </div>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent text-base font-semibold rounded-lg text-white bg-brand hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                    <span class="button-text">Sign in</span>
                    <span class="button-loader hidden ml-2">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </div>
            
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account? <a href="<?= BASE_URL ?>/register" class="font-medium text-brand hover:text-brand-dark">Register here</a>
                </p>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$page_title = 'Login';
require_once APP_PATH . '/views/layouts/main.php';
?>

