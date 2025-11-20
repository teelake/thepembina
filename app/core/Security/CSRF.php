<?php
/**
 * CSRF Protection Class
 */

namespace App\Core\Security;

class CSRF
{
    /**
     * Generate CSRF token
     * 
     * @return string
     */
    public function generateToken()
    {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
            $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Get CSRF token
     * 
     * @return string
     */
    public function getToken()
    {
        return $this->generateToken();
    }

    /**
     * Verify CSRF token
     * 
     * @param string $token
     * @return bool
     */
    public function verifyToken($token)
    {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }

        // Check token expiry
        if (isset($_SESSION[CSRF_TOKEN_NAME . '_time'])) {
            $age = time() - $_SESSION[CSRF_TOKEN_NAME . '_time'];
            if ($age > CSRF_TOKEN_EXPIRY) {
                unset($_SESSION[CSRF_TOKEN_NAME]);
                unset($_SESSION[CSRF_TOKEN_NAME . '_time']);
                return false;
            }
        }

        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    /**
     * Get CSRF token field for forms
     * 
     * @return string
     */
    public function getTokenField()
    {
        $token = $this->getToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
}

