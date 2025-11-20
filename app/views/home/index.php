<?php
use App\Core\Helper;
$content = ob_start();
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-brand to-brand-dark text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl font-bold mb-4">Welcome to The Pembina Pint</h1>
        <p class="text-xl mb-8">Authentic African & Nigerian Cuisine in Morden, Manitoba</p>
        <a href="<?= BASE_URL ?>/menu" class="bg-white text-brand px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition inline-block">
            View Menu
        </a>
    </div>
</section>

<!-- Featured Products -->
<?php if (!empty($featuredProducts)): ?>
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-8">Featured Dishes</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <?php if ($product['image']): ?>
                    <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-48 object-cover">
                <?php else: ?>
                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-utensils text-4xl text-gray-400"></i>
                    </div>
                <?php endif; ?>
                <div class="p-4">
                    <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="text-gray-600 text-sm mb-3"><?= htmlspecialchars(substr($product['short_description'] ?? $product['description'] ?? '', 0, 100)) ?>...</p>
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-bold text-brand"><?= Helper::formatCurrency($product['price']) ?></span>
                        <a href="<?= BASE_URL ?>/product/<?= htmlspecialchars($product['slug']) ?>" class="bg-brand text-white px-4 py-2 rounded hover:bg-brand-dark transition">
                            View
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Categories -->
<?php if (!empty($categories)): ?>
<section class="py-12 bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-8">Our Menu Categories</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($categories as $category): ?>
            <a href="<?= BASE_URL ?>/menu/<?= htmlspecialchars($category['slug']) ?>" class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
                <?php if ($category['image']): ?>
                    <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($category['image']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="w-full h-32 object-cover rounded mb-4">
                <?php else: ?>
                    <div class="w-full h-32 bg-gray-200 rounded mb-4 flex items-center justify-center">
                        <i class="fas fa-folder text-4xl text-gray-400"></i>
                    </div>
                <?php endif; ?>
                <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($category['name']) ?></h3>
                <p class="text-gray-600 text-sm"><?= $category['product_count'] ?> items</p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
$content = ob_get_clean();
$page_title = 'Home';
require_once APP_PATH . '/views/layouts/main.php';
?>

