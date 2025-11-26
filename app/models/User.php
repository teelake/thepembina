<?php
/**
 * User Model
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Helper;

class User extends Model
{
    protected $table = 'users';

    /**
     * Find user by email
     * 
     * @param string $email
     * @return array|null
     */
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT u.*, r.name as role_name, r.slug as role_slug FROM {$this->table} u LEFT JOIN roles r ON u.role_id = r.id WHERE u.email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create user
     * 
     * @param array $data
     * @return int
     */
    public function createUser($data)
    {
        $data['password'] = Helper::hashPassword($data['password']);
        $data['email_verification_token'] = Helper::generateToken();
        return $this->create($data);
    }

    /**
     * Update user
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser($id, $data)
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Helper::hashPassword($data['password']);
        } else {
            unset($data['password']);
        }
        return $this->update($id, $data);
    }

    /**
     * Verify user password
     * 
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function verifyLogin($email, $password)
    {
        $user = $this->findByEmail($email);
        
        if ($user && Helper::verifyPassword($password, $user['password'])) {
            if ($user['status'] === 'active') {
                // Update last login
                $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
                return $user;
            }
        }
        
        return null;
    }

    /**
     * Check if user has permission
     * 
     * @param int $userId
     * @param string $permissionSlug
     * @return bool
     */
    public function hasPermission($userId, $permissionSlug)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM role_permissions rp
            INNER JOIN permissions p ON rp.permission_id = p.id
            INNER JOIN users u ON u.role_id = rp.role_id
            WHERE u.id = :user_id AND p.slug = :permission
        ");
        $stmt->execute(['user_id' => $userId, 'permission' => $permissionSlug]);
        $result = $stmt->fetch();
        return (int)$result['count'] > 0;
    }

    /**
     * Get user permissions
     * 
     * @param int $userId
     * @return array
     */
    public function getPermissions($userId)
    {
        $stmt = $this->db->prepare("
            SELECT p.slug 
            FROM role_permissions rp
            INNER JOIN permissions p ON rp.permission_id = p.id
            INNER JOIN users u ON u.role_id = rp.role_id
            WHERE u.id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get all users with role names
     * 
     * @param array $conditions
     * @param string $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function findAllWithRoles($conditions = [], $orderBy = 'created_at DESC', $limit = null, $offset = null)
    {
        $where = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $where[] = "u.{$key} = :{$key}";
            $params[$key] = $value;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug 
                FROM {$this->table} u 
                LEFT JOIN roles r ON u.role_id = r.id 
                {$whereClause}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
            if ($offset !== null) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

