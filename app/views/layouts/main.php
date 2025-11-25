<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= isset($meta_description) ? htmlspecialchars($meta_description) : 'The Pembina Pint and Restaurant - Authentic African and Nigerian Cuisine in Morden, Manitoba' ?>">
    <meta name="keywords" content="African food, Nigerian restaurant, Morden, Manitoba, catering, bar">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>The Pembina Pint and Restaurant</title>
    
    <!-- Tailwind CSS (local copy) -->
    <script src="<?= BASE_URL ?>/public/js/tailwindcdn.js"></script>
    
    <!-- Custom Brand Colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand': '#F4A460',
                        'brand-dark': '#8B4513',
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/images/logo.png">
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/images/logo.png">
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50 backdrop-blur-sm bg-white/95" role="navigation" aria-label="Main navigation">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="<?= BASE_URL ?>" class="flex items-center group" aria-label="Home">
                        <img src="<?= BASE_URL ?>/public/images/logo.png" alt="The Pembina Pint and Restaurant" class="h-12 w-12 mr-3 transition-transform group-hover:scale-105">
                        <span class="text-xl font-bold text-brand">The Pembina Pint</span>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-6">
                    <a href="<?= BASE_URL ?>" class="text-gray-700 hover:text-brand transition-colors duration-200 font-medium relative group">
                        Home
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand transition-all duration-200 group-hover:w-full"></span>
                    </a>
                    
                    <!-- Menu Dropdown -->
                    <div class="relative menu-dropdown">
                        <a href="<?= BASE_URL ?>/menu" class="text-gray-700 hover:text-brand transition-colors duration-200 font-medium relative group flex items-center">
                            Menu
                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand transition-all duration-200 group-hover:w-full"></span>
                        </a>
                        <?php
                        // Get main categories for dropdown
                        require_once APP_PATH . '/models/Category.php';
                        $catModel = new \App\Models\Category();
                        $navCategories = $catModel->getAllWithCount();
                        $topCategories = array_slice($navCategories, 0, 3);
                        ?>
                        <?php if (!empty($topCategories)): ?>
                        <div class="menu-dropdown-content">
                            <div class="py-2">
                                <?php foreach ($topCategories as $cat): ?>
                                <a href="<?= BASE_URL ?>/menu/<?= htmlspecialchars($cat['slug']) ?>" 
                                   class="block px-4 py-2 text-gray-700 hover:bg-brand hover:text-white transition-colors">
                                    <?= htmlspecialchars($cat['name']) ?>
                                    <span class="text-xs text-gray-500 ml-2">(<?= $cat['product_count'] ?>)</span>
                                </a>
                                <?php endforeach; ?>
                                <div class="border-t border-gray-200 my-1"></div>
                                <a href="<?= BASE_URL ?>/menu" class="block px-4 py-2 text-brand hover:bg-brand hover:text-white transition-colors font-semibold">
                                    View All Categories <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <a href="<?= BASE_URL ?>/cart" class="text-gray-700 hover:text-brand transition-colors duration-200 font-medium relative group flex items-center">
                        <i class="fas fa-shopping-cart mr-1"></i> Cart
                        <span id="cart-count-badge" class="ml-1 bg-brand text-white rounded-full px-2 py-0.5 text-xs font-semibold min-w-[20px] text-center <?= (!isset($_SESSION['cart_count']) || $_SESSION['cart_count'] == 0) ? 'hidden' : '' ?>"><?= $_SESSION['cart_count'] ?? 0 ?></span>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand transition-all duration-200 group-hover:w-full"></span>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= BASE_URL ?>/account" class="text-gray-700 hover:text-brand transition-colors duration-200 font-medium relative group">
                            Account
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand transition-all duration-200 group-hover:w-full"></span>
                        </a>
                        <?php if (in_array($_SESSION['user_role'], ['super_admin', 'admin', 'data_entry'])): ?>
                            <a href="<?= BASE_URL ?>/admin" class="text-gray-700 hover:text-brand transition-colors duration-200 font-medium relative group">
                                Admin
                                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand transition-all duration-200 group-hover:w-full"></span>
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/logout" class="text-gray-700 hover:text-red-600 transition-colors duration-200 font-medium">Logout</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login" class="text-gray-700 hover:text-brand transition-colors duration-200 font-medium">Login</a>
                        <a href="<?= BASE_URL ?>/register" class="bg-brand text-white px-4 py-2 rounded-lg hover:bg-brand-dark transition-all duration-200 font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5">Register</a>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-brand" id="mobile-menu-btn" aria-label="Toggle menu" aria-expanded="false">
                        <i class="fas fa-bars text-2xl" id="mobile-menu-icon"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div class="md:hidden hidden transition-all duration-300 ease-in-out" id="mobile-menu" role="menu">
            <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t shadow-lg">
                <a href="<?= BASE_URL ?>" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium" role="menuitem">Home</a>
                <a href="<?= BASE_URL ?>/menu" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium" role="menuitem">Menu</a>
                <a href="<?= BASE_URL ?>/cart" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium flex items-center justify-between" role="menuitem">
                    <span>Cart</span>
                    <span id="cart-count-badge-mobile" class="bg-brand text-white rounded-full px-2 py-0.5 text-xs font-semibold min-w-[20px] text-center <?= (!isset($_SESSION['cart_count']) || $_SESSION['cart_count'] == 0) ? 'hidden' : '' ?>"><?= $_SESSION['cart_count'] ?? 0 ?></span>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= BASE_URL ?>/account" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium" role="menuitem">Account</a>
                    <a href="<?= BASE_URL ?>/logout" class="block px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors font-medium" role="menuitem">Logout</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium" role="menuitem">Login</a>
                    <a href="<?= BASE_URL ?>/register" class="block px-3 py-2 bg-brand text-white rounded-lg hover:bg-brand-dark transition-colors font-medium text-center" role="menuitem">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- CSRF Token (hidden, for AJAX requests) -->
    <?php
    if (!isset($csrfField)) {
        require_once APP_PATH . '/core/Security/CSRF.php';
        $csrf = new \App\Core\Security\CSRF();
        $csrfField = $csrf->getTokenField();
    }
    ?>
    <?= $csrfField ?? '' ?>

    <!-- Main Content -->
    <main>
        <?php echo $content ?? ''; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php
                use App\Core\Helper;
                $footerEmail = Helper::getSetting('site_email', 'info@pembinapint.com');
                $footerPhone = Helper::getSetting('site_phone', '(204) XXX-XXXX');
                $footerAddress = Helper::getSetting('site_address', '282 Loren Drive, Morden, Manitoba, Canada');
                $footerHours = Helper::getSetting('business_hours', 'Mon-Sat: 11AM-10PM, Sun: 12PM-9PM');
                $socialFacebook = Helper::getSetting('social_facebook', '');
                $socialInstagram = Helper::getSetting('social_instagram', '');
                $socialTwitter = Helper::getSetting('social_twitter', '');
                ?>
                <div>
                    <h3 class="text-xl font-bold mb-4">The Pembina Pint</h3>
                    <p class="text-gray-400"><?= htmlspecialchars($footerAddress) ?></p>
                    <p class="text-gray-400 mt-2">African & Nigerian Restaurant | Bar | Catering Services</p>
                    <?php if ($footerHours): ?>
                        <p class="text-gray-400 mt-2">
                            <i class="fas fa-clock mr-2"></i><?= htmlspecialchars($footerHours) ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="<?= BASE_URL ?>/menu" class="text-gray-400 hover:text-white transition">Menu</a></li>
                        <li><a href="<?= BASE_URL ?>/page/about" class="text-gray-400 hover:text-white transition">About Us</a></li>
                        <li><a href="<?= BASE_URL ?>/page/terms-of-service" class="text-gray-400 hover:text-white transition">Terms of Service</a></li>
                        <li><a href="<?= BASE_URL ?>/page/privacy-policy" class="text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact</h3>
                    <p class="text-gray-400">
                        <i class="fas fa-envelope mr-2"></i>
                        <a href="mailto:<?= htmlspecialchars($footerEmail) ?>" class="hover:text-white transition"><?= htmlspecialchars($footerEmail) ?></a>
                    </p>
                    <p class="text-gray-400 mt-2">
                        <i class="fas fa-phone mr-2"></i>
                        <a href="tel:<?= htmlspecialchars($footerPhone) ?>" class="hover:text-white transition"><?= htmlspecialchars($footerPhone) ?></a>
                    </p>
                    <?php if ($socialFacebook || $socialInstagram || $socialTwitter): ?>
                        <div class="flex gap-4 mt-4">
                            <?php if ($socialFacebook): ?>
                                <a href="<?= htmlspecialchars($socialFacebook) ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition" aria-label="Facebook">
                                    <i class="fab fa-facebook text-xl"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($socialInstagram): ?>
                                <a href="<?= htmlspecialchars($socialInstagram) ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition" aria-label="Instagram">
                                    <i class="fab fa-instagram text-xl"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($socialTwitter): ?>
                                <a href="<?= htmlspecialchars($socialTwitter) ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition" aria-label="Twitter">
                                    <i class="fab fa-twitter text-xl"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-400">
                    &copy; <?= date('Y') ?> The Pembina Pint and Restaurant. All rights reserved.
                </p>
                <p class="text-gray-400 mt-2">
                    Website designed by <a href="https://www.webspace.ng" target="_blank" rel="noopener noreferrer" class="text-brand hover:text-brand-dark transition">Webspace</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?= BASE_URL ?>/public/js/main.js"></script>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

