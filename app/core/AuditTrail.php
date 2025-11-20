<?php
/**
 * Audit Trail Class
 * Comprehensive logging of all user actions
 */

namespace App\Core;

use App\Core\Database;

class AuditTrail
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Log activity
     * 
     * @param string $action Action performed
     * @param string $model Model name (e.g., 'product', 'order')
     * @param int|null $modelId Model ID
     * @param string|null $description Additional description
     * @param array|null $data Additional data (stored as JSON)
     * @return bool
     */
    public static function log($action, $model = null, $modelId = null, $description = null, $data = null)
    {
        $db = Database::getInstance()->getConnection();
        
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $requestUri = $_SERVER['REQUEST_URI'] ?? null;
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        
        $stmt = $db->prepare("
            INSERT INTO activity_logs 
            (user_id, action, model, model_id, description, ip_address, user_agent, request_uri, request_method, additional_data, created_at)
            VALUES 
            (:user_id, :action, :model, :model_id, :description, :ip_address, :user_agent, :request_uri, :request_method, :additional_data, NOW())
        ");
        
        return $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'request_uri' => $requestUri,
            'request_method' => $requestMethod,
            'additional_data' => $data ? json_encode($data) : null
        ]);
    }

    /**
     * Get activity logs
     * 
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getLogs($filters = [], $limit = 50, $offset = 0)
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT al.*, u.email as user_email, u.first_name, u.last_name 
                FROM activity_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['user_id'])) {
            $sql .= " AND al.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }
        
        if (isset($filters['action'])) {
            $sql .= " AND al.action = :action";
            $params['action'] = $filters['action'];
        }
        
        if (isset($filters['model'])) {
            $sql .= " AND al.model = :model";
            $params['model'] = $filters['model'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND al.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND al.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}

