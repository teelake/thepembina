<?php

namespace App\Models;

use App\Core\Model;

class Role extends Model
{
    protected $table = 'roles';

    public function getAllWithPermissionCount()
    {
        $stmt = $this->db->query("
            SELECT r.*, COUNT(rp.permission_id) AS permission_count
            FROM {$this->table} r
            LEFT JOIN role_permissions rp ON rp.role_id = r.id
            GROUP BY r.id
            ORDER BY r.created_at ASC
        ");
        return $stmt->fetchAll();
    }

    public function getPermissions(int $roleId): array
    {
        $stmt = $this->db->prepare("
            SELECT permission_id FROM role_permissions WHERE role_id = :role_id
        ");
        $stmt->execute(['role_id' => $roleId]);
        return array_column($stmt->fetchAll(), 'permission_id');
    }

    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $this->db->beginTransaction();
        try {
            $delete = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
            $delete->execute(['role_id' => $roleId]);

            if (!empty($permissionIds)) {
                $insert = $this->db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");
                foreach ($permissionIds as $permissionId) {
                    $insert->execute([
                        'role_id' => $roleId,
                        'permission_id' => (int)$permissionId
                    ]);
                }
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}


