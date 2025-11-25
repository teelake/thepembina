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

    public function findByName($name)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch() ?: null;
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
     * Find main navigation categories (Food, As E Dey Hot, Drinks)
     * Tries multiple name variations
     * 
     * @return array
     */
    public function getMainNavigationCategories()
    {
        // Try to find by common names/variations (in order of preference)
        $variations = [
            ['Food', 'food', 'Main Meals', 'Main Meal'],
            ['As E Dey Hot', 'as e dey hot', 'As Per Menu', 'Hot Items', 'Pepper Soup'],
            ['Drinks', 'drinks', 'All Drinks', 'Beverages', 'Beverage']
        ];
        
        $found = [];
        foreach ($variations as $varGroup) {
            $placeholders = implode(',', array_fill(0, count($varGroup), '?'));
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE name IN ($placeholders) AND status = 'active' LIMIT 1");
            $stmt->execute($varGroup);
            $category = $stmt->fetch();
            if ($category) {
                $found[] = $category;
            }
        }
        
        // If we found less than 3, fill with top categories
        if (count($found) < 3) {
            $allCategories = $this->getAllWithCount();
            $foundIds = array_column($found, 'id');
            foreach ($allCategories as $cat) {
                if (!in_array($cat['id'], $foundIds) && count($found) < 3) {
                    $found[] = $cat;
                }
            }
        }
        
        return array_slice($found, 0, 3);
    }
}

