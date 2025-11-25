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
        // Check if show_in_nav and nav_order columns exist
        $columns = $this->db->query("SHOW COLUMNS FROM {$this->table}")->fetchAll(\PDO::FETCH_COLUMN);
        $hasNavFields = in_array('show_in_nav', $columns) && in_array('nav_order', $columns);
        
        $selectFields = "c.*, COUNT(p.id) as product_count";
        if ($hasNavFields) {
            $selectFields .= ", c.show_in_nav, c.nav_order";
        }
        
        $stmt = $this->db->query("
            SELECT {$selectFields}
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

    public function findByName($name)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Check if category name already exists
     * 
     * @param string $name
     * @param int $excludeId Exclude this ID from check (for updates)
     * @return bool
     */
    public function nameExists($name, $excludeId = null)
    {
        $sql = "SELECT id FROM {$this->table} WHERE name = :name";
        $params = ['name' => $name];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Find categories by names (for main navigation)
     * 
     * @param array $names Array of category names to find
     * @return array
     */
    public function findByNames($names)
    {
        if (empty($names)) {
            return [];
        }
        
        // Remove duplicates and empty values
        $names = array_unique(array_filter($names));
        if (empty($names)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($names), '?'));
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE name IN ($placeholders) AND status = 'active' ORDER BY FIELD(name, $placeholders)");
        $stmt->execute(array_merge($names, $names));
        return $stmt->fetchAll();
    }

    /**
     * Get main navigation categories (admin-managed)
     * Returns categories marked to show in navigation, ordered by nav_order
     * 
     * @return array
     */
    public function getMainNavigationCategories()
    {
        $stmt = $this->db->query("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM {$this->table} c
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
            WHERE c.status = 'active' AND c.show_in_nav = 1
            GROUP BY c.id
            ORDER BY c.nav_order ASC, c.sort_order ASC, c.name ASC
            LIMIT 3
        ");
        $categories = $stmt->fetchAll();
        
        // If no categories are marked for navigation, fallback to top 3 by sort_order
        if (empty($categories)) {
            $stmt = $this->db->query("
                SELECT c.*, COUNT(p.id) as product_count 
                FROM {$this->table} c
                LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
                WHERE c.status = 'active'
                GROUP BY c.id
                ORDER BY c.sort_order ASC, c.name ASC
                LIMIT 3
            ");
            $categories = $stmt->fetchAll();
        }
        
        return $categories;
    }
}

