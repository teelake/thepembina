<?php

namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    protected $table = 'settings';

    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateSetting($key, $value)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET value = :value WHERE `key` = :key");
        return $stmt->execute(['value' => $value, 'key' => $key]);
    }
}


