<?php
use App\Core\Helper;
$content = ob_start();
?>

<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="mb-6">
        <h1 class="text-3xl font-bold mb-2">Complete Payment</h1>
        <p class="text-gray-600">Review your order and complete payment securely</p>
    </div>
    
    <?php if (!empty($_GET['error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
            <p class="font-semibold">Payment Error</p>
            <p><?= htmlspecialchars($_GET['error']) ?></p>
            <p class="text-sm mt-2">Please check your card details and try again, or contact support if the problem persists.</p>
        </div>
    <?php endif; ?>
    
    <?php if (!isset($order) || empty($order)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
            <p class="font-semibold">Error</p>
            <p>Order information could not be loaded. Please try again or contact support.</p>
            <a href="<?= BASE_URL ?>/checkout" class="text-red-600 underline mt-2 inline-block">Return to Checkout</a>
        </div>
    <?php else: ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Summary (Left Side) -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md p-6 sticky top-4">
                <h2 class="text-xl font-bold mb-4 pb-3 border-b border-gray-200">Order Summary</h2>
                <div class="space-y-3 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Order #:</span>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($order['order_number'] ?? 'N/A') ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Order Type:</span>
                        <span class="font-semibold text-gray-900"><?= ucfirst($order['order_type'] ?? 'N/A') ?></span>
                    </div>
                </div>
                
                <?php if (isset($order['items']) && !empty($order['items'])): ?>
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-900 mb-3">Items:</h3>
                    <div class="space-y-3">
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="flex justify-between text-sm">
                            <div class="flex-1">
                                <span class="text-gray-900"><?= htmlspecialchars($item['product_name'] ?? '') ?></span>
                                <span class="text-gray-500 ml-1">x <?= $item['quantity'] ?? 1 ?></span>
                            </div>
                            <span class="font-semibold text-gray-900"><?= Helper::formatCurrency(($item['price'] ?? 0) * ($item['quantity'] ?? 1)) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="space-y-2 pt-4 border-t border-gray-200">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-semibold text-gray-900"><?= Helper::formatCurrency($order['subtotal'] ?? 0) ?></span>
                    </div>
                    <?php if (isset($order['tax_amount']) && $order['tax_amount'] > 0): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax:</span>
                        <span class="font-semibold text-gray-900"><?= Helper::formatCurrency($order['tax_amount']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($order['shipping_amount']) && $order['shipping_amount'] > 0): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Delivery:</span>
                        <span class="font-semibold text-gray-900"><?= Helper::formatCurrency($order['shipping_amount']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between pt-3 border-t border-gray-200">
                        <span class="text-lg font-bold text-gray-900">Total:</span>
                        <span class="text-lg font-bold text-brand"><?= Helper::formatCurrency($order['total'] ?? 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payment Form (Right Side) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-md p-6 md:p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold mb-2">Payment Information</h2>
                    <p class="text-gray-600">Enter your card details to complete the payment</p>
                </div>
                
                <form id="payment-form" method="POST" action="<?= BASE_URL ?>/payment/process">
                    <?= $csrfField ?? '' ?>
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="gateway" value="square">
                    <input type="hidden" name="source_id" id="source-id">
                    
                    <!-- Square Payment Form Container -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-3 text-gray-700">Card Details</label>
                        <div id="square-payment-form" class="border border-gray-300 rounded-lg p-4 bg-gray-50 min-h-[200px] relative">
                            <div id="payment-form-loading" class="absolute inset-0 flex items-center justify-center bg-gray-50 bg-opacity-90">
                                <div class="text-center">
                                    <i class="fas fa-spinner fa-spin text-2xl text-brand mb-2"></i>
                                    <p class="text-sm text-gray-600">Loading payment form...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="payment-status" class="mb-4"></div>
                    
                    <button type="submit" id="pay-button" 
                            class="w-full bg-brand text-white py-4 rounded-lg font-bold text-lg hover:bg-brand-dark transition shadow-md hover:shadow-lg transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none" 
                            disabled>
                        <i class="fas fa-lock mr-2"></i> 
                        <span id="pay-button-text">Pay <?= Helper::formatCurrency($order['total'] ?? 0) ?></span>
                    </button>
                    
                    <div class="mt-4 text-center text-sm text-gray-600">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Your payment is secure and encrypted
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Square Payment Form SDK -->
<?php
$squareSandbox = Helper::getSetting("payment_square_sandbox", "1") === "1";
$squareSdkUrl = $squareSandbox 
    ? "https://sandbox.web.squarecdn.com/v1/square.js"
    : "https://web.squarecdn.com/v1/square.js";
?>
<script src="<?= $squareSdkUrl ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const applicationId = '<?= Helper::getSetting("payment_square_app_id", "") ?>';
    const locationId = '<?= Helper::getSetting("payment_square_location_id", "") ?>';
    const sandbox = <?= $squareSandbox ? "true" : "false" ?>;
    const payButton = document.getElementById('pay-button');
    const paymentStatus = document.getElementById('payment-status');
    let cardInstance = null;
    
    if (!applicationId || !locationId) {
        paymentStatus.innerHTML = 
            '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Payment gateway not configured. Please contact administrator.</div>';
        return;
    }
    
    // Initialize Square Payment Form
    (async function() {
        try {
            // Wait for Square SDK to load
            if (typeof Square === 'undefined') {
                paymentStatus.innerHTML = 
                    '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Failed to load Square payment SDK. Please refresh the page.</div>';
                return;
            }
            
            const payments = Square.payments(applicationId, locationId);
            cardInstance = await payments.card();
            await cardInstance.attach('#square-payment-form');
            
            // Hide loading indicator
            const loadingIndicator = document.getElementById('payment-form-loading');
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
            
            // Enable pay button when card form is ready
            cardInstance.addEventListener('ready', function() {
                console.log('Square card form ready');
                enablePayButton();
            });
            
            // Handle card validation events
            cardInstance.addEventListener('error', function(event) {
                console.log('Square card error:', event);
                // Don't disable button on validation errors, let user try to submit
            });
            
            // Handle card validation state changes
            cardInstance.addEventListener('postalCode', function(event) {
                console.log('Square postal code event:', event);
            });
            
            // Fallback: Enable button after 2 seconds if ready event doesn't fire
            setTimeout(function() {
                if (payButton.disabled && cardInstance) {
                    console.log('Fallback: Enabling pay button');
                    enablePayButton();
                }
            }, 2000);
            
            // Handle form submission
            document.getElementById('payment-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                if (!cardInstance) {
                    paymentStatus.innerHTML = 
                        '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Payment form not initialized. Please refresh the page.</div>';
                    return;
                }
                
                payButton.disabled = true;
                payButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
                paymentStatus.innerHTML = '';
                
                try {
                    const result = await cardInstance.tokenize();
                    if (result.status === 'OK') {
                        document.getElementById('source-id').value = result.token;
                        this.submit();
                    } else {
                        let errorMessage = 'Payment processing failed. Please try again.';
                        if (result.errors && result.errors.length > 0) {
                            const firstError = result.errors[0];
                            errorMessage = firstError.detail || firstError.message || firstError.code || errorMessage;
                        } else if (result.message) {
                            errorMessage = result.message;
                        }
                        paymentStatus.innerHTML = 
                            '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Payment failed: ' + errorMessage + '</div>';
                        enablePayButton();
                    }
                } catch (error) {
                    console.error('Payment error:', error);
                    paymentStatus.innerHTML = 
                        '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error: ' + (error.message || 'Payment processing failed') + '</div>';
                    enablePayButton();
                }
            });
        } catch (error) {
            console.error('Square initialization error:', error);
            const loadingIndicator = document.getElementById('payment-form-loading');
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
            paymentStatus.innerHTML = 
                '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Failed to initialize payment form: ' + (error.message || 'Unknown error') + '. Please refresh the page or contact support.</div>';
        }
    })();
    
    function enablePayButton() {
        payButton.disabled = false;
        payButton.classList.remove('opacity-50', 'cursor-not-allowed');
        payButton.classList.add('hover:bg-brand-dark');
    }
});
</script>

<?php
$content = ob_get_clean();
$page_title = 'Payment';
require_once APP_PATH . '/views/layouts/main.php';
?>

