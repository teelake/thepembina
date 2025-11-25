<?php
$content = ob_start();
?>

<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">My Addresses</h1>
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-blue-800">
            <i class="fas fa-info-circle mr-2"></i>
            Addresses are saved during checkout. You can manage them here in the future.
        </p>
    </div>
    
    <div class="bg-white rounded-xl shadow-md p-12 text-center">
        <i class="fas fa-map-marker-alt text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No saved addresses</h3>
        <p class="text-gray-600 mb-6">Addresses will be saved automatically when you place an order</p>
        <a href="<?= BASE_URL ?>/menu" class="inline-block bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-dark transition">
            Start Shopping
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
$page_title = 'My Addresses';
require_once APP_PATH . '/views/layouts/main.php';
?>


