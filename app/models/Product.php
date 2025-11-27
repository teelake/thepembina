<?php
/**
 * Product Model
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Helper;

class Product extends Model
{
    protected $table = 'products';

    /**
     * Find product by slug
     * 
     * @param string $slug
     * @return array|null
     */
    public function findBySlug($slug)
    {
        $stmt = $this->db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM {$this->table} p LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = :slug AND p.status = 'active'");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get products by category with filters
     * 
     * @param int $categoryId
     * @param array $filters Filter options (sort, min_price, max_price, search, availability, featured)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByCategory($categoryId, $filters = [], $limit = null, $offset = null)
    {
        $sql = "SELECT p.*, c.name as category_name FROM {$this->table} p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = :category_id AND p.status = 'active'";
        $params = ['category_id' => $categoryId];
        
        // Apply filters
        if (!empty($filters['search'])) {
            // Search by product name and description (avoid columns that may not exist on older schemas)
            $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $sql .= " AND p.price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }
        
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $sql .= " AND p.price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }
        
        if (isset($filters['featured']) && $filters['featured'] == '1') {
            $sql .= " AND p.is_featured = 1";
        }
        
        if (isset($filters['availability'])) {
            switch ($filters['availability']) {
                case 'in_stock':
                    $sql .= " AND (p.manage_stock = 0 OR (p.manage_stock = 1 AND p.stock_quantity > 0))";
                    break;
                case 'out_of_stock':
                    $sql .= " AND (p.manage_stock = 1 AND (p.stock_quantity = 0 OR p.stock_status = 'out_of_stock'))";
                    break;
                case 'low_stock':
                    $sql .= " AND (p.manage_stock = 1 AND p.stock_quantity > 0 AND p.stock_quantity <= 5)";
                    break;
            }
        }
        
        // Apply sorting
        $orderBy = 'p.sort_order, p.name';
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $orderBy = 'p.price ASC';
                    break;
                case 'price_desc':
                    $orderBy = 'p.price DESC';
                    break;
                case 'name_asc':
                    $orderBy = 'p.name ASC';
                    break;
                case 'name_desc':
                    $orderBy = 'p.name DESC';
                    break;
                case 'newest':
                    $orderBy = 'p.created_at DESC';
                    break;
                case 'featured':
                    $orderBy = 'p.is_featured DESC, p.sort_order, p.name';
                    break;
            }
        }
        $sql .= " ORDER BY {$orderBy}";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Count products by category with filters
     * 
     * @param int $categoryId
     * @param array $filters
     * @return int
     */
    public function countByCategory($categoryId, $filters = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} p WHERE p.category_id = :category_id AND p.status = 'active'";
        $params = ['category_id' => $categoryId];
        
        // Apply same filters as getByCategory
        if (!empty($filters['search'])) {
            // Search by product name and description (avoid columns that may not exist on older schemas)
            $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $sql .= " AND p.price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }
        
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $sql .= " AND p.price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }
        
        if (isset($filters['featured']) && $filters['featured'] == '1') {
            $sql .= " AND p.is_featured = 1";
        }
        
        if (isset($filters['availability'])) {
            switch ($filters['availability']) {
                case 'in_stock':
                    $sql .= " AND (p.manage_stock = 0 OR (p.manage_stock = 1 AND p.stock_quantity > 0))";
                    break;
                case 'out_of_stock':
                    $sql .= " AND (p.manage_stock = 1 AND (p.stock_quantity = 0 OR p.stock_status = 'out_of_stock'))";
                    break;
                case 'low_stock':
                    $sql .= " AND (p.manage_stock = 1 AND p.stock_quantity > 0 AND p.stock_quantity <= 5)";
                    break;
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Get price range for category
     * 
     * @param int $categoryId
     * @return array ['min' => float, 'max' => float]
     */
    public function getPriceRange($categoryId)
    {
        $stmt = $this->db->prepare("SELECT MIN(price) as min_price, MAX(price) as max_price FROM {$this->table} WHERE category_id = :category_id AND status = 'active'");
        $stmt->execute(['category_id' => $categoryId]);
        $result = $stmt->fetch();
        return [
            'min' => (float)($result['min_price'] ?? 0),
            'max' => (float)($result['max_price'] ?? 0)
        ];
    }

    /**
     * Get featured products
     * 
     * @param int $limit
     * @return array
     */
    public function getFeatured($limit = 10)
    {
        $stmt = $this->db->prepare("SELECT p.*, c.name as category_name FROM {$this->table} p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 AND p.status = 'active' ORDER BY p.sort_order LIMIT :limit");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Check if product name already exists
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
     * Check if product SKU already exists
     * 
     * @param string $sku
     * @param int $excludeId Exclude this ID from check (for updates)
     * @return bool
     */
    public function skuExists($sku, $excludeId = null)
    {
        if (empty($sku)) {
            return false; // Null/empty SKUs are allowed
        }
        
        $sql = "SELECT id FROM {$this->table} WHERE sku = :sku";
        $params = ['sku' => $sku];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Create product
     * 
     * @param array $data
     * @return int
     */
    public function createProduct($data)
    {
        if (empty($data['slug'])) {
            $data['slug'] = Helper::slugify($data['name']);
        }
        return $this->create($data);
    }

    /**
     * Update product
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProduct($id, $data)
    {
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Helper::slugify($data['name']);
        }
        return $this->update($id, $data);
    }

    /**
     * Get product options
     * 
     * @param int $productId
     * @return array
     */
    public function getOptions($productId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT o.*, 
                       GROUP_CONCAT(ov.id, ':', ov.value, ':', ov.price_modifier ORDER BY ov.sort_order SEPARATOR '|') as `values`
                FROM product_options o
                LEFT JOIN product_option_values ov ON o.id = ov.option_id
                WHERE o.product_id = :product_id
                GROUP BY o.id
                ORDER BY o.sort_order
            ");
            $stmt->execute(['product_id' => $productId]);
            $options = $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log(sprintf('[ProductModel] Failed to load options for product %s: %s', $productId, $e->getMessage()));
            return [];
        }
        
        // Parse values
        foreach ($options as &$option) {
            $values = [];
            if ($option['values']) {
                foreach (explode('|', $option['values']) as $valueStr) {
                    $parts = explode(':', $valueStr);
                    if (count($parts) >= 3) {
                        $values[] = [
                            'id' => $parts[0],
                            'value' => $parts[1],
                            'price_modifier' => $parts[2]
                        ];
                    }
                }
            }
            $option['values'] = $values;
        }
        
        return $options;
    }
}

