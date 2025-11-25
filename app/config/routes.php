<?php
/**
 * Application Routes
 */

use App\Core\Router;

$router = $router ?? ($GLOBALS['router'] ?? null);

if (!$router instanceof App\Core\Router) {
    throw new Exception('Router instance not available');
}

// Public routes
$router->add('GET', '', ['controller' => 'Home', 'action' => 'index']);
$router->add('GET', 'home', ['controller' => 'Home', 'action' => 'index']);
$router->add('GET', 'menu', ['controller' => 'Menu', 'action' => 'index']);
$router->add('GET', 'menu/{slug}', ['controller' => 'Menu', 'action' => 'view']);
$router->add('GET', 'product/{slug}', ['controller' => 'Product', 'action' => 'view']);
$router->add('GET', 'page/{slug}', ['controller' => 'Page', 'action' => 'view']);
$router->add('POST', 'newsletter/subscribe', ['controller' => 'Newsletter', 'action' => 'subscribe']);

// Authentication routes
$router->add('GET', 'login', ['controller' => 'Auth', 'action' => 'login']);
$router->add('POST', 'login', ['controller' => 'Auth', 'action' => 'login']);
$router->add('GET', 'register', ['controller' => 'Auth', 'action' => 'register']);
$router->add('POST', 'register', ['controller' => 'Auth', 'action' => 'register']);
$router->add('GET', 'logout', ['controller' => 'Auth', 'action' => 'logout']);
$router->add('GET', 'forgot-password', ['controller' => 'Auth', 'action' => 'forgotPassword']);
$router->add('POST', 'forgot-password', ['controller' => 'Auth', 'action' => 'forgotPassword']);
$router->add('GET', 'reset-password/{token}', ['controller' => 'Auth', 'action' => 'resetPassword']);
$router->add('POST', 'reset-password', ['controller' => 'Auth', 'action' => 'resetPassword']);

// Cart routes
$router->add('GET', 'cart', ['controller' => 'Cart', 'action' => 'index']);
$router->add('POST', 'cart/add', ['controller' => 'Cart', 'action' => 'add']);
$router->add('POST', 'cart/update', ['controller' => 'Cart', 'action' => 'update']);
$router->add('POST', 'cart/remove', ['controller' => 'Cart', 'action' => 'remove']);
$router->add('POST', 'cart/clear', ['controller' => 'Cart', 'action' => 'clear']);

// Checkout routes
$router->add('GET', 'checkout', ['controller' => 'Checkout', 'action' => 'index']);
$router->add('POST', 'checkout', ['controller' => 'Checkout', 'action' => 'process']);
$router->add('POST', 'checkout/calculate-tax', ['controller' => 'Checkout', 'action' => 'calculateTax']);

// Payment routes
$router->add('GET', 'payment', ['controller' => 'Payment', 'action' => 'index']);
$router->add('POST', 'payment/process', ['controller' => 'Payment', 'action' => 'process']);
$router->add('POST', 'payment/square/webhook', ['controller' => 'Payment', 'action' => 'squareWebhook']);
$router->add('GET', 'payment/success', ['controller' => 'Payment', 'action' => 'success']);
$router->add('GET', 'payment/cancel', ['controller' => 'Payment', 'action' => 'cancel']);

// Customer account routes
$router->add('GET', 'account', ['controller' => 'Account', 'action' => 'index']);
$router->add('GET', 'account/orders', ['controller' => 'Account', 'action' => 'orders']);
$router->add('GET', 'account/orders/{id}', ['controller' => 'Account', 'action' => 'viewOrder']);
$router->add('GET', 'account/orders/{id}/receipt', ['controller' => 'Account', 'action' => 'receipt']);
$router->add('GET', 'account/addresses', ['controller' => 'Account', 'action' => 'addresses']);
$router->add('POST', 'account/addresses', ['controller' => 'Account', 'action' => 'saveAddress']);
$router->add('GET', 'account/profile', ['controller' => 'Account', 'action' => 'profile']);
$router->add('GET', 'unauthorized', ['controller' => 'Auth', 'action' => 'unauthorized']);
$router->add('POST', 'account/profile', ['controller' => 'Account', 'action' => 'updateProfile']);

// Admin routes
$router->add('GET', 'admin', ['controller' => 'Admin\Dashboard', 'action' => 'index']);
$router->add('GET', 'admin/dashboard', ['controller' => 'Admin\Dashboard', 'action' => 'index']);

// Admin - Products
$router->add('GET', 'admin/products', ['controller' => 'Admin\Product', 'action' => 'index']);
$router->add('GET', 'admin/products/create', ['controller' => 'Admin\Product', 'action' => 'create']);
$router->add('POST', 'admin/products', ['controller' => 'Admin\Product', 'action' => 'store']);
$router->add('GET', 'admin/products/{id}/edit', ['controller' => 'Admin\Product', 'action' => 'edit']);
$router->add('POST', 'admin/products/{id}', ['controller' => 'Admin\Product', 'action' => 'update']);
$router->add('POST', 'admin/products/{id}/delete', ['controller' => 'Admin\Product', 'action' => 'delete']);
$router->add('POST', 'admin/products/import', ['controller' => 'Admin\Product', 'action' => 'import']);

// Admin - Categories
$router->add('GET', 'admin/categories', ['controller' => 'Admin\Category', 'action' => 'index']);
$router->add('GET', 'admin/categories/create', ['controller' => 'Admin\Category', 'action' => 'create']);
$router->add('POST', 'admin/categories', ['controller' => 'Admin\Category', 'action' => 'store']);
$router->add('GET', 'admin/categories/{id}/edit', ['controller' => 'Admin\Category', 'action' => 'edit']);
$router->add('POST', 'admin/categories/{id}', ['controller' => 'Admin\Category', 'action' => 'update']);
$router->add('POST', 'admin/categories/{id}/delete', ['controller' => 'Admin\Category', 'action' => 'delete']);

// Admin - Navigation Management
$router->add('GET', 'admin/navigation', ['controller' => 'Admin\Navigation', 'action' => 'index']);
$router->add('POST', 'admin/navigation/update', ['controller' => 'Admin\Navigation', 'action' => 'updateSettings']);
$router->add('GET', 'admin/navigation/create', ['controller' => 'Admin\Navigation', 'action' => 'create']);
$router->add('POST', 'admin/navigation', ['controller' => 'Admin\Navigation', 'action' => 'store']);
$router->add('GET', 'admin/navigation/{id}/edit', ['controller' => 'Admin\Navigation', 'action' => 'edit']);
$router->add('POST', 'admin/navigation/{id}', ['controller' => 'Admin\Navigation', 'action' => 'update']);
$router->add('POST', 'admin/navigation/{id}/delete', ['controller' => 'Admin\Navigation', 'action' => 'delete']);
$router->add('POST', 'admin/navigation/update-order', ['controller' => 'Admin\Navigation', 'action' => 'updateOrder']);

// Admin - Orders
$router->add('GET', 'admin/orders', ['controller' => 'Admin\Order', 'action' => 'index']);
$router->add('GET', 'admin/orders/{id}', ['controller' => 'Admin\Order', 'action' => 'view']);
$router->add('POST', 'admin/orders/{id}/status', ['controller' => 'Admin\Order', 'action' => 'updateStatus']);
$router->add('GET', 'admin/orders/{id}/receipt', ['controller' => 'Admin\Order', 'action' => 'receipt']);
$router->add('POST', 'admin/orders/{id}/email-receipt', ['controller' => 'Admin\Order', 'action' => 'emailReceipt']);

// Admin - Users
$router->add('GET', 'admin/users', ['controller' => 'Admin\User', 'action' => 'index']);
$router->add('GET', 'admin/users/create', ['controller' => 'Admin\User', 'action' => 'create']);
$router->add('POST', 'admin/users', ['controller' => 'Admin\User', 'action' => 'store']);
$router->add('GET', 'admin/users/{id}/edit', ['controller' => 'Admin\User', 'action' => 'edit']);
$router->add('POST', 'admin/users/{id}', ['controller' => 'Admin\User', 'action' => 'update']);
$router->add('POST', 'admin/users/{id}/delete', ['controller' => 'Admin\User', 'action' => 'delete']);
$router->add('GET', 'admin/roles', ['controller' => 'Admin\Role', 'action' => 'index']);
$router->add('GET', 'admin/roles/create', ['controller' => 'Admin\Role', 'action' => 'create']);
$router->add('POST', 'admin/roles', ['controller' => 'Admin\Role', 'action' => 'store']);
$router->add('GET', 'admin/roles/{id}/edit', ['controller' => 'Admin\Role', 'action' => 'edit']);
$router->add('POST', 'admin/roles/{id}', ['controller' => 'Admin\Role', 'action' => 'update']);
$router->add('POST', 'admin/roles/{id}/delete', ['controller' => 'Admin\Role', 'action' => 'delete']);
$router->add('GET', 'admin/permissions', ['controller' => 'Admin\Permission', 'action' => 'index']);
$router->add('GET', 'admin/permissions/create', ['controller' => 'Admin\Permission', 'action' => 'create']);
$router->add('POST', 'admin/permissions', ['controller' => 'Admin\Permission', 'action' => 'store']);
$router->add('GET', 'admin/permissions/{id}/edit', ['controller' => 'Admin\Permission', 'action' => 'edit']);
$router->add('POST', 'admin/permissions/{id}', ['controller' => 'Admin\Permission', 'action' => 'update']);
$router->add('POST', 'admin/permissions/{id}/delete', ['controller' => 'Admin\Permission', 'action' => 'delete']);

// Admin - Pages
$router->add('GET', 'admin/pages', ['controller' => 'Admin\Page', 'action' => 'index']);
$router->add('GET', 'admin/pages/create', ['controller' => 'Admin\Page', 'action' => 'create']);
$router->add('POST', 'admin/pages', ['controller' => 'Admin\Page', 'action' => 'store']);
$router->add('GET', 'admin/pages/{id}/edit', ['controller' => 'Admin\Page', 'action' => 'edit']);
$router->add('POST', 'admin/pages/{id}', ['controller' => 'Admin\Page', 'action' => 'update']);
$router->add('POST', 'admin/pages/{id}/delete', ['controller' => 'Admin\Page', 'action' => 'delete']);

// Admin - Settings
$router->add('GET', 'admin/settings', ['controller' => 'Admin\Setting', 'action' => 'index']);
$router->add('POST', 'admin/settings', ['controller' => 'Admin\Setting', 'action' => 'update']);
$router->add('GET', 'admin/settings/payment', ['controller' => 'Admin\Setting', 'action' => 'payment']);
$router->add('POST', 'admin/settings/payment', ['controller' => 'Admin\Setting', 'action' => 'updatePayment']);
$router->add('GET', 'admin/settings/tax', ['controller' => 'Admin\Setting', 'action' => 'tax']);
$router->add('POST', 'admin/settings/tax', ['controller' => 'Admin\Setting', 'action' => 'updateTax']);
$router->add('GET', 'admin/settings/email', ['controller' => 'Admin\Setting', 'action' => 'email']);
$router->add('POST', 'admin/settings/email', ['controller' => 'Admin\Setting', 'action' => 'updateEmail']);

// Admin - Hero Slides
$router->add('GET', 'admin/hero-slides', ['controller' => 'Admin\HeroSlide', 'action' => 'index']);
$router->add('GET', 'admin/hero-slides/create', ['controller' => 'Admin\HeroSlide', 'action' => 'create']);
$router->add('POST', 'admin/hero-slides', ['controller' => 'Admin\HeroSlide', 'action' => 'store']);
$router->add('GET', 'admin/hero-slides/{id}/edit', ['controller' => 'Admin\HeroSlide', 'action' => 'edit']);
$router->add('POST', 'admin/hero-slides/{id}', ['controller' => 'Admin\HeroSlide', 'action' => 'update']);
$router->add('POST', 'admin/hero-slides/{id}/delete', ['controller' => 'Admin\HeroSlide', 'action' => 'delete']);

// Admin - Testimonials
$router->add('GET', 'admin/testimonials', ['controller' => 'Admin\Testimonial', 'action' => 'index']);
$router->add('GET', 'admin/testimonials/create', ['controller' => 'Admin\Testimonial', 'action' => 'create']);
$router->add('POST', 'admin/testimonials', ['controller' => 'Admin\Testimonial', 'action' => 'store']);
$router->add('GET', 'admin/testimonials/{id}/edit', ['controller' => 'Admin\Testimonial', 'action' => 'edit']);
$router->add('POST', 'admin/testimonials/{id}', ['controller' => 'Admin\Testimonial', 'action' => 'update']);
$router->add('POST', 'admin/testimonials/{id}/delete', ['controller' => 'Admin\Testimonial', 'action' => 'delete']);

// Admin - Events
$router->add('GET', 'admin/events', ['controller' => 'Admin\Event', 'action' => 'index']);
$router->add('GET', 'admin/events/create', ['controller' => 'Admin\Event', 'action' => 'create']);
$router->add('POST', 'admin/events', ['controller' => 'Admin\Event', 'action' => 'store']);
$router->add('GET', 'admin/events/{id}/edit', ['controller' => 'Admin\Event', 'action' => 'edit']);
$router->add('POST', 'admin/events/{id}', ['controller' => 'Admin\Event', 'action' => 'update']);
$router->add('POST', 'admin/events/{id}/delete', ['controller' => 'Admin\Event', 'action' => 'delete']);

// Admin - Newsletter
$router->add('GET', 'admin/newsletter', ['controller' => 'Admin\Newsletter', 'action' => 'index']);

// Admin - Testimonials
$router->add('GET', 'admin/testimonials', ['controller' => 'Admin\Testimonial', 'action' => 'index']);
$router->add('GET', 'admin/testimonials/create', ['controller' => 'Admin\Testimonial', 'action' => 'create']);
$router->add('POST', 'admin/testimonials', ['controller' => 'Admin\Testimonial', 'action' => 'store']);
$router->add('GET', 'admin/testimonials/{id}/edit', ['controller' => 'Admin\Testimonial', 'action' => 'edit']);
$router->add('POST', 'admin/testimonials/{id}', ['controller' => 'Admin\Testimonial', 'action' => 'update']);
$router->add('POST', 'admin/testimonials/{id}/delete', ['controller' => 'Admin\Testimonial', 'action' => 'delete']);

// Admin - Events
$router->add('GET', 'admin/events', ['controller' => 'Admin\Event', 'action' => 'index']);
$router->add('GET', 'admin/events/create', ['controller' => 'Admin\Event', 'action' => 'create']);
$router->add('POST', 'admin/events', ['controller' => 'Admin\Event', 'action' => 'store']);
$router->add('GET', 'admin/events/{id}/edit', ['controller' => 'Admin\Event', 'action' => 'edit']);
$router->add('POST', 'admin/events/{id}', ['controller' => 'Admin\Event', 'action' => 'update']);
$router->add('POST', 'admin/events/{id}/delete', ['controller' => 'Admin\Event', 'action' => 'delete']);

// Admin - Newsletter Subscribers
$router->add('GET', 'admin/newsletter', ['controller' => 'Admin\Newsletter', 'action' => 'index']);

// API routes
$router->add('GET', 'api/products', ['controller' => 'Api\Product', 'action' => 'index']);
$router->add('GET', 'api/products/{id}', ['controller' => 'Api\Product', 'action' => 'view']);

// Newsletter subscribe
$router->add('POST', 'newsletter/subscribe', ['controller' => 'Newsletter', 'action' => 'subscribe']);

