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
<?php
use App\Models\NavigationMenuItem;
use App\Models\Category;

$headerNavItems = [];
$navModel = null;
$customNavEnabled = false;

try {
    require_once APP_PATH . '/models/NavigationMenuItem.php';
    $navModel = new NavigationMenuItem();
    if ($navModel->tableExists()) {
        $headerNavItems = $navModel->getActiveItems();
        $customNavEnabled = !empty($headerNavItems);
    }
} catch (\Exception $e) {
    $headerNavItems = [];
    $customNavEnabled = false;
}

$primaryCategories = [];
$otherCategories = [];

if (!$customNavEnabled) {
    require_once APP_PATH . '/models/Category.php';
    $catModel = new Category();
    $allCategories = $catModel->getAllWithCount();

    foreach ($allCategories as $cat) {
        if (isset($cat['show_in_nav']) && $cat['show_in_nav'] == 1) {
            $primaryCategories[] = $cat;
        } else {
            $otherCategories[] = $cat;
        }
    }

    usort($primaryCategories, function($a, $b) {
        $orderA = isset($a['nav_order']) ? (int)$a['nav_order'] : 999;
        $orderB = isset($b['nav_order']) ? (int)$b['nav_order'] : 999;
        return $orderA <=> $orderB;
    });
}
?>

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50 backdrop-blur-sm bg-white/95" role="navigation" aria-label="Main navigation">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="<?= BASE_URL ?>" class="flex items-center group" aria-label="Home">
                        <img src="<?= BASE_URL ?>/public/images/logo.png" alt="The Pembina Pint and Restaurant" class="h-12 w-12 sm:h-14 sm:w-14 mr-2 sm:mr-3 transition-transform group-hover:scale-105 logo-image flex-shrink-0">
                        <span class="text-lg sm:text-xl font-bold text-brand whitespace-nowrap">
                            <span class="hidden sm:inline">The Pembina Pint & Restaurant</span>
                            <span class="sm:hidden">The Pembina Pint</span>
                        </span>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-4 lg:space-x-6">
                    <a href="<?= BASE_URL ?>" class="text-gray-700 hover:text-brand transition-colors duration-200 font-medium relative group">
                        Home
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand transition-all duration-200 group-hover:w-full"></span>
                    </a>
                    
                    <?php if (!empty($headerNavItems)): ?>
                        <?php foreach ($headerNavItems as $item): ?>
                            <?php
                                $url = $navModel ? $navModel->getUrl($item) : '#';
                                $target = htmlspecialchars($item['target'] ?? '_self');
                            ?>
                            <a href="<?= htmlspecialchars($url) ?>"
                               target="<?= $target ?>"
                               class="text-gray-700 hover:text-brand transition-colors duration-200 font-medium relative group flex items-center gap-1">
                                <?php if (!empty($item['icon'])): ?>
                                    <i class="<?= htmlspecialchars($item['icon']) ?> text-sm"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($item['label']) ?>
                                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand transition-all duration-200 group-hover:w-full"></span>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($primaryCategories as $cat): ?>
                            <a href="<?= BASE_URL ?>/menu/<?= htmlspecialchars($cat['slug']) ?>"
                               class="text-gray-700 hover:text-brand transition-colors duration-200 font-medium relative group flex items-center">
                                <?= htmlspecialchars($cat['name']) ?>
                                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand transition-all duration-200 group-hover:w-full"></span>
                            </a>
                        <?php endforeach; ?>
                        <?php if (!empty($otherCategories)): ?>
                        <div class="relative menu-dropdown">
                            <a href="<?= BASE_URL ?>/menu" class="text-gray-700 hover:text-brand transition-colors duration-200 font-medium relative group flex items-center">
                                More
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand transition-all duration-200 group-hover:w-full"></span>
                            </a>
                            <div class="menu-dropdown-content">
                                <div class="py-2">
                                    <?php foreach ($otherCategories as $cat): ?>
                                    <a href="<?= BASE_URL ?>/menu/<?= htmlspecialchars($cat['slug']) ?>" 
                                       class="block px-4 py-2 text-gray-700 hover:bg-brand hover:text-white transition-colors">
                                        <?= htmlspecialchars($cat['name']) ?>
                                        <span class="text-xs text-gray-500 ml-2">(<?= $cat['product_count'] ?>)</span>
                                    </a>
                                    <?php endforeach; ?>
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <a href="<?= BASE_URL ?>/menu" class="block px-4 py-2 text-brand hover:bg-brand hover:text-white transition-colors font-semibold">
                                        View All Menu <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Cart - Primary CTA (Most Prominent) -->
                    <a href="<?= BASE_URL ?>/cart" class="bg-brand text-white px-4 py-2 rounded-lg hover:bg-brand-dark transition-all duration-200 font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center relative">
                        <i class="fas fa-shopping-cart mr-2"></i> Cart
                        <span id="cart-count-badge" class="ml-2 bg-white text-brand rounded-full px-2 py-0.5 text-xs font-bold min-w-[20px] text-center <?= (!isset($_SESSION['cart_count']) || $_SESSION['cart_count'] == 0) ? 'hidden' : '' ?>"><?= $_SESSION['cart_count'] ?? 0 ?></span>
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Account Dropdown (When Logged In) -->
                        <div class="relative account-dropdown">
                            <a href="<?= BASE_URL ?>/account" class="text-gray-700 hover:text-brand transition-colors duration-200 font-medium relative group flex items-center">
                                <i class="fas fa-user-circle mr-1 text-lg"></i>
                                <span class="hidden lg:inline"><?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'Account')[0]) ?></span>
                                <span class="lg:hidden">Account</span>
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </a>
                            <div class="account-dropdown-content">
                                <div class="py-2">
                                    <a href="<?= BASE_URL ?>/account" class="block px-4 py-2 text-gray-700 hover:bg-brand hover:text-white transition-colors">
                                        <i class="fas fa-user mr-2"></i> My Account
                                    </a>
                                    <a href="<?= BASE_URL ?>/account/orders" class="block px-4 py-2 text-gray-700 hover:bg-brand hover:text-white transition-colors">
                                        <i class="fas fa-shopping-bag mr-2"></i> My Orders
                                    </a>
                                    <?php if (in_array($_SESSION['user_role'], ['super_admin', 'admin', 'data_entry'])): ?>
                                        <div class="border-t border-gray-200 my-1"></div>
                                        <a href="<?= BASE_URL ?>/admin" class="block px-4 py-2 text-gray-700 hover:bg-brand hover:text-white transition-colors">
                                            <i class="fas fa-cog mr-2"></i> Admin Panel
                                        </a>
                                    <?php endif; ?>
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <a href="<?= BASE_URL ?>/logout" class="block px-4 py-2 text-red-600 hover:bg-red-50 transition-colors">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Login/Register - Subtle (Don't Create Friction) -->
                        <div class="flex items-center gap-2 text-sm">
                            <a href="<?= BASE_URL ?>/login" class="text-gray-600 hover:text-brand transition-colors duration-200">Login</a>
                            <span class="text-gray-300">|</span>
                            <a href="<?= BASE_URL ?>/register" class="text-gray-600 hover:text-brand transition-colors duration-200">Sign Up</a>
                        </div>
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
                <?php if (!empty($headerNavItems)): ?>
                    <?php foreach ($headerNavItems as $menuItem):
                        $menuUrl = $navModel ? $navModel->getUrl($menuItem) : '#';
                    ?>
                        <a href="<?= htmlspecialchars($menuUrl) ?>" 
                           target="<?= htmlspecialchars($menuItem['target'] ?? '_self') ?>"
                           class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium" 
                           role="menuitem">
                            <?php if ($menuItem['icon']): ?>
                                <i class="<?= htmlspecialchars($menuItem['icon']) ?> mr-2"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($menuItem['label']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ($primaryCategories as $cat): ?>
                        <a href="<?= BASE_URL ?>/menu/<?= htmlspecialchars($cat['slug']) ?>" 
                           class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium" 
                           role="menuitem">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                    <?php foreach ($otherCategories as $cat): ?>
                        <a href="<?= BASE_URL ?>/menu/<?= htmlspecialchars($cat['slug']) ?>" 
                           class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium" 
                           role="menuitem">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/menu" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium" role="menuitem">All Menu</a>
                <a href="<?= BASE_URL ?>/cart" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium flex items-center justify-between" role="menuitem">
                    <span>Cart</span>
                    <span id="cart-count-badge-mobile" class="bg-brand text-white rounded-full px-2 py-0.5 text-xs font-semibold min-w-[20px] text-center <?= (!isset($_SESSION['cart_count']) || $_SESSION['cart_count'] == 0) ? 'hidden' : '' ?>"><?= $_SESSION['cart_count'] ?? 0 ?></span>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= BASE_URL ?>/account" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium" role="menuitem">
                        <i class="fas fa-user-circle mr-2"></i> Account
                    </a>
                    <a href="<?= BASE_URL ?>/account/orders" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium" role="menuitem">
                        <i class="fas fa-shopping-bag mr-2"></i> Orders
                    </a>
                    <?php if (in_array($_SESSION['user_role'], ['super_admin', 'admin', 'data_entry'])): ?>
                        <a href="<?= BASE_URL ?>/admin" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium" role="menuitem">
                            <i class="fas fa-cog mr-2"></i> Admin
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/logout" class="block px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors font-medium" role="menuitem">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium" role="menuitem">Login</a>
                    <a href="<?= BASE_URL ?>/register" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium" role="menuitem">Sign Up</a>
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
                    <h3 class="text-xl font-bold mb-4">The Pembina Pint & Restaurant</h3>
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
                        <li><a href="<?= BASE_URL ?>/track-order" class="text-gray-400 hover:text-white transition">Track Your Order</a></li>
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
    
    <!-- WhatsApp Chatbot Widget -->
    <?php
    // Check if WhatsApp is enabled
    $whatsappEnabled = \App\Core\Helper::getSetting('whatsapp_enabled', '1') === '1';
    
    if ($whatsappEnabled):
        // Get WhatsApp number from settings
        $whatsappNumber = \App\Core\Helper::getSetting('whatsapp_number', '');
        $whatsappMessage = \App\Core\Helper::getSetting('whatsapp_message', 'Hello! I need help with my order.');
        
        // Only show widget if number is configured
        if (!empty($whatsappNumber)):
            // Remove any non-numeric characters for WhatsApp link
            $whatsappNumber = preg_replace('/[^0-9]/', '', $whatsappNumber);
            // Add country code if not present (assuming Canada +1)
            if (substr($whatsappNumber, 0, 1) !== '1' && strlen($whatsappNumber) == 10) {
                $whatsappNumber = '1' . $whatsappNumber;
            }
            $whatsappMessage = urlencode($whatsappMessage);
            $whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}";
    ?>
    <style>
        .whatsapp-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            animation: pulse 2s infinite;
        }
        .whatsapp-button {
            width: 60px;
            height: 60px;
            background-color: #25D366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
        }
        .whatsapp-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.6);
        }
        .whatsapp-button i {
            color: white;
            font-size: 32px;
        }
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(37, 211, 102, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
            }
        }
        @media (max-width: 768px) {
            .whatsapp-widget {
                bottom: 15px;
                right: 15px;
            }
            .whatsapp-button {
                width: 56px;
                height: 56px;
            }
            .whatsapp-button i {
                font-size: 28px;
            }
        }
    </style>
    <div class="whatsapp-widget">
        <a href="<?= $whatsappUrl ?>" target="_blank" rel="noopener noreferrer" class="whatsapp-button" aria-label="Chat with us on WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
    <?php
        endif; // End if whatsappNumber is not empty
    endif; // End if whatsappEnabled
    ?>
</body>
</html>

