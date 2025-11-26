<?php
$content = ob_start();
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-brand/10 via-white to-brand-dark/10 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center mb-2">
                <img src="<?= BASE_URL ?>/public/images/logo.png" alt="The Pembina Pint and Restaurant" class="h-20 w-20 md:h-24 md:w-24">
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Forgot your password?
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter your email address and we'll send you a link to reset your password.
            </p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST" action="<?= BASE_URL ?>/forgot-password">
            <?= $csrfField ?? '' ?>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email address
                </label>
                <input id="email" name="email" type="email" required autocomplete="email" 
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-brand focus:ring-2 focus:ring-brand/20 transition-all duration-200" 
                       placeholder="Enter your email address">
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent text-base font-semibold rounded-lg text-white bg-brand hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <span>Send Reset Link</span>
                </button>
            </div>
            
            <div class="text-center">
                <a href="<?= BASE_URL ?>/login" class="text-sm font-medium text-brand hover:text-brand-dark">
                    <i class="fas fa-arrow-left mr-1"></i> Back to login
                </a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$page_title = 'Forgot Password';
require_once APP_PATH . '/views/layouts/main.php';
?>

