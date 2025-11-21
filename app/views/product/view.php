<?php
use App\Core\Helper;
$content = ob_start();
?>

<!-- Product Detail -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="mb-6 text-sm text-gray-600">
            <a href="<?= BASE_URL ?>" class="hover:text-brand">Home</a> / 
            <a href="<?= BASE_URL ?>/menu" class="hover:text-brand">Menu</a> / 
            <?php if ($product['category_slug']): ?>
                <a href="<?= BASE_URL ?>/menu/<?= htmlspecialchars($product['category_slug']) ?>" class="hover:text-brand">
                    <?= htmlspecialchars($product['category_name']) ?>
                </a> / 
            <?php endif; ?>
            <span><?= htmlspecialchars($product['name']) ?></span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Product Image -->
            <div class="bg-gray-100 rounded-xl overflow-hidden">
                <?php if ($product['image']): ?>
                    <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($product['image']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="w-full h-96 object-cover">
                <?php else: ?>
                    <div class="w-full h-96 bg-gradient-to-br from-brand/20 to-brand-dark/20 flex items-center justify-center">
                        <i class="fas fa-utensils text-8xl text-brand"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div>
                <div class="mb-4">
                    <?php if ($product['is_featured']): ?>
                        <span class="inline-block bg-brand text-white px-3 py-1 rounded-full text-sm font-semibold mb-2">
                            ⭐ Featured Item
                        </span>
                    <?php endif; ?>
                    <?php if ($product['category_name']): ?>
                        <a href="<?= BASE_URL ?>/menu/<?= htmlspecialchars($product['category_slug']) ?>" 
                           class="inline-block text-brand hover:text-brand-dark text-sm font-semibold">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </a>
                    <?php endif; ?>
                </div>
                
                <h1 class="text-4xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($product['name']) ?></h1>
                
                <div class="mb-6">
                    <div class="flex items-baseline gap-4 mb-4">
                        <span class="text-4xl font-bold text-brand"><?= Helper::formatCurrency($product['price']) ?></span>
                        <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                            <span class="text-xl text-gray-500 line-through"><?= Helper::formatCurrency($product['compare_price']) ?></span>
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm font-semibold">
                                Save <?= Helper::formatCurrency($product['compare_price'] - $product['price']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($product['stock_status'] === 'in_stock'): ?>
                        <span class="inline-flex items-center text-green-600 font-semibold">
                            <i class="fas fa-check-circle mr-2"></i> In Stock
                        </span>
                    <?php elseif ($product['stock_status'] === 'out_of_stock'): ?>
                        <span class="inline-flex items-center text-red-600 font-semibold">
                            <i class="fas fa-times-circle mr-2"></i> Out of Stock
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($product['short_description']): ?>
                    <p class="text-lg text-gray-700 mb-6"><?= htmlspecialchars($product['short_description']) ?></p>
                <?php endif; ?>

                <!-- Product Options -->
                <?php if (!empty($options)): ?>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">Customize Your Order</h3>
                        <div id="product-options" class="space-y-4">
                            <?php foreach ($options as $option): ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <label class="block font-semibold mb-2">
                                        <?= htmlspecialchars($option['name']) ?>
                                        <?php if ($option['required']): ?>
                                            <span class="text-red-500">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <?php if ($option['type'] === 'select'): ?>
                                        <select name="options[<?= $option['id'] ?>]" 
                                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-brand focus:border-brand"
                                                <?= $option['required'] ? 'required' : '' ?>>
                                            <option value="">Select an option</option>
                                            <?php foreach ($option['values'] as $value): ?>
                                                <option value="<?= $value['id'] ?>" 
                                                        data-price="<?= $value['price_modifier'] ?>">
                                                    <?= htmlspecialchars($value['value']) ?>
                                                    <?php if ($value['price_modifier'] > 0): ?>
                                                        (+<?= Helper::formatCurrency($value['price_modifier']) ?>)
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Add to Cart Form -->
                <form id="add-to-cart-form" class="mb-6">
                    <?= $csrfField ?? '' ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="flex items-center gap-4 mb-4">
                        <label class="font-semibold">Quantity:</label>
                        <div class="flex items-center border border-gray-300 rounded-lg">
                            <button type="button" id="qty-decrease" class="px-4 py-2 hover:bg-gray-100">-</button>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" 
                                   class="w-16 text-center border-0 focus:ring-0" readonly>
                            <button type="button" id="qty-increase" class="px-4 py-2 hover:bg-gray-100">+</button>
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-brand text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-brand-dark transition transform hover:scale-105 shadow-lg">
                        <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                    </button>
                </form>

                <!-- Product Info Tabs -->
                <?php if ($product['description']): ?>
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold mb-3">Description</h3>
                        <div class="text-gray-700 prose max-w-none">
                            <?= nl2br(htmlspecialchars($product['description'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
            <div class="border-t pt-12">
                <h2 class="text-3xl font-bold mb-8 text-center">You Might Also Like</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($relatedProducts as $related): ?>
                        <a href="<?= BASE_URL ?>/product/<?= htmlspecialchars($related['slug']) ?>" 
                           class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition transform hover:-translate-y-2">
                            <?php if ($related['image']): ?>
                                <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($related['image']) ?>" 
                                     alt="<?= htmlspecialchars($related['name']) ?>" 
                                     class="w-full h-40 object-cover">
                            <?php else: ?>
                                <div class="w-full h-40 bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-utensils text-4xl text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                            <div class="p-4">
                                <h3 class="font-semibold mb-2"><?= htmlspecialchars($related['name']) ?></h3>
                                <p class="text-brand font-bold"><?= Helper::formatCurrency($related['price']) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    document.getElementById('qty-increase')?.addEventListener('click', function() {
        const qty = document.getElementById('quantity');
        qty.value = parseInt(qty.value) + 1;
    });
    
    document.getElementById('qty-decrease')?.addEventListener('click', function() {
        const qty = document.getElementById('quantity');
        if (parseInt(qty.value) > 1) {
            qty.value = parseInt(qty.value) - 1;
        }
    });
    
    // Add to cart
    document.getElementById('add-to-cart-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const options = {};
        
        // Collect options
        document.querySelectorAll('#product-options select').forEach(select => {
            if (select.value) {
                const optionId = select.name.match(/\[(\d+)\]/)[1];
                options[optionId] = select.value;
            }
        });
        
        formData.append('options', JSON.stringify(options));
        
        fetch('<?= BASE_URL ?>/cart/add', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Item added to cart!');
                // Update cart count
                if (document.querySelector('.cart-count')) {
                    document.querySelector('.cart-count').textContent = data.cart_count;
                }
            } else {
                alert(data.message || 'Failed to add item to cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
});
</script>

<?php
$content = ob_get_clean();
$page_title = $product['name'];
require_once APP_PATH . '/views/layouts/main.php';
?>

