<?php
/**
 * The Pembina Pint and Restaurant
 * E-Commerce Application Entry Point
 * 
 * @package PembinaPint
 * @author Webspace (https://www.webspace.ng)
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // disable debug output
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

// Define constants
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads');
// Base URL - detect subdirectory automatically
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'thepembina.ca';

// Get the subdirectory path
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$scriptDir = dirname($scriptName);

// If index.php is in root, use script directory
// If in public folder, go up one level
if (strpos($scriptName, '/public/') !== false) {
    $basePath = str_replace('/public', '', $scriptDir);
} else {
    $basePath = $scriptDir;
}

// Clean up the path
$basePath = str_replace('\\', '/', $basePath);
$basePath = rtrim($basePath, '/');

// Set BASE_URL
define('BASE_URL', $protocol . '://' . $host . ($basePath !== '/' ? $basePath : ''));

// Autoloader
require_once APP_PATH . '/core/Autoloader.php';
\App\Core\Autoloader::register();

// Load configuration
require_once APP_PATH . '/config/config.php';

// Start session (after session configuration)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize application
$app = new App\Core\Application();
$app->run();

