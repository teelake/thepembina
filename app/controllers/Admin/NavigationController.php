<?php
/**
 * Navigation Management Controller
 * Manages which categories appear in main navigation
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Category;

class NavigationController extends Controller
{
    private $categoryModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin', 'data_entry']);
        $this->categoryModel = new Category();
    }

    /**
     * Navigation management page
     */
    public function index()
    {
        // Get all active categories with navigation status
        $categories = $this->categoryModel->getAllWithCount();
        
        // Sort: navigation categories first, then others
        usort($categories, function($a, $b) {
            // Navigation categories first
            if ($a['show_in_nav'] != $b['show_in_nav']) {
                return $b['show_in_nav'] - $a['show_in_nav'];
            }
            // Then by nav_order
            if ($a['show_in_nav'] && $b['show_in_nav']) {
                return ($a['nav_order'] ?? 0) - ($b['nav_order'] ?? 0);
            }
            // Then by sort_order
            return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
        });

        $this->render('admin/navigation/index', [
            'categories' => $categories,
            'page_title' => 'Navigation Management',
            'current_page' => 'navigation',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    /**
     * Update navigation settings (AJAX)
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->verifyCSRF()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            return;
        }

        $categoryId = (int)$this->post('category_id');
        $showInNav = $this->post('show_in_nav') ? 1 : 0;
        $navOrder = (int)$this->post('nav_order', 0);

        // Limit to 3 categories in navigation
        if ($showInNav) {
            $currentNavCount = $this->categoryModel->db->query(
                "SELECT COUNT(*) FROM categories WHERE show_in_nav = 1 AND id != {$categoryId}"
            )->fetchColumn();
            
            if ($currentNavCount >= 3) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Maximum 3 categories can be shown in navigation. Please remove one first.'
                ]);
                return;
            }
        }

        $data = [
            'show_in_nav' => $showInNav,
            'nav_order' => $navOrder
        ];

        if ($this->categoryModel->update($categoryId, $data)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Navigation updated successfully']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to update navigation']);
        }
    }

    /**
     * Bulk update navigation order
     */
    public function updateOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->verifyCSRF()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            return;
        }

        $orders = $this->post('orders', []);
        
        if (empty($orders) || !is_array($orders)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid order data']);
            return;
        }

        $db = $this->categoryModel->db;
        $db->beginTransaction();
        
        try {
            foreach ($orders as $order) {
                $categoryId = (int)($order['id'] ?? 0);
                $navOrder = (int)($order['nav_order'] ?? 0);
                
                if ($categoryId > 0) {
                    $db->prepare("UPDATE categories SET nav_order = ? WHERE id = ?")
                       ->execute([$navOrder, $categoryId]);
                }
            }
            
            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Navigation order updated successfully']);
        } catch (\Exception $e) {
            $db->rollBack();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to update order: ' . $e->getMessage()]);
        }
    }
}

