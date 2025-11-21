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
                    <label for="first_name" class="sr-only">First name</label>
                    <input id="first_name" name="first_name" type="text" required class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand focus:border-brand sm:text-sm" placeholder="First name" value="<?= htmlspecialchars($data['first_name'] ?? '') ?>">
                </div>
                <div>
                    <label for="last_name" class="sr-only">Last name</label>
                    <input id="last_name" name="last_name" type="text" required class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand focus:border-brand sm:text-sm" placeholder="Last name" value="<?= htmlspecialchars($data['last_name'] ?? '') ?>">
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" required class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand focus:border-brand sm:text-sm" placeholder="Email address" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
                </div>
                <div>
                    <label for="phone" class="sr-only">Phone</label>
                    <input id="phone" name="phone" type="text" class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand focus:border-brand sm:text-sm" placeholder="Phone number" value="<?= htmlspecialchars($data['phone'] ?? '') ?>">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand focus:border-brand sm:text-sm" placeholder="Password">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-brand hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand">
                    Sign up
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

