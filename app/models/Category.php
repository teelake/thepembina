<?php
/**
 * Category Model
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Helper;

class Category extends Model
{
    protected $table = 'categories';

    /**
     * Find category by slug
     * 
     * @param string $slug
     * @return array|null
     */
    public function findBySlug($slug)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = :slug AND status = 'active'");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get all categories with product count
     * 
     * @return array
     */
    public function getAllWithCount()
    {
        $stmt = $this->db->query("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM {$this->table} c
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
            WHERE c.status = 'active'
            GROUP BY c.id
            ORDER BY c.sort_order, c.name
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get parent categories
     * 
     * @return array
     */
    public function getParents()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE parent_id IS NULL AND status = 'active' ORDER BY sort_order, name");
        return $stmt->fetchAll();
    }

    /**
     * Get child categories
     * 
     * @param int $parentId
     * @return array
     */
    public function getChildren($parentId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE parent_id = :parent_id AND status = 'active' ORDER BY sort_order, name");
        $stmt->execute(['parent_id' => $parentId]);
        return $stmt->fetchAll();
    }

    /**
     * Create category
     * 
     * @param array $data
     * @return int
     */
    public function createCategory($data)
    {
        if (empty($data['slug'])) {
            $data['slug'] = Helper::slugify($data['name']);
        }
        return $this->create($data);
    }

    /**
     * Update category
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCategory($id, $data)
    {
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Helper::slugify($data['name']);
        }
        return $this->update($id, $data);
    }
}

