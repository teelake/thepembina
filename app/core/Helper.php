<?php
/**
 * Helper Functions
 */

namespace App\Core;

class Helper
{
    /**
     * Generate slug from string
     * 
     * @param string $string
     * @return string
     */
    public static function slugify($string)
    {
        $string = strtolower(trim($string));
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        return trim($string, '-');
    }

    /**
     * Format currency
     * 
     * @param float $amount
     * @param string $currency
     * @return string
     */
    public static function formatCurrency($amount, $currency = 'CAD')
    {
        return $currency . ' ' . number_format($amount, 2);
    }

    /**
     * Generate order number
     * 
     * @return string
     */
    public static function generateOrderNumber()
    {
        return 'PP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Upload file
     * 
     * @param array $file $_FILES array element
     * @param string $directory Upload directory
     * @param array $allowedTypes Allowed file types
     * @return string|false Filename on success, false on failure
     */
    public static function uploadFile($file, $directory = 'uploads', $allowedTypes = [])
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if ($file['size'] > MAX_UPLOAD_SIZE) {
            return false;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
            return false;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadPath = PUBLIC_PATH . '/' . $directory . '/' . $filename;

        if (!is_dir(dirname($uploadPath))) {
            mkdir(dirname($uploadPath), 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return $directory . '/' . $filename;
        }

        return false;
    }

    /**
     * Delete file
     * 
     * @param string $filepath
     * @return bool
     */
    public static function deleteFile($filepath)
    {
        $fullPath = PUBLIC_PATH . '/' . $filepath;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    /**
     * Get setting value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getSetting($key, $default = null)
    {
        static $settings = null;
        
        if ($settings === null) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT `key`, `value` FROM settings");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['key']] = $row['value'];
            }
        }
        
        return $settings[$key] ?? $default;
    }

    /**
     * Set setting value
     * 
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function setSetting($key, $value)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO settings (`key`, `value`) VALUES (:key, :value) ON DUPLICATE KEY UPDATE `value` = :value");
        return $stmt->execute(['key' => $key, 'value' => $value]);
    }

    /**
     * Log activity (wrapper for AuditTrail)
     * 
     * @param string $action
     * @param string $model
     * @param int $modelId
     * @param string $description
     * @param array $data
     */
    public static function logActivity($action, $model = null, $modelId = null, $description = null, $data = null)
    {
        \App\Core\AuditTrail::log($action, $model, $modelId, $description, $data);
    }

    /**
     * Hash password
     * 
     * @param string $password
     * @return string
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     * 
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate random token
     * 
     * @param int $length
     * @return string
     */
    public static function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Sanitize output
     * 
     * @param string $string
     * @return string
     */
    public static function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

