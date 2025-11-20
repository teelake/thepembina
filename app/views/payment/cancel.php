<?php
$content = ob_start();
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
                    <i class="fas fa-times text-4xl text-yellow-600"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Cancelled</h1>
                <p class="text-gray-600">Your payment was cancelled</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <p class="text-gray-700 mb-4">
                    No charges were made to your account. Your order has been saved and you can complete the payment later.
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?= BASE_URL ?>/checkout" 
                   class="bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-dark transition">
                    Try Again
                </a>
                <a href="<?= BASE_URL ?>/cart" 
                   class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                    Back to Cart
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$page_title = 'Payment Cancelled';
require_once APP_PATH . '/views/layouts/main.php';
?>

