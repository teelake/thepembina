<?php
/**
 * Application Configuration
 */

// Timezone
date_default_timezone_set('America/Winnipeg');

// Application settings
define('APP_NAME', 'The Pembina Pint and Restaurant');
define('APP_VERSION', '1.0.0');

if (!defined('APP_ENV')) {
    define('APP_ENV', 'production');
}

if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}
error_reporting(E_ALL);

// Database configuration
require_once __DIR__ . '/database.php';

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Enabled for HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour

// Upload settings
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_FILE_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Rate limiting
define('RATE_LIMIT_REQUESTS', 100); // requests per window
define('RATE_LIMIT_WINDOW', 3600); // 1 hour in seconds

// Email settings
// NOTE: These can be overridden by database settings (smtp_host, smtp_port, etc.)
// Default values - will be overridden by database settings in Email class if they exist
define('SMTP_HOST', 'mail.thepembina.ca');
define('SMTP_PORT', 587);
define('SMTP_USER', 'no-reply@thepembina.ca');
define('SMTP_PASS', 'Temp_Pass123@'); // Default password - can be changed via admin email settings
define('SMTP_FROM_EMAIL', 'no-reply@thepembina.ca');
define('SMTP_FROM_NAME', 'The Pembina Pint and Restaurant');

// Brand colors (from logo)
define('BRAND_PRIMARY', '#F4A460'); // Warm yellow-orange/mustard
define('BRAND_SECONDARY', '#8B4513'); // Darker brown for contrast

// Business information
define('BUSINESS_NAME', 'The Pembina Pint and Restaurant');
define('BUSINESS_ADDRESS', '282 Loren Drive, Morden, Manitoba, Canada');
define('BUSINESS_PHONE', '');
define('BUSINESS_EMAIL', 'no-reply@thepembina.ca');
define('BUSINESS_CATEGORY', 'African & Nigerian Restaurant | Bar | Catering Services');

