<?php
use App\Core\Helper;
$content = ob_start();
?>

<section class="py-8 md:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Progress Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-center space-x-4 mb-4">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-brand text-white font-bold">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <span class="ml-2 text-sm font-medium text-gray-700 hidden sm:inline">Cart</span>
                </div>
                <div class="w-12 h-0.5 bg-brand"></div>
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-brand text-white font-bold">
                        <span>2</span>
                    </div>
                    <span class="ml-2 text-sm font-medium text-gray-700 hidden sm:inline">Checkout</span>
                </div>
                <div class="w-12 h-0.5 bg-gray-300"></div>
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-bold">
                        <span>3</span>
                    </div>
                    <span class="ml-2 text-sm font-medium text-gray-500 hidden sm:inline">Payment</span>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Checkout</h1>
            <p class="text-gray-600">Complete your order details below</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 flex items-start">
                <i class="fas fa-exclamation-circle mt-0.5 mr-3"></i>
                <div>
                    <p class="font-semibold">Error</p>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= BASE_URL ?>/checkout" id="checkout-form" class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            <?= $csrfField ?? $this->csrf->getTokenField() ?>
            
            <!-- Checkout Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Type -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-list-ul mr-2 text-brand"></i>
                        Order Type
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="border-2 rounded-xl p-5 cursor-pointer transition-all duration-200 hover:shadow-md <?= (isset($formData['order_type']) && $formData['order_type'] === 'pickup') || !isset($formData['order_type']) ? 'border-brand bg-brand/5 shadow-sm' : 'border-gray-200 hover:border-brand/50' ?>">
                            <input type="radio" name="order_type" value="pickup" class="hidden" 
                                   <?= (isset($formData['order_type']) && $formData['order_type'] === 'pickup') || !isset($formData['order_type']) ? 'checked' : '' ?>>
                            <div class="text-center">
                                <i class="fas fa-store text-4xl text-brand mb-3"></i>
                                <p class="font-semibold text-lg mb-1">Pickup</p>
                                <p class="text-sm text-gray-600">Order ready for pickup at our location</p>
                            </div>
                        </label>
                        <label class="border-2 rounded-xl p-5 cursor-pointer transition-all duration-200 hover:shadow-md <?= isset($formData['order_type']) && $formData['order_type'] === 'delivery' ? 'border-brand bg-brand/5 shadow-sm' : 'border-gray-200 hover:border-brand/50' ?>">
                            <input type="radio" name="order_type" value="delivery" class="hidden"
                                   <?= isset($formData['order_type']) && $formData['order_type'] === 'delivery' ? 'checked' : '' ?>>
                            <div class="text-center">
                                <i class="fas fa-truck text-4xl text-brand mb-3"></i>
                                <p class="font-semibold text-lg mb-1">Delivery</p>
                                <p class="text-sm text-gray-600">DoorDash delivery to your address</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-user mr-2 text-brand"></i>
                        Contact Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="email" name="email" required 
                                       value="<?= htmlspecialchars($formData['email'] ?? ($isGuest ? '' : $_SESSION['user_email'] ?? '')) ?>"
                                       class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="tel" name="phone" required 
                                       value="<?= htmlspecialchars($formData['phone'] ?? '') ?>"
                                       placeholder="(204) 555-1234"
                                       class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Address -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-brand"></i>
                        Billing Address
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="billing_first_name" required 
                                   value="<?= htmlspecialchars($formData['billing_first_name'] ?? '') ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="billing_last_name" required 
                                   value="<?= htmlspecialchars($formData['billing_last_name'] ?? '') ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Address Line 1 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="billing_address_line1" required 
                                   value="<?= htmlspecialchars($formData['billing_address_line1'] ?? '') ?>"
                                   placeholder="Street address"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Address Line 2 <span class="text-gray-500 text-xs font-normal">(Optional)</span>
                            </label>
                            <input type="text" name="billing_address_line2" 
                                   value="<?= htmlspecialchars($formData['billing_address_line2'] ?? '') ?>"
                                   placeholder="Apartment, suite, etc."
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                City <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="billing_city" required 
                                   value="<?= htmlspecialchars($formData['billing_city'] ?? '') ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Province <span class="text-red-500">*</span>
                            </label>
                            <select name="billing_province" required id="billing_province"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition bg-white">
                                <option value="">Select Province</option>
                                <option value="AB" <?= (isset($formData['billing_province']) && $formData['billing_province'] === 'AB') ? 'selected' : '' ?>>Alberta</option>
                                <option value="BC" <?= (isset($formData['billing_province']) && $formData['billing_province'] === 'BC') ? 'selected' : '' ?>>British Columbia</option>
                                <option value="MB" <?= (isset($formData['billing_province']) && $formData['billing_province'] === 'MB') ? 'selected' : '' ?>>Manitoba</option>
                                <option value="NB" <?= (isset($formData['billing_province']) && $formData['billing_province'] === 'NB') ? 'selected' : '' ?>>New Brunswick</option>
                                <option value="NL" <?= (isset($formData['billing_province']) && $formData['billing_province'] === 'NL') ? 'selected' : '' ?>>Newfoundland and Labrador</option>
                                <option value="NS" <?= (isset($formData['billing_province']) && $formData['billing_province'] === 'NS') ? 'selected' : '' ?>>Nova Scotia</option>
                                <option value="ON" <?= (isset($formData['billing_province']) && $formData['billing_province'] === 'ON') ? 'selected' : '' ?>>Ontario</option>
                                <option value="PE" <?= (isset($formData['billing_province']) && $formData['billing_province'] === 'PE') ? 'selected' : '' ?>>Prince Edward Island</option>
                                <option value="QC" <?= (isset($formData['billing_province']) && $formData['billing_province'] === 'QC') ? 'selected' : '' ?>>Quebec</option>
                                <option value="SK" <?= (isset($formData['billing_province']) && $formData['billing_province'] === 'SK') ? 'selected' : '' ?>>Saskatchewan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Postal Code <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="billing_postal_code" required 
                                   value="<?= htmlspecialchars($formData['billing_postal_code'] ?? '') ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition uppercase"
                                   pattern="[A-Za-z][0-9][A-Za-z] [0-9][A-Za-z][0-9]" 
                                   placeholder="A1A 1A1"
                                   maxlength="7">
                        </div>
                    </div>
                </div>

                <!-- Shipping Address (for delivery) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" id="shipping-section" style="display: none;">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-truck mr-2 text-brand"></i>
                        Delivery Address
                    </h2>
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" id="same-as-billing" class="w-5 h-5 text-brand border-gray-300 rounded focus:ring-brand">
                            <span class="ml-3 text-gray-700 font-medium">Same as billing address</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="shipping_first_name" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="shipping_last_name" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Address Line 1 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="shipping_address_line1" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Address Line 2 <span class="text-gray-500 text-xs font-normal">(Optional)</span>
                            </label>
                            <input type="text" name="shipping_address_line2" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                City <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="shipping_city" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Province <span class="text-red-500">*</span>
                            </label>
                            <select name="shipping_province" id="shipping_province"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition bg-white">
                                <option value="">Select Province</option>
                                <option value="AB">Alberta</option>
                                <option value="BC">British Columbia</option>
                                <option value="MB">Manitoba</option>
                                <option value="NB">New Brunswick</option>
                                <option value="NL">Newfoundland and Labrador</option>
                                <option value="NS">Nova Scotia</option>
                                <option value="ON">Ontario</option>
                                <option value="PE">Prince Edward Island</option>
                                <option value="QC">Quebec</option>
                                <option value="SK">Saskatchewan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Postal Code <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="shipping_postal_code" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition uppercase"
                                   pattern="[A-Za-z][0-9][A-Za-z] [0-9][A-Za-z][0-9]" 
                                   placeholder="A1A 1A1"
                                   maxlength="7">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                Delivery Instructions <span class="text-gray-500 text-xs font-normal">(Optional)</span>
                            </label>
                            <textarea name="delivery_instructions" rows="3" 
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition resize-none"
                                      placeholder="Any special delivery instructions (e.g., gate code, apartment number, etc.)"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pickup Time (for pickup) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" id="pickup-section" style="display: none;">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-clock mr-2 text-brand"></i>
                        Pickup Time
                    </h2>
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            Preferred Pickup Time
                        </label>
                        <input type="datetime-local" name="pickup_time" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-brand transition">
                        <p class="text-sm text-gray-600 mt-2 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            We'll prepare your order for this time. Please allow at least 30 minutes.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-4">
                    <h2 class="text-xl font-bold mb-6 flex items-center">
                        <i class="fas fa-receipt mr-2 text-brand"></i>
                        Order Summary
                    </h2>
                    
                    <div class="space-y-3 mb-6 max-h-80 overflow-y-auto pr-2">
                        <?php foreach ($items as $item): ?>
                            <div class="flex items-start justify-between pb-3 border-b border-gray-200">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 text-sm"><?= htmlspecialchars($item['name']) ?></p>
                                    <p class="text-xs text-gray-500 mt-1">Quantity: <?= $item['quantity'] ?></p>
                                </div>
                                <span class="font-semibold text-gray-900 ml-4"><?= Helper::formatCurrency($item['price'] * $item['quantity']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="space-y-3 mb-6 border-t border-gray-200 pt-4">
                        <div class="flex justify-between text-gray-700">
                            <span>Subtotal</span>
                            <span class="font-semibold"><?= Helper::formatCurrency($subtotal) ?></span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Tax (GST/HST)</span>
                            <span id="tax-amount" class="font-semibold">Calculating...</span>
                        </div>
                        <div class="flex justify-between text-gray-700" id="delivery-fee-row" style="display: none;">
                            <span>Delivery Fee</span>
                            <span id="delivery-fee" class="font-semibold">$0.00</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex justify-between">
                            <span class="text-lg font-bold text-gray-900">Total</span>
                            <span id="order-total" class="text-lg font-bold text-brand"><?= Helper::formatCurrency($subtotal) ?></span>
                        </div>
                    </div>
                    
                    <button type="submit" id="checkout-submit-btn"
                            class="w-full bg-brand text-white py-4 rounded-lg font-bold text-lg hover:bg-brand-dark transition-all duration-200 transform hover:scale-[1.02] shadow-lg mb-4 flex items-center justify-center">
                        <i class="fas fa-lock mr-2"></i> 
                        <span>Proceed to Payment</span>
                    </button>
                    
                    <div class="text-center space-y-2">
                        <p class="text-xs text-gray-500 flex items-center justify-center">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Secure payment powered by Square
                        </p>
                        <p class="text-xs text-gray-400">
                            Your payment information is encrypted and secure
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderTypeRadios = document.querySelectorAll('input[name="order_type"]');
    const shippingSection = document.getElementById('shipping-section');
    const pickupSection = document.getElementById('pickup-section');
    const sameAsBilling = document.getElementById('same-as-billing');
    const checkoutForm = document.getElementById('checkout-form');
    const checkoutSubmitBtn = document.getElementById('checkout-submit-btn');
    
    // Order type change handler
    orderTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'delivery') {
                shippingSection.style.display = 'block';
                pickupSection.style.display = 'none';
                document.getElementById('delivery-fee-row').style.display = 'flex';
                // Set delivery fee (you can make this dynamic)
                document.getElementById('delivery-fee').textContent = '$5.00';
            } else {
                shippingSection.style.display = 'none';
                pickupSection.style.display = 'block';
                document.getElementById('delivery-fee-row').style.display = 'none';
            }
            calculateTotal();
        });
    });
    
    // Trigger on load
    orderTypeRadios.forEach(radio => {
        if (radio.checked) radio.dispatchEvent(new Event('change'));
    });
    
    // Same as billing
    sameAsBilling?.addEventListener('change', function() {
        if (this.checked) {
            const billingFields = ['first_name', 'last_name', 'address_line1', 'address_line2', 'city', 'province', 'postal_code'];
            billingFields.forEach(field => {
                const billingField = document.querySelector(`input[name="billing_${field}"], select[name="billing_${field}"]`);
                const shippingField = document.querySelector(`input[name="shipping_${field}"], select[name="shipping_${field}"]`);
                if (billingField && shippingField && billingField.value) {
                    shippingField.value = billingField.value;
                }
            });
        }
    });
    
    // Calculate tax on province change
    const billingProvince = document.getElementById('billing_province');
    if (billingProvince) {
        billingProvince.addEventListener('change', function() {
            calculateTax();
        });
    }
    
    // Form submission
    checkoutForm?.addEventListener('submit', function(e) {
        checkoutSubmitBtn.disabled = true;
        checkoutSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
    });
    
    function calculateTax() {
        const province = billingProvince?.value;
        const subtotal = <?= $subtotal ?>;
        
        if (province) {
            const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
            fetch('<?= BASE_URL ?>/checkout/calculate-tax', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csrf_token=${csrfToken}&subtotal=${subtotal}&province=${province}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const taxAmount = parseFloat(data.tax.total_tax);
                    document.getElementById('tax-amount').textContent = formatCurrency(taxAmount);
                    calculateTotal();
                } else {
                    document.getElementById('tax-amount').textContent = 'Calculating...';
                }
            })
            .catch(error => {
                console.error('Tax calculation error:', error);
                document.getElementById('tax-amount').textContent = 'Error calculating';
            });
        } else {
            document.getElementById('tax-amount').textContent = 'Select province';
        }
    }
    
    function calculateTotal() {
        const subtotal = <?= $subtotal ?>;
        const taxText = document.getElementById('tax-amount').textContent;
        let taxAmount = 0;
        
        // Extract tax amount from text
        if (taxText && taxText !== 'Calculating...' && taxText !== 'Select province' && taxText !== 'Error calculating') {
            const taxMatch = taxText.match(/[\d.]+/);
            if (taxMatch) {
                taxAmount = parseFloat(taxMatch[0]);
            }
        }
        
        let deliveryFee = 0;
        const deliveryFeeRow = document.getElementById('delivery-fee-row');
        if (deliveryFeeRow && deliveryFeeRow.style.display !== 'none') {
            const deliveryFeeText = document.getElementById('delivery-fee').textContent;
            const feeMatch = deliveryFeeText.match(/[\d.]+/);
            if (feeMatch) {
                deliveryFee = parseFloat(feeMatch[0]);
            }
        }
        
        const total = subtotal + taxAmount + deliveryFee;
        document.getElementById('order-total').textContent = formatCurrency(total);
    }
    
    // Initial tax calculation if province is selected
    if (billingProvince?.value) {
        calculateTax();
    }
});
</script>

<?php
$content = ob_get_clean();
$page_title = 'Checkout';
require_once APP_PATH . '/views/layouts/main.php';
?>
