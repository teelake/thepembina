<?php
// Payment form with Square integration
// This would include Square's payment form JavaScript
$content = ob_start();
?>

<div class="max-w-2xl mx-auto py-12">
    <h1 class="text-3xl font-bold mb-8">Complete Payment</h1>
    
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Order Summary</h2>
        <div class="space-y-2">
            <div class="flex justify-between">
                <span>Order #:</span>
                <span class="font-semibold"><?= htmlspecialchars($order['order_number']) ?></span>
            </div>
            <div class="flex justify-between">
                <span>Total:</span>
                <span class="font-bold text-brand text-xl"><?= Helper::formatCurrency($order['total']) ?></span>
            </div>
        </div>
    </div>
    
    <!-- Square Payment Form -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">Payment Information</h2>
        
        <form id="payment-form" method="POST" action="<?= BASE_URL ?>/payment/process">
            <?= $this->csrf->getTokenField() ?>
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <input type="hidden" name="gateway" value="square">
            <input type="hidden" name="source_id" id="source-id">
            
            <!-- Square Payment Form Container -->
            <div id="square-payment-form"></div>
            
            <div id="payment-status" class="mt-4"></div>
            
            <button type="submit" id="pay-button" class="w-full bg-brand text-white py-4 rounded-lg font-bold text-lg hover:bg-brand-dark transition mt-6" disabled>
                <i class="fas fa-lock mr-2"></i> Pay <?= Helper::formatCurrency($order['total']) ?>
            </button>
        </form>
    </div>
</div>

<!-- Square Payment Form SDK -->
<script src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const applicationId = '<?= Helper::getSetting("payment_square_app_id", "") ?>';
    const locationId = '<?= Helper::getSetting("payment_square_location_id", "") ?>';
    const sandbox = <?= Helper::getSetting("payment_square_sandbox", "1") === "1" ? "true" : "false" ?>;
    
    if (!applicationId || !locationId) {
        document.getElementById('payment-status').innerHTML = 
            '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Payment gateway not configured. Please contact administrator.</div>';
        return;
    }
    
    // Initialize Square Payment Form
    const payments = Square.payments(applicationId, locationId);
    const card = await payments.card();
    await card.attach('#square-payment-form');
    
    // Handle form submission
    document.getElementById('payment-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const payButton = document.getElementById('pay-button');
        payButton.disabled = true;
        payButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
        
        try {
            const result = await card.tokenize();
            if (result.status === 'OK') {
                document.getElementById('source-id').value = result.token;
                this.submit();
            } else {
                document.getElementById('payment-status').innerHTML = 
                    '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Payment failed: ' + result.errors[0].detail + '</div>';
                payButton.disabled = false;
                payButton.innerHTML = '<i class="fas fa-lock mr-2"></i> Pay <?= Helper::formatCurrency($order['total']) ?>';
            }
        } catch (error) {
            document.getElementById('payment-status').innerHTML = 
                '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error: ' + error.message + '</div>';
            payButton.disabled = false;
            payButton.innerHTML = '<i class="fas fa-lock mr-2"></i> Pay <?= Helper::formatCurrency($order['total']) ?>';
        }
    });
    
    // Enable pay button when card is ready
    card.addEventListener('ready', function() {
        document.getElementById('pay-button').disabled = false;
    });
});
</script>

<?php
$content = ob_get_clean();
$page_title = 'Payment';
require_once APP_PATH . '/views/layouts/main.php';
?>

