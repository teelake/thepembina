<?php
/**
 * Navigation Menu Item Model
 */

namespace App\Models;

use App\Core\Model;

class NavigationMenuItem extends Model
{
    protected $table = 'navigation_menu_items';
    protected $primaryKey = 'id';

    /**
     * Get all active menu items ordered by display order
     * 
     * @return array
     */
    public function getActiveItems()
    {
        $stmt = $this->db->query("
            SELECT 
                nmi.*,
                c.slug as category_slug,
                c.name as category_name,
                p.slug as page_slug,
                p.title as page_title
            FROM {$this->table} nmi
            LEFT JOIN categories c ON nmi.category_id = c.id AND nmi.type = 'category'
            LEFT JOIN pages p ON nmi.page_id = p.id AND nmi.type = 'page'
            WHERE nmi.status = 'active'
            ORDER BY nmi.order ASC, nmi.label ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get menu item with full details
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithDetails($id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                nmi.*,
                c.slug as category_slug,
                c.name as category_name,
                p.slug as page_slug,
                p.title as page_title
            FROM {$this->table} nmi
            LEFT JOIN categories c ON nmi.category_id = c.id AND nmi.type = 'category'
            LEFT JOIN pages p ON nmi.page_id = p.id AND nmi.type = 'page'
            WHERE nmi.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get URL for menu item
     * 
     * @param array $item
     * @return string
     */
    public function getUrl($item)
    {
        // Get BASE_URL from global or constant
        $baseUrl = defined('BASE_URL') ? BASE_URL : ($GLOBALS['BASE_URL'] ?? '');
        
        switch ($item['type']) {
            case 'category':
                if (empty($item['category_slug'])) {
                    return '#';
                }
                return $baseUrl . '/menu/' . $item['category_slug'];
            case 'page':
                if (empty($item['page_slug'])) {
                    return '#';
                }
                return $baseUrl . '/page/' . $item['page_slug'];
            case 'custom':
                return $item['url'] ?? '#';
            default:
                return '#';
        }
    }

    /**
     * Get all categories for dropdown
     * 
     * @return array
     */
    public function getAvailableCategories()
    {
        $stmt = $this->db->query("
            SELECT id, name, slug 
            FROM categories 
            WHERE status = 'active' 
            ORDER BY name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get all pages for dropdown
     * 
     * @return array
     */
    public function getAvailablePages()
    {
        $stmt = $this->db->query("
            SELECT id, title, slug 
            FROM pages 
            WHERE status = 'published' 
            ORDER BY title ASC
        ");
        return $stmt->fetchAll();
    }
}

