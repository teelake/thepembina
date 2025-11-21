<?php

namespace App\Models;

use App\Core\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    public function findBySlug(string $slug)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }
}


