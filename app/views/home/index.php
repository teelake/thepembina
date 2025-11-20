<?php
use App\Core\Helper;
$content = ob_start();

$defaultSlides = [
    [
        'title' => 'Welcome to The Pembina Pint',
        'subtitle' => 'Authentic African & Nigerian Cuisine',
        'description' => 'Experience vibrant flavors, handcrafted cocktails, and a warm atmosphere right in Manitoba.',
        'button_text' => 'Explore Menu',
        'button_link' => '/menu',
        'image' => 'images/hero/default-slide.jpg'
    ]
];

$slides = !empty($heroSlides) ? $heroSlides : $defaultSlides;
?>

<!-- Hero Slider -->
<section class="relative hero-slider-wrapper">
    <div class="hero-slider">
        <?php foreach ($slides as $index => $slide): ?>
            <?php
                $imagePath = $slide['image'] ?? 'images/hero/default-slide.jpg';
                if (strpos($imagePath, 'http') === 0) {
                    $imageUrl = $imagePath;
                } else {
                    $imageUrl = BASE_URL . '/public/' . ltrim($imagePath, '/');
                }
            ?>
            <div class="hero-slide <?= $index === 0 ? 'active' : '' ?>" data-slide-index="<?= $index ?>">
                <div class="hero-slide-bg" style="background-image: url('<?= $imageUrl ?>');"></div>
                <div class="hero-slide-overlay"></div>
                <div class="hero-slide-content">
                    <?php if (!empty($slide['subtitle'])): ?>
                        <p class="text-sm uppercase tracking-wide text-white/80 mb-3"><?= htmlspecialchars($slide['subtitle']) ?></p>
                    <?php endif; ?>
                    <h1 class="text-4xl md:text-6xl font-bold text-white mb-4"><?= htmlspecialchars($slide['title']) ?></h1>
                    <?php if (!empty($slide['description'])): ?>
                        <p class="text-lg md:text-xl text-white/90 mb-6 max-w-2xl"><?= htmlspecialchars($slide['description']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($slide['button_text']) && !empty($slide['button_link'])): ?>
                        <a href="<?= htmlspecialchars($slide['button_link']) ?>"
                           class="inline-flex items-center bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-brand-dark transition transform hover:scale-105 shadow-lg">
                            <?= htmlspecialchars($slide['button_text']) ?>
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (count($slides) > 1): ?>
        <div class="hero-slider-controls">
            <button class="hero-slider-btn prev" aria-label="Previous slide"><i class="fas fa-chevron-left"></i></button>
            <button class="hero-slider-btn next" aria-label="Next slide"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="hero-slider-dots">
            <?php foreach ($slides as $index => $slide): ?>
                <button class="hero-slider-dot <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>"></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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

<!-- Testimonials -->
<?php if (!empty($testimonials)): ?>
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <p class="text-brand font-semibold uppercase tracking-wide">Kind Words</p>
            <h2 class="text-3xl font-bold text-gray-900">What Guests Are Saying</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="bg-gray-50 rounded-2xl p-6 shadow hover:shadow-lg transition">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-brand/20 flex items-center justify-center text-brand font-bold">
                            <?= strtoupper(substr($testimonial['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900"><?= htmlspecialchars($testimonial['name']) ?></p>
                            <?php if ($testimonial['title']): ?>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($testimonial['title']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="text-gray-700 mb-4 leading-relaxed"><?= htmlspecialchars($testimonial['message']) ?></p>
                    <div class="flex items-center text-yellow-400">
                        <?php for ($i = 0; $i < (int)$testimonial['rating']; $i++): ?>
                            <i class="fas fa-star"></i>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Upcoming Events -->
<?php if (!empty($events)): ?>
<section class="py-12 bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <p class="text-brand font-semibold uppercase tracking-wide">Cultural Nights</p>
               <h2 class="text-3xl font-bold">Upcoming Events</h2>
            </div>
            <a href="<?= BASE_URL ?>/page/events-calendar" class="text-brand hover:text-brand-dark font-semibold">View full calendar →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($events as $event): ?>
                <div class="bg-white/10 rounded-2xl p-6 backdrop-blur">
                    <p class="text-brand font-semibold mb-2">
                        <?= date('M d, Y', strtotime($event['event_date'])) ?>
                        <?php if (!empty($event['event_time'])): ?>
                            · <?= date('g:i A', strtotime($event['event_time'])) ?>
                        <?php endif; ?>
                    </p>
                    <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($event['title']) ?></h3>
                    <?php if ($event['subtitle']): ?>
                        <p class="text-brand mb-3"><?= htmlspecialchars($event['subtitle']) ?></p>
                    <?php endif; ?>
                    <p class="text-gray-200 mb-4"><?= htmlspecialchars(mb_strimwidth($event['description'], 0, 120, '...')) ?></p>
                    <?php if ($event['location']): ?>
                        <p class="text-sm text-gray-300"><i class="fas fa-map-marker-alt mr-2"></i><?= htmlspecialchars($event['location']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Newsletter -->
<section class="py-12 bg-brand text-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="uppercase tracking-[0.3em] text-sm font-semibold mb-3">Newsletter</p>
        <h2 class="text-3xl font-bold mb-4">Stay in the loop with Pembina Pint</h2>
        <p class="text-white/80 mb-8">Get event invites, menu drops, and tasting menu news straight to your inbox.</p>
        <form id="newsletter-form" class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl mx-auto">
            <?= $this->csrf->getTokenField() ?>
            <input type="text" name="name" placeholder="Your name" class="form-input bg-white text-gray-900">
            <input type="email" name="email" placeholder="Email address *" required class="form-input bg-white text-gray-900">
            <button type="submit" class="bg-gray-900 text-white font-semibold rounded-lg px-6 py-3 hover:bg-black transition">Subscribe</button>
        </form>
        <p id="newsletter-feedback" class="mt-4 text-sm"></p>
    </div>
</section>

<?php
$content = ob_get_clean();
$page_title = 'Home';
require_once APP_PATH . '/views/layouts/main.php';
?>

