<?php
/**
 * Base Controller Class
 */

namespace App\Core;

use App\Core\View;
use App\Core\Security\CSRF;
use App\Core\Security\RateLimiter;
use App\Core\Security\Validator;

abstract class Controller
{
    protected $params = [];
    protected $view;
    protected $csrf;
    protected $rateLimiter;
    protected $validator;

    public function __construct($params = [])
    {
        $this->params = $params;
        $this->view = new View();
        $this->csrf = new CSRF();
        $this->rateLimiter = new RateLimiter();
        $this->validator = new Validator();
        
        // Check rate limiting
        if (!$this->rateLimiter->checkLimit()) {
            $this->jsonResponse(['error' => 'Too many requests. Please try again later.'], 429);
            exit;
        }
    }

    /**
     * Render view
     * 
     * @param string $viewName View file name
     * @param array $data Data to pass to view
     */
    protected function render($viewName, $data = [])
    {
        $this->view->render($viewName, $data);
    }

    /**
     * Render JSON response
     * 
     * @param array $data
     * @param int $statusCode
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect
     * 
     * @param string $url
     */
    protected function redirect($url)
    {
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $base = rtrim(BASE_URL, '/');
            $path = '/' . ltrim($url, '/');
            $url = $base . $path;
        }
        header("Location: {$url}");
        exit;
    }

    /**
     * Get POST data
     * 
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     * 
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    protected function get($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Verify CSRF token
     * 
     * @return bool
     */
    protected function verifyCSRF()
    {
        $token = $this->post(CSRF_TOKEN_NAME) ?? $this->get(CSRF_TOKEN_NAME);
        return $this->csrf->verifyToken($token);
    }

    /**
     * Require authentication
     */
    protected function requireAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    /**
     * Require specific role
     * 
     * @param string|array $roles
     */
    protected function requireRole($roles)
    {
        $this->requireAuth();
        
        $userRole = $_SESSION['user_role'] ?? null;
        
        if (is_array($roles)) {
            if (!in_array($userRole, $roles)) {
                $this->redirect('/unauthorized');
            }
        } else {
            if ($userRole !== $roles) {
                $this->redirect('/unauthorized');
            }
        }
    }

    /**
     * Get current user ID
     * 
     * @return int|null
     */
    protected function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user role
     * 
     * @return string|null
     */
    protected function getUserRole()
    {
        return $_SESSION['user_role'] ?? null;
    }
}

