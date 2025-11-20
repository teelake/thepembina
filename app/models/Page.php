<?php
/**
 * Page Model
 */

namespace App\Models;

use App\Core\Model;

class Page extends Model
{
    protected $table = 'pages';

    public function findBySlug($slug)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = :slug AND status = 'published'");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }
}


