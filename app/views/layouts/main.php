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
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="<?= BASE_URL ?>" class="flex items-center">
                        <img src="<?= BASE_URL ?>/public/images/logo.png" alt="The Pembina Pint and Restaurant" class="h-12 w-12 mr-3">
                        <span class="text-xl font-bold text-brand">The Pembina Pint</span>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-6">
                    <a href="<?= BASE_URL ?>" class="text-gray-700 hover:text-brand transition">Home</a>
                    <a href="<?= BASE_URL ?>/menu" class="text-gray-700 hover:text-brand transition">Menu</a>
                    <a href="<?= BASE_URL ?>/cart" class="text-gray-700 hover:text-brand transition">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <span id="cart-count-badge" class="ml-1 bg-brand text-white rounded-full px-2 py-1 text-xs <?= (!isset($_SESSION['cart_count']) || $_SESSION['cart_count'] == 0) ? 'hidden' : '' ?>"><?= $_SESSION['cart_count'] ?? 0 ?></span>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= BASE_URL ?>/account" class="text-gray-700 hover:text-brand transition">Account</a>
                        <?php if (in_array($_SESSION['user_role'], ['super_admin', 'admin', 'data_entry'])): ?>
                            <a href="<?= BASE_URL ?>/admin" class="text-gray-700 hover:text-brand transition">Admin</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/logout" class="text-gray-700 hover:text-brand transition">Logout</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login" class="text-gray-700 hover:text-brand transition">Login</a>
                        <a href="<?= BASE_URL ?>/register" class="bg-brand text-white px-4 py-2 rounded hover:bg-brand-dark transition">Register</a>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-700" id="mobile-menu-btn">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t">
                <a href="<?= BASE_URL ?>" class="block px-3 py-2 text-gray-700 hover:bg-gray-100">Home</a>
                <a href="<?= BASE_URL ?>/menu" class="block px-3 py-2 text-gray-700 hover:bg-gray-100">Menu</a>
                <a href="<?= BASE_URL ?>/cart" class="block px-3 py-2 text-gray-700 hover:bg-gray-100">
                    Cart <span id="cart-count-badge-mobile" class="ml-1 bg-brand text-white rounded-full px-2 py-1 text-xs <?= (!isset($_SESSION['cart_count']) || $_SESSION['cart_count'] == 0) ? 'hidden' : '' ?>"><?= $_SESSION['cart_count'] ?? 0 ?></span>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= BASE_URL ?>/account" class="block px-3 py-2 text-gray-700 hover:bg-gray-100">Account</a>
                    <a href="<?= BASE_URL ?>/logout" class="block px-3 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login" class="block px-3 py-2 text-gray-700 hover:bg-gray-100">Login</a>
                    <a href="<?= BASE_URL ?>/register" class="block px-3 py-2 text-gray-700 hover:bg-gray-100">Register</a>
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
                <div>
                    <h3 class="text-xl font-bold mb-4">The Pembina Pint</h3>
                    <p class="text-gray-400">282 Loren Drive, Morden, Manitoba, Canada</p>
                    <p class="text-gray-400 mt-2">African & Nigerian Restaurant | Bar | Catering Services</p>
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
                    <p class="text-gray-400">Email: info@pembinapint.com</p>
                    <p class="text-gray-400 mt-2">Phone: (204) XXX-XXXX</p>
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

