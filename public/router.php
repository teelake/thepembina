<?php
/**
 * Router for PHP Built-in Server
 * This file handles routing when using PHP's built-in development server
 */

// If the requested file exists, serve it
if (file_exists(__DIR__ . $_SERVER['REQUEST_URI'])) {
    return false; // Serve the file as-is
}

// Otherwise, route through the main application
require_once dirname(__DIR__) . '/index.php';

