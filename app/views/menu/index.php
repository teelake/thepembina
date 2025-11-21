<?php
use App\Core\Helper;
$content = ob_start();
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-brand via-yellow-500 to-brand-dark text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl md:text-6xl font-bold mb-4 animate-fade-in">Our Delicious Menu</h1>
        <p class="text-xl md:text-2xl mb-8">Authentic African & Nigerian Cuisine</p>
        <div class="flex flex-wrap justify-center gap-4 text-sm">
            <span class="bg-white/20 px-4 py-2 rounded-full">🍛 Jollof Rice</span>
            <span class="bg-white/20 px-4 py-2 rounded-full">🍖 Suya</span>
            <span class="bg-white/20 px-4 py-2 rounded-full">🥘 Egusi Soup</span>
            <span class="bg-white/20 px-4 py-2 rounded-full">🍌 Plantain Delights</span>
        </div>
    </div>
</section>

<!-- Featured Products -->
<?php if (!empty($featuredProducts)): ?>
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">⭐ Featured Dishes</h2>
            <p class="text-gray-600">Customer favorites you don't want to miss!</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                <?php if ($product['image']): ?>
                    <div class="relative h-48 overflow-hidden">
                        <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($product['image']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                        <div class="absolute top-2 right-2 bg-brand text-white px-3 py-1 rounded-full text-sm font-semibold">
                            Featured
                        </div>
                    </div>
                <?php else: ?>
                    <div class="h-48 bg-gradient-to-br from-brand/20 to-brand-dark/20 flex items-center justify-center">
                        <i class="fas fa-utensils text-6xl text-brand"></i>
                    </div>
                <?php endif; ?>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2 text-gray-900"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                        <?= htmlspecialchars(substr($product['short_description'] ?? $product['description'] ?? 'Delicious African cuisine', 0, 100)) ?>
                        <?= strlen($product['short_description'] ?? $product['description'] ?? '') > 100 ? '...' : '' ?>
                    </p>
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-bold text-brand"><?= Helper::formatCurrency($product['price']) ?></span>
                        <a href="<?= BASE_URL ?>/product/<?= htmlspecialchars($product['slug']) ?>" 
                           class="bg-brand text-white px-6 py-2 rounded-lg font-semibold hover:bg-brand-dark transition transform hover:scale-105">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Categories Grid -->
<?php if (!empty($categories)): ?>
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Browse by Category</h2>
            <p class="text-gray-600">Explore our menu organized by your favorite dishes</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($categories as $category): ?>
            <a href="<?= BASE_URL ?>/menu/<?= htmlspecialchars($category['slug']) ?>" 
               class="group bg-white rounded-xl shadow-md p-6 text-center hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border-2 border-transparent hover:border-brand">
                <?php if ($category['image']): ?>
                    <div class="mb-4 overflow-hidden rounded-lg">
                        <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($category['image']) ?>" 
                             alt="<?= htmlspecialchars($category['name']) ?>" 
                             class="w-full h-32 object-cover group-hover:scale-110 transition-transform duration-300">
                    </div>
                <?php else: ?>
                    <div class="mb-4 h-32 bg-gradient-to-br from-brand/30 to-brand-dark/30 rounded-lg flex items-center justify-center group-hover:from-brand/50 group-hover:to-brand-dark/50 transition-all">
                        <i class="fas fa-folder-open text-5xl text-brand group-hover:scale-110 transition-transform"></i>
                    </div>
                <?php endif; ?>
                <h3 class="text-lg font-bold mb-2 text-gray-900 group-hover:text-brand transition"><?= htmlspecialchars($category['name']) ?></h3>
                <p class="text-sm text-gray-600 mb-2"><?= $category['product_count'] ?> <?= $category['product_count'] == 1 ? 'item' : 'items' ?></p>
                <span class="inline-block text-brand font-semibold group-hover:translate-x-1 transition-transform">
                    View Menu <i class="fas fa-arrow-right ml-1"></i>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Call to Action -->
<section class="py-12 bg-gradient-to-r from-brand to-brand-dark text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Ready to Order?</h2>
        <p class="text-xl mb-6">Experience authentic African flavors delivered to your door</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="<?= BASE_URL ?>/cart" class="bg-white text-brand px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition transform hover:scale-105 shadow-lg">
                <i class="fas fa-shopping-cart mr-2"></i> View Cart
            </a>
            <a href="<?= BASE_URL ?>/menu" class="bg-brand-dark text-white px-8 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition transform hover:scale-105 border-2 border-white">
                Browse All Items
            </a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
$page_title = 'Our Menu';
require_once APP_PATH . '/views/layouts/main.php';
?>

