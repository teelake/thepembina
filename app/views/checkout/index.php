<?php
use App\Core\Helper;
$content = ob_start();
?>

<section class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-8 text-gray-900">Checkout</h1>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= BASE_URL ?>/checkout" id="checkout-form" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <?= $this->csrf->getTokenField() ?>
            
            <!-- Checkout Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Type -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-4">Order Type</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="border-2 rounded-lg p-4 cursor-pointer hover:border-brand transition <?= (isset($formData['order_type']) && $formData['order_type'] === 'pickup') || !isset($formData['order_type']) ? 'border-brand bg-brand/5' : 'border-gray-200' ?>">
                            <input type="radio" name="order_type" value="pickup" class="hidden" 
                                   <?= (isset($formData['order_type']) && $formData['order_type'] === 'pickup') || !isset($formData['order_type']) ? 'checked' : '' ?>>
                            <div class="text-center">
                                <i class="fas fa-store text-3xl text-brand mb-2"></i>
                                <p class="font-semibold">Pickup</p>
                                <p class="text-sm text-gray-600">Order ready for pickup</p>
                            </div>
                        </label>
                        <label class="border-2 rounded-lg p-4 cursor-pointer hover:border-brand transition <?= isset($formData['order_type']) && $formData['order_type'] === 'delivery' ? 'border-brand bg-brand/5' : 'border-gray-200' ?>">
                            <input type="radio" name="order_type" value="delivery" class="hidden"
                                   <?= isset($formData['order_type']) && $formData['order_type'] === 'delivery' ? 'checked' : '' ?>>
                            <div class="text-center">
                                <i class="fas fa-truck text-3xl text-brand mb-2"></i>
                                <p class="font-semibold">Delivery</p>
                                <p class="text-sm text-gray-600">DoorDash delivery</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-4">Contact Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Email *</label>
                            <input type="email" name="email" required 
                                   value="<?= htmlspecialchars($formData['email'] ?? ($isGuest ? '' : $_SESSION['user_email'] ?? '')) ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">Phone *</label>
                            <input type="tel" name="phone" required 
                                   value="<?= htmlspecialchars($formData['phone'] ?? '') ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
                        </div>
                    </div>
                </div>

                <!-- Billing Address -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-4">Billing Address</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">First Name *</label>
                            <input type="text" name="billing_first_name" required 
                                   value="<?= htmlspecialchars($formData['billing_first_name'] ?? '') ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">Last Name *</label>
                            <input type="text" name="billing_last_name" required 
                                   value="<?= htmlspecialchars($formData['billing_last_name'] ?? '') ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-2">Address Line 1 *</label>
                            <input type="text" name="billing_address_line1" required 
                                   value="<?= htmlspecialchars($formData['billing_address_line1'] ?? '') ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-2">Address Line 2</label>
                            <input type="text" name="billing_address_line2" 
                                   value="<?= htmlspecialchars($formData['billing_address_line2'] ?? '') ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">City *</label>
                            <input type="text" name="billing_city" required 
                                   value="<?= htmlspecialchars($formData['billing_city'] ?? '') ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">Province *</label>
                            <select name="billing_province" required id="billing_province"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
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
                            <label class="block text-sm font-semibold mb-2">Postal Code *</label>
                            <input type="text" name="billing_postal_code" required 
                                   value="<?= htmlspecialchars($formData['billing_postal_code'] ?? '') ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand"
                                   pattern="[A-Za-z][0-9][A-Za-z] [0-9][A-Za-z][0-9]" 
                                   placeholder="A1A 1A1">
                        </div>
                    </div>
                </div>

                <!-- Shipping Address (for delivery) -->
                <div class="bg-white rounded-xl shadow-md p-6" id="shipping-section" style="display: none;">
                    <h2 class="text-2xl font-bold mb-4">Delivery Address</h2>
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="same-as-billing" class="mr-2">
                            <span>Same as billing address</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">First Name *</label>
                            <input type="text" name="shipping_first_name" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">Last Name *</label>
                            <input type="text" name="shipping_last_name" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-2">Address Line 1 *</label>
                            <input type="text" name="shipping_address_line1" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-2">Address Line 2</label>
                            <input type="text" name="shipping_address_line2" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">City *</label>
                            <input type="text" name="shipping_city" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">Province *</label>
                            <select name="shipping_province" id="shipping_province"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
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
                            <label class="block text-sm font-semibold mb-2">Postal Code *</label>
                            <input type="text" name="shipping_postal_code" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand"
                                   pattern="[A-Za-z][0-9][A-Za-z] [0-9][A-Za-z][0-9]" 
                                   placeholder="A1A 1A1">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-2">Delivery Instructions</label>
                            <textarea name="delivery_instructions" rows="3" 
                                      class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand"
                                      placeholder="Any special delivery instructions..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pickup Time (for pickup) -->
                <div class="bg-white rounded-xl shadow-md p-6" id="pickup-section" style="display: none;">
                    <h2 class="text-2xl font-bold mb-4">Pickup Time</h2>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Preferred Pickup Time</label>
                        <input type="datetime-local" name="pickup_time" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand">
                        <p class="text-sm text-gray-600 mt-2">We'll prepare your order for this time</p>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md p-6 sticky top-4">
                    <h2 class="text-2xl font-bold mb-6">Order Summary</h2>
                    
                    <div class="space-y-2 mb-6 max-h-64 overflow-y-auto">
                        <?php foreach ($items as $item): ?>
                            <div class="flex justify-between text-sm border-b pb-2">
                                <span class="flex-1"><?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?></span>
                                <span class="font-semibold"><?= Helper::formatCurrency($item['price'] * $item['quantity']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="space-y-3 mb-6 border-t pt-4">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span><?= Helper::formatCurrency($subtotal) ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Tax</span>
                            <span id="tax-amount">Calculated at payment</span>
                        </div>
                        <div class="flex justify-between text-gray-600" id="delivery-fee-row" style="display: none;">
                            <span>Delivery Fee</span>
                            <span id="delivery-fee">$0.00</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between text-xl font-bold">
                            <span>Total</span>
                            <span id="order-total" class="text-brand"><?= Helper::formatCurrency($subtotal) ?></span>
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-brand text-white py-4 rounded-lg font-bold text-lg hover:bg-brand-dark transition transform hover:scale-105 shadow-lg mb-4">
                        <i class="fas fa-lock mr-2"></i> Proceed to Payment
                    </button>
                    
                    <p class="text-xs text-gray-500 text-center">
                        <i class="fas fa-shield-alt mr-1"></i> Secure payment powered by Square
                    </p>
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
    
    orderTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'delivery') {
                shippingSection.style.display = 'block';
                pickupSection.style.display = 'none';
                document.getElementById('delivery-fee-row').style.display = 'flex';
            } else {
                shippingSection.style.display = 'none';
                pickupSection.style.display = 'block';
                document.getElementById('delivery-fee-row').style.display = 'none';
            }
        });
    });
    
    // Trigger on load
    orderTypeRadios.forEach(radio => {
        if (radio.checked) radio.dispatchEvent(new Event('change'));
    });
    
    // Same as billing
    sameAsBilling.addEventListener('change', function() {
        if (this.checked) {
            const billingFields = ['first_name', 'last_name', 'address_line1', 'address_line2', 'city', 'province', 'postal_code'];
            billingFields.forEach(field => {
                const billingValue = document.querySelector(`input[name="billing_${field}"], select[name="billing_${field}"]`)?.value;
                const shippingField = document.querySelector(`input[name="shipping_${field}"], select[name="shipping_${field}"]`);
                if (shippingField && billingValue) {
                    shippingField.value = billingValue;
                }
            });
        }
    });
    
    // Calculate tax on province change
    document.getElementById('billing_province')?.addEventListener('change', function() {
        calculateTax();
    });
    
    function calculateTax() {
        const province = document.getElementById('billing_province').value;
        const subtotal = <?= $subtotal ?>;
        
        if (province) {
            fetch('<?= BASE_URL ?>/checkout/calculate-tax', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csrf_token=${document.querySelector('input[name="csrf_token"]').value}&subtotal=${subtotal}&province=${province}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('tax-amount').textContent = 'CAD ' + data.tax.total_tax.toFixed(2);
                    const total = subtotal + parseFloat(data.tax.total_tax);
                    document.getElementById('order-total').textContent = 'CAD ' + total.toFixed(2);
                }
            });
        }
    }
});
</script>

<?php
$content = ob_get_clean();
$page_title = 'Checkout';
require_once APP_PATH . '/views/layouts/main.php';
?>

