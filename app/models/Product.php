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
     * Get products by category
     * 
     * @param int $categoryId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByCategory($categoryId, $limit = null, $offset = null)
    {
        $sql = "SELECT p.*, c.name as category_name FROM {$this->table} p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = :category_id AND p.status = 'active' ORDER BY p.sort_order, p.name";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['category_id' => $categoryId]);
        return $stmt->fetchAll();
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
                       GROUP_CONCAT(ov.id, ':', ov.value, ':', ov.price_modifier ORDER BY ov.sort_order SEPARATOR '|') as values
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

