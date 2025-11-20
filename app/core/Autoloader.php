<?php
/**
 * Autoloader Class
 * PSR-4 compliant autoloader
 */

namespace App\Core;

class Autoloader
{
    /**
     * Register autoloader
     */
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'load']);
    }

    /**
     * Load class file
     * 
     * @param string $class Full class name with namespace
     */
    public static function load($class)
    {
        // Remove App\ namespace prefix
        $prefix = 'App\\';
        $baseDir = APP_PATH . '/';
        
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

