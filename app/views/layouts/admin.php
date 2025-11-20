<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>Admin - The Pembina Pint</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
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

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg min-h-screen">
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="<?= BASE_URL ?>/admin" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-brand hover:text-white transition <?= (isset($current_page) && $current_page === 'dashboard') ? 'bg-brand text-white' : 'text-gray-700' ?>">
                            <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase">Products</p>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/products" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-brand hover:text-white transition <?= (isset($current_page) && $current_page === 'products') ? 'bg-brand text-white' : 'text-gray-700' ?>">
                            <i class="fas fa-box w-5 mr-3"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/categories" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-brand hover:text-white transition <?= (isset($current_page) && $current_page === 'categories') ? 'bg-brand text-white' : 'text-gray-700' ?>">
                            <i class="fas fa-folder w-5 mr-3"></i>
                            <span>Categories</span>
                        </a>
                    </li>
                    
                    <li class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase">Orders</p>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/orders" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-brand hover:text-white transition <?= (isset($current_page) && $current_page === 'orders') ? 'bg-brand text-white' : 'text-gray-700' ?>">
                            <i class="fas fa-shopping-cart w-5 mr-3"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    
                    <?php if (in_array($_SESSION['user_role'] ?? '', ['super_admin', 'admin'])): ?>
                    <li class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase">Management</p>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/users" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-brand hover:text-white transition <?= (isset($current_page) && $current_page === 'users') ? 'bg-brand text-white' : 'text-gray-700' ?>">
                            <i class="fas fa-users w-5 mr-3"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/pages" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-brand hover:text-white transition <?= (isset($current_page) && $current_page === 'pages') ? 'bg-brand text-white' : 'text-gray-700' ?>">
                            <i class="fas fa-file-alt w-5 mr-3"></i>
                            <span>Pages</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/hero-slides" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-brand hover:text-white transition <?= (isset($current_page) && $current_page === 'hero_slides') ? 'bg-brand text-white' : 'text-gray-700' ?>">
                            <i class="fas fa-images w-5 mr-3"></i>
                            <span>Hero Slider</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/testimonials" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-brand hover:text-white transition <?= (isset($current_page) && $current_page === 'testimonials') ? 'bg-brand text-white' : 'text-gray-700' ?>">
                            <i class="fas fa-comment-dots w-5 mr-3"></i>
                            <span>Testimonials</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/events" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-brand hover:text-white transition <?= (isset($current_page) && $current_page === 'events') ? 'bg-brand text-white' : 'text-gray-700' ?>">
                            <i class="fas fa-calendar-alt w-5 mr-3"></i>
                            <span>Events</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/newsletter" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-brand hover:text-white transition <?= (isset($current_page) && $current_page === 'newsletter') ? 'bg-brand text-white' : 'text-gray-700' ?>">
                            <i class="fas fa-envelope-open-text w-5 mr-3"></i>
                            <span>Newsletter</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/settings" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-brand hover:text-white transition <?= (isset($current_page) && $current_page === 'settings') ? 'bg-brand text-white' : 'text-gray-700' ?>">
                            <i class="fas fa-cog w-5 mr-3"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
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
    <script src="<?= BASE_URL ?>/public/js/admin.js"></script>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

