<?php
/**
 * Router Class
 * Handles routing and URL parsing
 */

namespace App\Core;

class Router
{
    private $routes = [];
    private $params = [];

    /**
     * Add route
     * 
     * @param string $method HTTP method
     * @param string $route Route pattern
     * @param array $params Controller and action
     */
    public function add($method, $route, $params = [])
    {
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z0-9-]+)', $route);
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        $route = '/^' . $route . '$/i';
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => $route,
            'params' => $params
        ];
    }

    /**
     * Match route
     * 
     * @param string $url Request URL
     * @param string $method HTTP method
     * @return bool
     */
    public function match($url, $method)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['route'], $url, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $this->params[$key] = $match;
                    }
                }
                $this->params = array_merge($this->params, $route['params']);
                return true;
            }
        }
        return false;
    }

    /**
     * Get route parameters
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Dispatch route
     * 
     * @param string $url Request URL
     * @param string $method HTTP method
     */
    public function dispatch($url, $method)
    {
        $url = $this->removeQueryString($url);

        if ($this->match($url, $method)) {
            $controller = $this->params['controller'] ?? 'Home';
            
            // Handle namespaced controllers (e.g., Admin\Dashboard)
            if (strpos($controller, '\\') !== false) {
                $controllerClass = "App\\Controllers\\{$controller}Controller";
            } else {
                $controller = $this->convertToStudlyCaps($controller);
                $controllerClass = "App\\Controllers\\{$controller}Controller";
            }

            if (class_exists($controllerClass)) {
                $controllerObject = new $controllerClass($this->params);
                $action = $this->params['action'] ?? 'index';
                $action = $this->convertToCamelCase($action);

                if (is_callable([$controllerObject, $action])) {
                    $controllerObject->$action();
                } else {
                    throw new \Exception("Method {$action} not found in controller {$controllerClass}");
                }
            } else {
                throw new \Exception("Controller class {$controllerClass} not found");
            }
        } else {
            throw new \Exception("No route matched", 404);
        }
    }

    /**
     * Remove query string from URL
     * 
     * @param string $url
     * @return string
     */
    private function removeQueryString($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);
            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }
        return $url;
    }

    /**
     * Convert string to StudlyCaps
     * 
     * @param string $string
     * @return string
     */
    private function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert string to camelCase
     * 
     * @param string $string
     * @return string
     */
    private function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }
}

