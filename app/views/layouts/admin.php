<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>Admin - The Pembina Pint</title>
    
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/images/logo.png">
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="bg-gray-100">
    <!-- Admin Navigation -->
    <nav class="bg-white shadow-md border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="<?= BASE_URL ?>/admin" class="flex items-center">
                        <i class="fas fa-utensils text-2xl text-brand mr-3"></i>
                        <span class="text-xl font-bold text-gray-900">Admin Panel</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="<?= BASE_URL ?>" target="_blank" class="text-gray-600 hover:text-brand transition">
                        <i class="fas fa-external-link-alt mr-1"></i> View Site
                    </a>
                    <span class="text-gray-400">|</span>
                    <span class="text-gray-600"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
                    <a href="<?= BASE_URL ?>/logout" class="text-red-600 hover:text-red-700 transition">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile sidebar overlay -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    
    <div class="flex relative">
        <!-- Mobile menu button -->
        <button class="md:hidden fixed top-4 left-4 z-50 bg-white p-2 rounded-lg shadow-md" id="mobile-sidebar-toggle" aria-label="Toggle sidebar">
            <i class="fas fa-bars text-xl text-gray-700"></i>
        </button>
        
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-gray-50 to-white shadow-xl fixed left-0 top-0 h-screen overflow-y-auto z-40 md:block hidden admin-sidebar" id="admin-sidebar">
            <!-- Sidebar Header -->
            <div class="p-6 border-b border-gray-200 bg-white">
                <div class="flex items-center">
                    <img src="<?= BASE_URL ?>/public/images/logo.png" alt="Logo" class="h-10 w-10 rounded-lg mr-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Admin Panel</h2>
                        <p class="text-xs text-gray-500">The Pembina Pint</p>
                    </div>
                </div>
            </div>
            
            <nav class="p-4">
                <ul class="space-y-1">
                    <!-- Dashboard -->
                    <li>
                        <a href="<?= BASE_URL ?>/admin" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'dashboard') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-tachometer-alt w-5 mr-3 text-center"></i>
                            <span class="font-medium">Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Products Section -->
                    <li class="pt-6">
                        <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Products</p>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/products" 
                           class="flex items-center px-4 py-2.5 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'products') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-box w-5 mr-3 text-center"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/categories" 
                           class="flex items-center px-4 py-2.5 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'categories') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-folder w-5 mr-3 text-center"></i>
                            <span>Categories</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/navigation" 
                           class="flex items-center px-4 py-2.5 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'navigation') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-bars w-5 mr-3 text-center"></i>
                            <span>Navigation</span>
                        </a>
                    </li>
                    
                    <!-- Orders Section -->
                    <li class="pt-6">
                        <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Orders</p>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/orders" 
                           class="flex items-center px-4 py-2.5 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'orders') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-shopping-cart w-5 mr-3 text-center"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/transactions" 
                           class="flex items-center px-4 py-2.5 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'transactions') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-receipt w-5 mr-3 text-center"></i>
                            <span>Transactions</span>
                        </a>
                    </li>
                    
                    <?php if (in_array($_SESSION['user_role'] ?? '', ['super_admin', 'admin'])): ?>
                    <!-- Content Management Section -->
                    <li class="pt-6">
                        <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Content</p>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/pages" 
                           class="flex items-center px-4 py-2.5 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'pages') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-file-alt w-5 mr-3 text-center"></i>
                            <span>Pages</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/hero-slides" 
                           class="flex items-center px-4 py-2.5 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'hero_slides') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-images w-5 mr-3 text-center"></i>
                            <span>Hero Slider</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/testimonials" 
                           class="flex items-center px-4 py-2.5 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'testimonials') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-comment-dots w-5 mr-3 text-center"></i>
                            <span>Testimonials</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/events" 
                           class="flex items-center px-4 py-2.5 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'events') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-calendar-alt w-5 mr-3 text-center"></i>
                            <span>Events</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/newsletter" 
                           class="flex items-center px-4 py-2.5 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'newsletter') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-envelope-open-text w-5 mr-3 text-center"></i>
                            <span>Newsletter</span>
                        </a>
                    </li>
                    
                    <!-- System Management Section -->
                    <li class="pt-6">
                        <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">System</p>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/users" 
                           class="flex items-center px-4 py-2.5 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'users') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-users w-5 mr-3 text-center"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/roles" 
                           class="flex items-center px-4 py-2.5 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'roles') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-user-shield w-5 mr-3 text-center"></i>
                            <span>Roles</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/permissions" 
                           class="flex items-center px-4 py-2.5 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 <?= (isset($current_page) && $current_page === 'permissions') ? 'bg-brand text-white shadow-md' : 'text-gray-700 hover:shadow-sm' ?>">
                            <i class="fas fa-key w-5 mr-3 text-center"></i>
                            <span>Permissions</span>
                        </a>
                    </li>
                    
                    <!-- Settings Section (Collapsible) -->
                    <?php 
                    $isSettingsPage = in_array($current_page ?? '', ['settings', 'payment', 'email', 'whatsapp', 'tax']);
                    ?>
                    <li class="pt-6">
                        <button onclick="toggleSettingsMenu()" class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg hover:bg-gray-100 transition-all duration-200 text-gray-700">
                            <div class="flex items-center">
                                <i class="fas fa-cog w-5 mr-3 text-center"></i>
                                <span class="font-medium">Settings</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200 <?= $isSettingsPage ? 'rotate-180' : '' ?>" id="settings-chevron"></i>
                        </button>
                        <ul id="settings-submenu" class="mt-1 space-y-1 <?= $isSettingsPage ? '' : 'hidden' ?>">
                            <li>
                                <a href="<?= BASE_URL ?>/admin/settings" 
                                   class="flex items-center px-4 py-2 pl-12 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 text-sm <?= (isset($current_page) && $current_page === 'settings') ? 'bg-brand text-white shadow-md' : 'text-gray-600 hover:shadow-sm' ?>">
                                    <i class="fas fa-cog w-4 mr-2 text-center"></i>
                                    <span>General</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>/admin/settings/payment" 
                                   class="flex items-center px-4 py-2 pl-12 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 text-sm <?= (isset($current_page) && $current_page === 'payment') ? 'bg-brand text-white shadow-md' : 'text-gray-600 hover:shadow-sm' ?>">
                                    <i class="fas fa-credit-card w-4 mr-2 text-center"></i>
                                    <span>Payment</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>/admin/settings/email" 
                                   class="flex items-center px-4 py-2 pl-12 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 text-sm <?= (isset($current_page) && $current_page === 'email') ? 'bg-brand text-white shadow-md' : 'text-gray-600 hover:shadow-sm' ?>">
                                    <i class="fas fa-envelope w-4 mr-2 text-center"></i>
                                    <span>Email</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>/admin/settings/tax" 
                                   class="flex items-center px-4 py-2 pl-12 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 text-sm <?= (isset($current_page) && $current_page === 'tax') ? 'bg-brand text-white shadow-md' : 'text-gray-600 hover:shadow-sm' ?>">
                                    <i class="fas fa-percent w-4 mr-2 text-center"></i>
                                    <span>Tax</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>/admin/settings/whatsapp" 
                                   class="flex items-center px-4 py-2 pl-12 rounded-lg hover:bg-brand hover:text-white transition-all duration-200 text-sm <?= (isset($current_page) && $current_page === 'whatsapp') ? 'bg-brand text-white shadow-md' : 'text-gray-600 hover:shadow-sm' ?>">
                                    <i class="fab fa-whatsapp w-4 mr-2 text-center"></i>
                                    <span>WhatsApp</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-8 mt-16 md:mt-0 md:ml-64">
            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <?php echo $content ?? ''; ?>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // Make BASE_URL available to JavaScript
        var BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= BASE_URL ?>/public/js/admin.js"></script>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

