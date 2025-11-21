<?php
use App\Core\Helper;
$content = ob_start();
?>

<!-- Category Header -->
<section class="bg-gradient-to-r from-brand to-brand-dark text-white py-12">
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

<!-- Products Grid -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<?php if (!empty($products)): ?>
            <?php
            $totalProducts = $totalProducts ?? count($products);
            ?>
            <div class="mb-6 flex justify-between items-center">
                <p class="text-gray-600">Showing <?= count($products) ?> of <?= $totalProducts ?> items</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
                        
                        <a href="<?= BASE_URL ?>/product/<?= htmlspecialchars($product['slug']) ?>" 
                           class="block w-full bg-brand text-white text-center py-2 rounded-lg font-semibold hover:bg-brand-dark transition transform hover:scale-105">
                            View Details
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="mt-8 flex justify-center">
                <nav class="flex space-x-2">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?= $currentPage - 1 ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="px-4 py-2 bg-brand text-white rounded-lg font-semibold"><?= $i ?></span>
                        <?php elseif ($i == 1 || $i == $totalPages || ($i >= $currentPage - 2 && $i <= $currentPage + 2)): ?>
                            <a href="?page=<?= $i ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"><?= $i ?></a>
                        <?php elseif ($i == $currentPage - 3 || $i == $currentPage + 3): ?>
                            <span class="px-4 py-2 text-gray-500">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-utensils text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-700 mb-2">No products found</h3>
                <p class="text-gray-500 mb-6">This category doesn't have any products yet.</p>
                <a href="<?= BASE_URL ?>/menu" class="inline-block bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-dark transition">
                    Browse All Categories
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
$page_title = $category['name'];
require_once APP_PATH . '/views/layouts/main.php';
?>

