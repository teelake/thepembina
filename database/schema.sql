-- The Pembina Pint and Restaurant - Database Schema
-- Created for E-Commerce Platform

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Database: pembina_pint
-- --------------------------------------------------------

-- --------------------------------------------------------
-- Table structure for table `roles`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles
INSERT INTO `roles` (`name`, `slug`, `description`) VALUES
('Super Admin', 'super_admin', 'Full system access'),
('Admin', 'admin', 'Administrative access'),
('Data Entry Officer', 'data_entry', 'Can manage products and content'),
('Customer', 'customer', 'Regular customer access');

-- --------------------------------------------------------
-- Table structure for table `permissions`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default permissions
INSERT INTO `permissions` (`name`, `slug`, `description`) VALUES
('Manage Users', 'manage_users', 'Create, edit, and delete users'),
('Manage Roles', 'manage_roles', 'Manage roles and permissions'),
('Manage Products', 'manage_products', 'Create, edit, and delete products'),
('Manage Categories', 'manage_categories', 'Manage product categories'),
('Manage Orders', 'manage_orders', 'View and manage orders'),
('Manage Payments', 'manage_payments', 'Manage payment settings'),
('Manage Content', 'manage_content', 'Edit website content'),
('Manage Settings', 'manage_settings', 'Manage system settings'),
('View Reports', 'view_reports', 'Access reports and analytics'),
('Manage Tax', 'manage_tax', 'Configure tax settings');

-- --------------------------------------------------------
-- Table structure for table `role_permissions`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permission` (`role_id`, `permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 4,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verification_token` varchar(100) DEFAULT NULL,
  `password_reset_token` varchar(100) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `addresses`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('billing','shipping') NOT NULL DEFAULT 'shipping',
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(50) NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `country` varchar(50) NOT NULL DEFAULT 'Canada',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `short_description` text,
  `sku` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `compare_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT NULL,
  `stock_status` enum('in_stock','out_of_stock','on_backorder') NOT NULL DEFAULT 'in_stock',
  `manage_stock` tinyint(1) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','draft') NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `product_options`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('select','radio','checkbox') NOT NULL DEFAULT 'select',
  `required` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_options_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `product_option_values`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_option_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  `price_modifier` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `option_id` (`option_id`),
  CONSTRAINT `product_option_values_ibfk_1` FOREIGN KEY (`option_id`) REFERENCES `product_options` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `cart`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `options` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `session_id` (`session_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `orders`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `order_type` enum('pickup','delivery') NOT NULL,
  `status` enum('pending','processing','confirmed','preparing','ready','out_for_delivery','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'CAD',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_gateway` varchar(50) DEFAULT NULL,
  `payment_transaction_id` varchar(255) DEFAULT NULL,
  `billing_address` text NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `delivery_instructions` text,
  `pickup_time` datetime DEFAULT NULL,
  `delivery_time` datetime DEFAULT NULL,
  `doordash_order_id` varchar(255) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `order_items`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `options` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payments`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `gateway` varchar(50) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'CAD',
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `gateway_response` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `transaction_id` (`transaction_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `tax_rates`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tax_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `province` varchar(50) NOT NULL,
  `province_code` varchar(10) NOT NULL,
  `gst_rate` decimal(5,4) NOT NULL DEFAULT 0.0500,
  `pst_rate` decimal(5,4) NOT NULL DEFAULT 0.0000,
  `hst_rate` decimal(5,4) NOT NULL DEFAULT 0.0000,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `province_code` (`province_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Canadian tax rates
INSERT INTO `tax_rates` (`province`, `province_code`, `gst_rate`, `pst_rate`, `hst_rate`) VALUES
('Alberta', 'AB', 0.0500, 0.0000, 0.0000),
('British Columbia', 'BC', 0.0500, 0.0700, 0.0000),
('Manitoba', 'MB', 0.0500, 0.0700, 0.0000),
('New Brunswick', 'NB', 0.0000, 0.0000, 0.1500),
('Newfoundland and Labrador', 'NL', 0.0000, 0.0000, 0.1500),
('Northwest Territories', 'NT', 0.0500, 0.0000, 0.0000),
('Nova Scotia', 'NS', 0.0000, 0.0000, 0.1500),
('Nunavut', 'NU', 0.0500, 0.0000, 0.0000),
('Ontario', 'ON', 0.0000, 0.0000, 0.1300),
('Prince Edward Island', 'PE', 0.0000, 0.0000, 0.1500),
('Quebec', 'QC', 0.0500, 0.09975, 0.0000),
('Saskatchewan', 'SK', 0.0500, 0.0600, 0.0000),
('Yukon', 'YT', 0.0500, 0.0000, 0.0000);

-- --------------------------------------------------------
-- Table structure for table `pages`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text,
  `status` enum('published','draft') NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default pages
INSERT INTO `pages` (`title`, `slug`, `content`, `status`) VALUES
('Terms of Service', 'terms-of-service', '<h2>Dining & Ordering</h2><p>All table reservations, takeaway, delivery, and catering orders are confirmed via email or SMS. Prices may change without notice to reflect seasonal ingredients and market conditions.</p><h2>Payments</h2><p>We accept major credit cards, Interac, and Square secure payments. A 50% deposit is required for catering and private events.</p><h2>Conduct</h2><p>We reserve the right to refuse service to anyone engaging in abusive behaviour towards guests or team members. Outside food or alcohol is not permitted.</p>', 'published'),
('Privacy Policy', 'privacy-policy', '<p>Your privacy matters to us. We collect only the information required to process orders, reservations, and newsletter opt-ins. Data is stored on encrypted servers and never sold to third parties. You may request deletion of your data at any time by emailing <a href="mailto:info@thepembina.ca">info@thepembina.ca</a>.</p>', 'published'),
('Refund and Delivery Policy', 'refund-delivery-policy', '<h2>Pickup & Delivery</h2><p>DoorDash handles national delivery. Once a driver picks up your order, delivery timelines are subject to DoorDash networks. Local pickup orders are prepared within 25–35 minutes.</p><h2>Refunds</h2><p>If your meal arrives incorrect or damaged, contact us within 24 hours. Approved refunds are processed within 3–5 business days back to the original payment method.</p>', 'published');

-- --------------------------------------------------------
-- Table structure for table `settings`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text,
  `type` varchar(50) DEFAULT 'text',
  `group` varchar(50) DEFAULT 'general',
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`key`, `value`, `type`, `group`, `description`) VALUES
('site_name', 'The Pembina Pint and Restaurant', 'text', 'general', 'Site name'),
('site_email', '', 'email', 'general', 'Site email address'),
('site_phone', '', 'text', 'general', 'Site phone number'),
('site_address', '282 Loren Drive, Morden, Manitoba, Canada', 'text', 'general', 'Business address'),
('currency', 'CAD', 'text', 'general', 'Default currency'),
('payment_square_enabled', '0', 'boolean', 'payment', 'Enable Square payment gateway'),
('payment_square_app_id', '', 'text', 'payment', 'Square Application ID'),
('payment_square_access_token', '', 'text', 'payment', 'Square Access Token'),
('payment_square_location_id', '', 'text', 'payment', 'Square Location ID'),
('payment_square_sandbox', '1', 'boolean', 'payment', 'Use Square sandbox mode'),
('delivery_enabled', '1', 'boolean', 'delivery', 'Enable delivery orders'),
('pickup_enabled', '1', 'boolean', 'delivery', 'Enable pickup orders'),
('doordash_enabled', '1', 'boolean', 'delivery', 'Enable DoorDash integration'),
('min_order_amount', '0.00', 'decimal', 'orders', 'Minimum order amount'),
('delivery_fee', '0.00', 'decimal', 'delivery', 'Default delivery fee');

-- --------------------------------------------------------
-- Table structure for table `activity_logs`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `model` varchar(50) DEFAULT NULL,
  `model_id` int(11) DEFAULT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `request_uri` varchar(500) DEFAULT NULL,
  `request_method` varchar(10) DEFAULT NULL,
  `additional_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  KEY `action` (`action`),
  KEY `model` (`model`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `hero_slides`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `hero_slides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('published','draft') NOT NULL DEFAULT 'published',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default hero slide
INSERT INTO `hero_slides` (`title`, `subtitle`, `description`, `button_text`, `button_link`, `image`, `status`, `sort_order`)
VALUES (
  'Authentic African & Nigerian Cuisine',
  'Experience vibrant flavors in Manitoba',
  'From Jollof Rice to Suya, enjoy freshly prepared dishes that celebrate African heritage.',
  'Explore Menu',
  '/menu',
  'images/hero/default-slide.jpg',
  'published',
  0
);

-- --------------------------------------------------------
-- Table structure for table `testimonials`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `rating` tinyint(1) DEFAULT 5,
  `status` enum('published','draft') NOT NULL DEFAULT 'published',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `testimonials` (`name`, `title`, `message`, `rating`, `status`, `sort_order`)
VALUES
('Chinedu A.', 'Winnipeg', 'The Pembina Pint instantly reminds me of home. Their Jollof tastes exactly like what my mom makes!', 5, 'published', 0),
('Sarah L.', 'Morden', 'We hosted our corporate mixer here—amazing cocktails, friendly staff, and incredible vibes.', 5, 'published', 1);

-- --------------------------------------------------------
-- Table structure for table `events`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('upcoming','completed','draft') NOT NULL DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `events` (`title`, `subtitle`, `description`, `event_date`, `event_time`, `location`, `image`, `status`)
VALUES
('Afrobeat Fridays', 'Live DJ + Cultural Cocktails', 'Join us every Friday evening for live Afrobeat sessions, signature cocktails, and chef specials.', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '20:00:00', 'The Pembina Pint Lounge', 'images/hero/default-slide.jpg', 'upcoming'),
('Taste of Nigeria Brunch', 'Featuring Suya & Palmwine', 'A curated brunch celebrating Nigerian street classics and sweet palmwine pairings.', DATE_ADD(CURDATE(), INTERVAL 21 DAY), '11:00:00', 'The Pembina Pint Main Hall', 'images/hero/default-slide.jpg', 'upcoming');

-- --------------------------------------------------------
-- Table structure for table `newsletter_subscribers`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `status` enum('active','unsubscribed') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

