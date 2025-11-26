<?php
use App\Core\Helper;
$content = ob_start();

$filters = $filters ?? [];
$priceRange = $priceRange ?? ['min' => 0, 'max' => 1000];
$currentMinPrice = isset($filters['min_price']) ? (float)$filters['min_price'] : $priceRange['min'];
$currentMaxPrice = isset($filters['max_price']) ? (float)$filters['max_price'] : $priceRange['max'];
?>

<!-- Category Header -->
<section class="hero-brand-gradient text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="mb-4 text-sm">
            <a href="<?= BASE_URL ?>" class="hover:underline">Home</a> / 
            <a href="<?= BASE_URL ?>/menu" class="hover:underline">Menu</a> / 
            <span><?= htmlspecialchars($category['name']) ?></span>
        </nav>
        <h1 class="text-4xl md:text-5xl font-bold mb-4"><?= htmlspecialchars($category['name']) ?></h1>
        <?php if ($category['description']): ?>
            <p class="text-xl opacity-90"><?= htmlspecialchars($category['description']) ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- Filters and Products Section -->
<section class="py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-6">
            
            <!-- Filter Sidebar -->
            <aside class="lg:w-64 flex-shrink-0">
                <!-- Mobile Filter Toggle -->
                <button id="filter-toggle" class="lg:hidden w-full bg-white border-2 border-gray-300 rounded-lg px-4 py-3 flex items-center justify-between font-semibold text-gray-700 hover:bg-gray-50 transition mb-4">
                    <span><i class="fas fa-filter mr-2"></i> Filters</span>
                    <i class="fas fa-chevron-down transition-transform" id="filter-chevron"></i>
                </button>
                
                <!-- Filter Panel -->
                <div id="filter-panel" class="hidden lg:block bg-white rounded-xl shadow-md p-6 space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Filters</h2>
                        <?php if (!empty($filters)): ?>
                            <a href="?" class="text-sm text-brand hover:text-brand-dark font-semibold">
                                <i class="fas fa-times mr-1"></i> Clear All
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <form id="filter-form" method="GET" action="">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i> Search
                            </label>
                            <input type="text" 
                                   name="search" 
                                   value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                                   placeholder="Search products..."
                                   class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                        </div>
                        
                        <!-- Sort By -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-sort mr-1"></i> Sort By
                            </label>
                            <select name="sort" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                                <option value="default" <?= (!isset($filters['sort']) || $filters['sort'] === 'default') ? 'selected' : '' ?>>Default</option>
                                <option value="price_asc" <?= (isset($filters['sort']) && $filters['sort'] === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="price_desc" <?= (isset($filters['sort']) && $filters['sort'] === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
                                <option value="name_asc" <?= (isset($filters['sort']) && $filters['sort'] === 'name_asc') ? 'selected' : '' ?>>Name: A-Z</option>
                                <option value="name_desc" <?= (isset($filters['sort']) && $filters['sort'] === 'name_desc') ? 'selected' : '' ?>>Name: Z-A</option>
                                <option value="newest" <?= (isset($filters['sort']) && $filters['sort'] === 'newest') ? 'selected' : '' ?>>Newest First</option>
                                <option value="featured" <?= (isset($filters['sort']) && $filters['sort'] === 'featured') ? 'selected' : '' ?>>Featured First</option>
                            </select>
                        </div>
                        
                        <!-- Price Range -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-dollar-sign mr-1"></i> Price Range
                            </label>
                            <div class="space-y-3">
                                <div class="flex gap-2">
                                    <input type="number" 
                                           name="min_price" 
                                           value="<?= $currentMinPrice ?>" 
                                           min="<?= $priceRange['min'] ?>" 
                                           max="<?= $priceRange['max'] ?>"
                                           step="0.01"
                                           placeholder="Min"
                                           class="flex-1 px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-brand focus:ring-2 focus:ring-brand/20 transition text-sm">
                                    <input type="number" 
                                           name="max_price" 
                                           value="<?= $currentMaxPrice ?>" 
                                           min="<?= $priceRange['min'] ?>" 
                                           max="<?= $priceRange['max'] ?>"
                                           step="0.01"
                                           placeholder="Max"
                                           class="flex-1 px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-brand focus:ring-2 focus:ring-brand/20 transition text-sm">
                                </div>
                                <div class="text-xs text-gray-500 text-center">
                                    <?= Helper::formatCurrency($priceRange['min']) ?> - <?= Helper::formatCurrency($priceRange['max']) ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Availability -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-box mr-1"></i> Availability
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                           name="availability" 
                                           value="in_stock" 
                                           <?= (isset($filters['availability']) && $filters['availability'] === 'in_stock') ? 'checked' : '' ?>
                                           class="mr-2 text-brand focus:ring-brand">
                                    <span class="text-sm text-gray-700">In Stock</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                           name="availability" 
                                           value="low_stock" 
                                           <?= (isset($filters['availability']) && $filters['availability'] === 'low_stock') ? 'checked' : '' ?>
                                           class="mr-2 text-brand focus:ring-brand">
                                    <span class="text-sm text-gray-700">Low Stock</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                           name="availability" 
                                           value="out_of_stock" 
                                           <?= (isset($filters['availability']) && $filters['availability'] === 'out_of_stock') ? 'checked' : '' ?>
                                           class="mr-2 text-brand focus:ring-brand">
                                    <span class="text-sm text-gray-700">Out of Stock</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                           name="availability" 
                                           value="" 
                                           <?= (!isset($filters['availability']) || $filters['availability'] === '') ? 'checked' : '' ?>
                                           class="mr-2 text-brand focus:ring-brand">
                                    <span class="text-sm text-gray-700">All</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Featured -->
                        <div>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="featured" 
                                       value="1" 
                                       <?= (isset($filters['featured']) && $filters['featured'] === '1') ? 'checked' : '' ?>
                                       class="mr-2 text-brand focus:ring-brand rounded">
                                <span class="text-sm font-semibold text-gray-700">
                                    <i class="fas fa-star mr-1 text-yellow-500"></i> Featured Only
                                </span>
                            </label>
                        </div>
                        
                        <!-- Apply Filters Button -->
                        <button type="submit" class="w-full bg-brand text-white px-4 py-3 rounded-lg font-semibold hover:bg-brand-dark transition-all duration-200 shadow-sm hover:shadow-md">
                            <i class="fas fa-filter mr-2"></i> Apply Filters
                        </button>
                    </form>
                </div>
            </aside>
            
            <!-- Products Grid -->
            <div class="flex-1">
                <?php if (!empty($products)): ?>
                    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <p class="text-gray-600">
                            Showing <span class="font-semibold"><?= count($products) ?></span> of <span class="font-semibold"><?= $totalProducts ?? count($products) ?></span> items
                        </p>
                        <?php if (!empty($filters)): ?>
                            <div class="flex flex-wrap gap-2">
                                <?php if (!empty($filters['search'])): ?>
                                    <span class="inline-flex items-center bg-brand/10 text-brand px-3 py-1 rounded-full text-sm">
                                        Search: "<?= htmlspecialchars($filters['search']) ?>"
                                        <a href="?<?= http_build_query(array_diff_key($filters, ['search' => ''])) ?>" class="ml-2 hover:text-brand-dark">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </span>
                                <?php endif; ?>
                                <?php if (isset($filters['sort']) && $filters['sort'] !== 'default'): ?>
                                    <span class="inline-flex items-center bg-brand/10 text-brand px-3 py-1 rounded-full text-sm">
                                        Sorted
                                        <a href="?<?= http_build_query(array_diff_key($filters, ['sort' => ''])) ?>" class="ml-2 hover:text-brand-dark">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-6">
                        <?php foreach ($products as $product): ?>
                        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 group">
                            <?php if ($product['image']): ?>
                                <div class="relative h-48 overflow-hidden">
                                    <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($product['image']) ?>"
                                         alt="<?= htmlspecialchars($product['name']) ?>" 
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    <?php if ($product['is_featured']): ?>
                                        <div class="absolute top-2 right-2 bg-brand text-white px-2 py-1 rounded-full text-xs font-semibold">
                                            ⭐ Featured
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="h-48 bg-gradient-to-br from-brand/20 to-brand-dark/20 flex items-center justify-center">
                                    <i class="fas fa-utensils text-5xl text-brand"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-5">
                                <h3 class="text-lg font-bold mb-2 text-gray-900 group-hover:text-brand transition">
                                    <?= htmlspecialchars($product['name']) ?>
                                </h3>
                                <?php if ($product['short_description']): ?>
                                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                        <?= htmlspecialchars($product['short_description']) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-2xl font-bold text-brand"><?= Helper::formatCurrency($product['price']) ?></span>
                                    <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                        <span class="text-sm text-gray-500 line-through"><?= Helper::formatCurrency($product['compare_price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($product['manage_stock']) && isset($product['stock_quantity'])): ?>
                                    <div class="mb-3">
                                        <span class="inline-flex items-center text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">
                                            <i class="fas fa-box mr-1.5"></i>
                                            <?= (int)$product['stock_quantity'] ?> <?= (int)$product['stock_quantity'] == 1 ? 'item' : 'items' ?> available
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="flex gap-2">
                                    <button type="button" 
                                            class="add-to-cart-btn bg-brand text-white px-3 py-2 rounded-lg font-semibold hover:bg-brand-dark transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-1.5 text-sm flex-1 sm:flex-initial"
                                            data-product-id="<?= $product['id'] ?>"
                                            data-product-name="<?= htmlspecialchars($product['name']) ?>"
                                            aria-label="Add to cart">
                                        <i class="fas fa-shopping-cart"></i>
                                        <span class="hidden sm:inline">Add</span>
                                    </button>
                                    <a href="<?= BASE_URL ?>/product/<?= htmlspecialchars($product['slug']) ?>" 
                                       class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-all duration-200 text-sm flex items-center">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <div class="mt-8 flex justify-center">
                        <nav class="flex flex-wrap justify-center gap-2">
                            <?php if ($currentPage > 1): ?>
                                <a href="?<?= http_build_query(array_merge($filters, ['page' => $currentPage - 1])) ?>" 
                                   class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="px-4 py-2 bg-brand text-white rounded-lg font-semibold"><?= $i ?></span>
                                <?php elseif ($i == 1 || $i == $totalPages || ($i >= $currentPage - 2 && $i <= $currentPage + 2)): ?>
                                    <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>" 
                                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"><?= $i ?></a>
                                <?php elseif ($i == $currentPage - 3 || $i == $currentPage + 3): ?>
                                    <span class="px-4 py-2 text-gray-500">...</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?<?= http_build_query(array_merge($filters, ['page' => $currentPage + 1])) ?>" 
                                   class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-12 bg-white rounded-xl shadow-md">
                        <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-2xl font-bold text-gray-700 mb-2">No products found</h3>
                        <p class="text-gray-500 mb-6">
                            <?php if (!empty($filters)): ?>
                                Try adjusting your filters or 
                                <a href="?" class="text-brand hover:text-brand-dark font-semibold">clear all filters</a>
                            <?php else: ?>
                                This category doesn't have any products yet.
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($filters)): ?>
                            <a href="?" class="inline-block bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-dark transition">
                                Clear All Filters
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/menu" class="inline-block bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-dark transition">
                                Browse All Categories
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile filter toggle
    const filterToggle = document.getElementById('filter-toggle');
    const filterPanel = document.getElementById('filter-panel');
    const filterChevron = document.getElementById('filter-chevron');
    
    if (filterToggle && filterPanel) {
        filterToggle.addEventListener('click', function() {
            filterPanel.classList.toggle('hidden');
            if (filterChevron) {
                filterChevron.classList.toggle('rotate-180');
            }
        });
    }
    
    // Initialize add to cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            
            // Disable button during request
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Adding...';
            
            addToCartAjax(productId, 1, {}, function(success, message, cartCount) {
                // Re-enable button
                this.disabled = false;
                this.innerHTML = originalText;
                
                if (success) {
                    showPopupAlert('success', message || 'Item added to cart!');
                    updateCartCount(cartCount);
                } else {
                    showPopupAlert('error', message || 'Failed to add item to cart');
                }
            }.bind(this));
        });
    });
});
</script>

<?php
$content = ob_get_clean();
$page_title = $category['name'];
require_once APP_PATH . '/views/layouts/main.php';
?>
