<?php
/**
 * Rate Limiting Class
 * Prevents spam and abuse
 */

namespace App\Core\Security;

class RateLimiter
{
    /**
     * Check rate limit
     * 
     * @param string $identifier User identifier (IP or user ID)
     * @return bool
     */
    public function checkLimit($identifier = null)
    {
        $identifier = $identifier ?? $this->getIdentifier();
        $key = 'rate_limit_' . md5($identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 1,
                'reset_time' => time() + RATE_LIMIT_WINDOW
            ];
            return true;
        }

        $data = $_SESSION[$key];
        
        // Reset if window expired
        if (time() > $data['reset_time']) {
            $_SESSION[$key] = [
                'count' => 1,
                'reset_time' => time() + RATE_LIMIT_WINDOW
            ];
            return true;
        }

        // Check limit
        if ($data['count'] >= RATE_LIMIT_REQUESTS) {
            return false;
        }

        // Increment count
        $_SESSION[$key]['count']++;
        return true;
    }

    /**
     * Get user identifier
     * 
     * @return string
     */
    private function getIdentifier()
    {
        if (isset($_SESSION['user_id'])) {
            return 'user_' . $_SESSION['user_id'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Reset rate limit for identifier
     * 
     * @param string $identifier
     */
    public function resetLimit($identifier = null)
    {
        $identifier = $identifier ?? $this->getIdentifier();
        $key = 'rate_limit_' . md5($identifier);
        unset($_SESSION[$key]);
    }
}

