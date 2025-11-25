<?php
/**
 * The Pembina Pint and Restaurant
 * E-Commerce Application Entry Point
 * 
 * @package PembinaPint
 * @author Webspace (https://www.webspace.ng)
 */

// Detect environment & configure error reporting
$env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? (function_exists('apache_getenv') ? apache_getenv('APP_ENV') : null)) ?: 'production';
define('APP_ENV', $env);

error_reporting(E_ALL);
ini_set('display_errors', APP_ENV === 'development' ? 1 : 0);
ini_set('display_startup_errors', APP_ENV === 'development' ? 1 : 0);
ini_set('log_errors', 1);

// Define constants
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads');

// Set error log file path
$errorLogFile = ROOT_PATH . '/php-error.log';
ini_set('error_log', $errorLogFile);

// Ensure error log file exists and is writable
if (!file_exists($errorLogFile)) {
    @touch($errorLogFile);
    @chmod($errorLogFile, 0664);
}

/**
 * Comprehensive Error Handler
 * Logs all PHP errors, warnings, notices to php-error.log
 */
set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext = null) use ($errorLogFile) {
    // Don't log if error reporting is disabled for this level
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE_ERROR',
        E_CORE_WARNING => 'CORE_WARNING',
        E_COMPILE_ERROR => 'COMPILE_ERROR',
        E_COMPILE_WARNING => 'COMPILE_WARNING',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED',
    ];
    
    $errorType = $errorTypes[$errno] ?? 'UNKNOWN';
    $timestamp = date('Y-m-d H:i:s');
    $url = $_SERVER['REQUEST_URI'] ?? 'CLI';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    
    $logMessage = sprintf(
        "[%s] PHP %s: %s in %s on line %d | URL: %s | Method: %s | IP: %s\n",
        $timestamp,
        $errorType,
        $errstr,
        $errfile,
        $errline,
        $url,
        $method,
        $ip
    );
    
    @file_put_contents($errorLogFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    // Return false to continue with PHP's normal error handling
    return false;
}, E_ALL);

/**
 * Exception Handler
 * Logs all uncaught exceptions to php-error.log
 */
set_exception_handler(function($exception) use ($errorLogFile) {
    $timestamp = date('Y-m-d H:i:s');
    $url = $_SERVER['REQUEST_URI'] ?? 'CLI';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    
    $logMessage = sprintf(
        "[%s] UNCAUGHT EXCEPTION: %s | Message: %s | File: %s | Line: %d | URL: %s | Method: %s | IP: %s\n",
        $timestamp,
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $url,
        $method,
        $ip
    );
    
    $logMessage .= sprintf("Stack Trace:\n%s\n", $exception->getTraceAsString());
    
    @file_put_contents($errorLogFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    // Show error page
    http_response_code(500);
    if (APP_ENV === 'development') {
        echo "<h1>Uncaught Exception</h1>";
        echo "<p><strong>" . get_class($exception) . "</strong>: " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . " on line " . $exception->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    } else {
        if (file_exists(APP_PATH . '/views/errors/500.php')) {
            require_once APP_PATH . '/views/errors/500.php';
        } else {
            echo "<h1>500 Internal Server Error</h1>";
            echo "<p>An error occurred. Please try again later.</p>";
        }
    }
    exit(1);
});

/**
 * Shutdown Handler
 * Catches fatal errors that occur after script execution
 */
register_shutdown_function(function() use ($errorLogFile) {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING], true)) {
        $timestamp = date('Y-m-d H:i:s');
        $url = $_SERVER['REQUEST_URI'] ?? 'CLI';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        
        $logMessage = sprintf(
            "[%s] FATAL ERROR: %s | Message: %s | File: %s | Line: %d | URL: %s | Method: %s | IP: %s\n",
            $timestamp,
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line'],
            $url,
            $method,
            $ip
        );
        
        @file_put_contents($errorLogFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
});
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
\App\Core\Logger::registerShutdownHandler();

// Load configuration
require_once APP_PATH . '/config/config.php';

// Start session (after session configuration)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize application
$app = new App\Core\Application();
$app->run();

