<?php
/**
 * Application Class
 * Main application controller
 */

namespace App\Core;

class Application
{
    private $router;

    public function __construct()
    {
        $this->router = new Router();
        $this->loadRoutes();
    }

    /**
     * Load application routes
     */
    private function loadRoutes()
    {
        $router = $this->router;
        require_once APP_PATH . '/config/routes.php';
    }

    /**
     * Run application
     */
    public function run()
    {
        try {
            $url = $_SERVER['REQUEST_URI'] ?? '/';
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
            
            // Get the base path (subdirectory)
            $scriptDir = dirname($scriptName);
            if (strpos($scriptName, '/public/') !== false) {
                $basePath = str_replace('/public', '', $scriptDir);
            } else {
                $basePath = $scriptDir;
            }
            $basePath = str_replace('\\', '/', $basePath);
            
            // Remove base path and query string from URL
            if ($basePath !== '/' && strpos($url, $basePath) === 0) {
                $url = substr($url, strlen($basePath));
            }
            
            // Remove query string
            if (($pos = strpos($url, '?')) !== false) {
                $url = substr($url, 0, $pos);
            }
            
            $url = trim($url, '/');
            
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            
            $this->router->dispatch($url, $method);
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Handle errors
     * 
     * @param \Exception $e
     */
    private function handleError(\Exception $e)
    {
        // Normalize exception code to a valid HTTP status code
        $rawCode = $e->getCode();
        if (is_int($rawCode)) {
            $code = ($rawCode >= 100 && $rawCode <= 599) ? $rawCode : 500;
        } elseif (is_numeric($rawCode)) {
            $intCode = (int)$rawCode;
            $code = ($intCode >= 100 && $intCode <= 599) ? $intCode : 500;
        } else {
            $code = 500;
        }
        http_response_code($code);

        Logger::error('Application exception', [
            'code' => $code,
            'message' => $e->getMessage(),
            'url' => $_SERVER['REQUEST_URI'] ?? null,
            'trace' => $e->getTraceAsString(),
        ]);
        
        // Also log to php-error.log directly
        $errorLogFile = ROOT_PATH . '/php-error.log';
        $timestamp = date('Y-m-d H:i:s');
        $url = $_SERVER['REQUEST_URI'] ?? 'CLI';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        
        $logMessage = sprintf(
            "[%s] APPLICATION EXCEPTION: %s | Code: %d | Message: %s | File: %s | Line: %d | URL: %s | Method: %s | IP: %s\n",
            $timestamp,
            get_class($e),
            $code,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $url,
            $method,
            $ip
        );
        $logMessage .= sprintf("Stack Trace:\n%s\n", $e->getTraceAsString());
        @file_put_contents($errorLogFile, $logMessage, FILE_APPEND | LOCK_EX);
        
        if (APP_ENV === 'development') {
            echo "<h1>Error {$code}</h1>";
            echo "<p>{$e->getMessage()}</p>";
            echo "<pre>{$e->getTraceAsString()}</pre>";
        } else {
            if ($code === 404) {
                require_once APP_PATH . '/views/errors/404.php';
            } else {
                require_once APP_PATH . '/views/errors/500.php';
            }
        }
    }
}

