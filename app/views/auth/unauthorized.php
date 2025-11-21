<?php
$content = ob_start();
?>

<section class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="bg-white rounded-2xl shadow-lg p-10 max-w-xl text-center">
        <div class="w-16 h-16 mx-auto mb-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-2xl">
            <i class="fas fa-lock"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Access Denied</h1>
        <p class="text-gray-600 mb-6">You don't have permission to view this page. If you believe this is a mistake, please contact an administrator.</p>
        <a href="<?= BASE_URL ?>/" class="btn btn-primary inline-flex items-center">
            <i class="fas fa-home mr-2"></i>Go Home
        </a>
    </div>
</section>

<?php
$content = ob_get_clean();
$page_title = 'Unauthorized';
require_once APP_PATH . '/views/layouts/main.php';
?>

