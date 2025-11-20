<?php
/**
 * View Class
 * Handles view rendering
 */

namespace App\Core;

class View
{
    private $data = [];

    /**
     * Render view
     * 
     * @param string $viewName View file name
     * @param array $data Data to pass to view
     */
    public function render($viewName, $data = [])
    {
        $this->data = $data;
        extract($data);
        
        $viewFile = APP_PATH . '/views/' . $viewName . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View file {$viewName}.php not found");
        }
        
        require_once $viewFile;
    }

    /**
     * Get data
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Escape output
     * 
     * @param string $string
     * @return string
     */
    public function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

