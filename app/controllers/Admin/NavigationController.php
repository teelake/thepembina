<?php
/**
 * Navigation Management Controller
 * Manages navigation menu items (categories, pages, custom links)
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\NavigationMenuItem;
use App\Models\Category;
use App\Core\Helper;

class NavigationController extends Controller
{
    private $menuItemModel;
    private $categoryModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin', 'data_entry']);
        $this->menuItemModel = new NavigationMenuItem();
        $this->categoryModel = new Category();
    }

    /**
     * Navigation management page
     */
    public function index()
    {
        try {
            // Check if table exists - try a simple SELECT query first (most direct)
            $tableExists = false;
            try {
                // Try to query the table - if it exists, this will succeed
                $this->menuItemModel->db->query("SELECT 1 FROM navigation_menu_items LIMIT 1");
                $tableExists = true;
            } catch (\Exception $e) {
                // Table doesn't exist or query failed
                $tableExists = false;
                // Try alternative method using information_schema
                try {
                    $dbName = $this->menuItemModel->db->query("SELECT DATABASE()")->fetchColumn();
                    $stmt = $this->menuItemModel->db->prepare("
                        SELECT COUNT(*) 
                        FROM information_schema.tables 
                        WHERE table_schema = ? 
                        AND table_name = 'navigation_menu_items'
                    ");
                    $stmt->execute([$dbName]);
                    $tableExists = $stmt->fetchColumn() > 0;
                } catch (\Exception $e2) {
                    // Fallback to SHOW TABLES
                    try {
                        $result = $this->menuItemModel->db->query("SHOW TABLES LIKE 'navigation_menu_items'");
                        $tableExists = $result && $result->rowCount() > 0;
                    } catch (\Exception $e3) {
                        // All methods failed, assume table doesn't exist
                        $tableExists = false;
                    }
                }
            }
            
            if (!$tableExists) {
                $this->render('admin/navigation/migration-needed', [
                    'page_title' => 'Navigation Management',
                    'current_page' => 'navigation',
                    'csrfField' => $this->csrf->getTokenField()
                ]);
                return;
            }

            // Try to fetch data - wrap each call in try-catch
            $menuItems = [];
            $allItems = [];
            $categories = [];
            $pages = [];
            
            try {
                $menuItems = $this->menuItemModel->getActiveItems();
            } catch (\Exception $e) {
                error_log("Error fetching active items: " . $e->getMessage());
            }
            
            try {
                $allItems = $this->menuItemModel->findAll([], '`order` ASC, `label` ASC');
            } catch (\Exception $e) {
                error_log("Error fetching all items: " . $e->getMessage());
                // If table doesn't exist, show migration page
                $errorMsg = strtolower($e->getMessage());
                if (strpos($errorMsg, "doesn't exist") !== false || 
                    strpos($errorMsg, "does not exist") !== false ||
                    strpos($errorMsg, "unknown table") !== false ||
                    strpos($errorMsg, "table") !== false && strpos($errorMsg, "exist") !== false) {
                    $this->render('admin/navigation/migration-needed', [
                        'page_title' => 'Navigation Management',
                        'current_page' => 'navigation',
                        'csrfField' => $this->csrf->getTokenField()
                    ]);
                    return;
                }
            }
            
            try {
                $categories = $this->menuItemModel->getAvailableCategories();
            } catch (\Exception $e) {
                error_log("Error fetching categories: " . $e->getMessage());
            }
            
            try {
                $pages = $this->menuItemModel->getAvailablePages();
            } catch (\Exception $e) {
                error_log("Error fetching pages: " . $e->getMessage());
            }

            $this->render('admin/navigation/index', [
                'menuItems' => $allItems ?: [],
                'activeItems' => $menuItems ?: [],
                'categories' => $categories ?: [],
                'pages' => $pages ?: [],
                'page_title' => 'Navigation Management',
                'current_page' => 'navigation',
                'csrfField' => $this->csrf->getTokenField()
            ]);
        } catch (\Exception $e) {
            // Log error
            error_log("Navigation page error: " . $e->getMessage());
            error_log("Navigation page error trace: " . $e->getTraceAsString());
            
            // Check if it's a table missing error
            $errorMsg = strtolower($e->getMessage());
            if (strpos($errorMsg, "doesn't exist") !== false || 
                strpos($errorMsg, "does not exist") !== false ||
                strpos($errorMsg, "unknown table") !== false ||
                strpos($errorMsg, "table") !== false && strpos($errorMsg, "exist") !== false) {
                $this->render('admin/navigation/migration-needed', [
                    'page_title' => 'Navigation Management',
                    'current_page' => 'navigation',
                    'csrfField' => $this->csrf->getTokenField()
                ]);
            } else {
                $this->render('admin/navigation/error', [
                    'error' => $e->getMessage(),
                    'page_title' => 'Navigation Management - Error',
                    'current_page' => 'navigation',
                    'csrfField' => $this->csrf->getTokenField()
                ]);
            }
        }
    }

    /**
     * Create menu item form
     */
    public function create()
    {
        try {
            $categories = $this->menuItemModel->getAvailableCategories();
            $pages = $this->menuItemModel->getAvailablePages();

            $this->render('admin/navigation/form', [
                'categories' => $categories ?: [],
                'pages' => $pages ?: [],
                'page_title' => 'Add Menu Item',
                'current_page' => 'navigation',
                'csrfField' => $this->csrf->getTokenField()
            ]);
        } catch (\Exception $e) {
            error_log("Navigation create form error: " . $e->getMessage());
            $this->redirect('/admin/navigation?error=' . urlencode('Unable to load form. Please ensure the navigation table exists.'));
        }
    }

    /**
     * Store menu item
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/navigation');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/navigation?error=Invalid security token');
            return;
        }

        $type = $this->post('type');
        $data = [
            'label' => $this->post('label'),
            'type' => $type,
            'order' => (int)$this->post('order', 0),
            'status' => $this->post('status', 'active'),
            'target' => $this->post('target', '_self'),
            'icon' => $this->post('icon') ?: null
        ];

        // Set type-specific fields
        if ($type === 'category') {
            $data['category_id'] = (int)$this->post('category_id');
            $data['page_id'] = null;
            $data['url'] = null;
        } elseif ($type === 'page') {
            $data['page_id'] = (int)$this->post('page_id');
            $data['category_id'] = null;
            $data['url'] = null;
        } elseif ($type === 'custom') {
            $data['url'] = $this->post('url');
            $data['category_id'] = null;
            $data['page_id'] = null;
        }

        $id = $this->menuItemModel->create($data);
        if ($id) {
            $this->redirect('/admin/navigation?success=Menu item created successfully');
        } else {
            $this->redirect('/admin/navigation?error=Failed to create menu item');
        }
    }

    /**
     * Edit menu item form
     */
    public function edit()
    {
        try {
            $id = (int)($this->params['id'] ?? 0);
            $item = $this->menuItemModel->getWithDetails($id);
            
            if (!$item) {
                $this->redirect('/admin/navigation?error=Menu item not found');
                return;
            }

            $categories = $this->menuItemModel->getAvailableCategories();
            $pages = $this->menuItemModel->getAvailablePages();

            $this->render('admin/navigation/form', [
                'item' => $item,
                'categories' => $categories ?: [],
                'pages' => $pages ?: [],
                'page_title' => 'Edit Menu Item',
                'current_page' => 'navigation',
                'csrfField' => $this->csrf->getTokenField()
            ]);
        } catch (\Exception $e) {
            error_log("Navigation edit form error: " . $e->getMessage());
            $this->redirect('/admin/navigation?error=' . urlencode('Unable to load form. Please ensure the navigation table exists.'));
        }
    }

    /**
     * Update menu item
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/navigation');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $item = $this->menuItemModel->find($id);
        
        if (!$item) {
            $this->redirect('/admin/navigation?error=Menu item not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/navigation/{$id}/edit?error=Invalid security token");
            return;
        }

        $type = $this->post('type');
        $data = [
            'label' => $this->post('label'),
            'type' => $type,
            'order' => (int)$this->post('order', 0),
            'status' => $this->post('status', 'active'),
            'target' => $this->post('target', '_self'),
            'icon' => $this->post('icon') ?: null
        ];

        // Set type-specific fields
        if ($type === 'category') {
            $data['category_id'] = (int)$this->post('category_id');
            $data['page_id'] = null;
            $data['url'] = null;
        } elseif ($type === 'page') {
            $data['page_id'] = (int)$this->post('page_id');
            $data['category_id'] = null;
            $data['url'] = null;
        } elseif ($type === 'custom') {
            $data['url'] = $this->post('url');
            $data['category_id'] = null;
            $data['page_id'] = null;
        }

        if ($this->menuItemModel->update($id, $data)) {
            $this->redirect('/admin/navigation?success=Menu item updated successfully');
        } else {
            $this->redirect("/admin/navigation/{$id}/edit?error=Failed to update menu item");
        }
    }

    /**
     * Delete menu item
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/navigation');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/navigation?error=Invalid security token');
            return;
        }

        if ($this->menuItemModel->delete($id)) {
            $this->redirect('/admin/navigation?success=Menu item deleted successfully');
        } else {
            $this->redirect('/admin/navigation?error=Failed to delete menu item');
        }
    }

    /**
     * Update order (AJAX)
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

        $db = $this->menuItemModel->db;
        $db->beginTransaction();
        
        try {
            foreach ($orders as $order) {
                $id = (int)($order['id'] ?? 0);
                $orderValue = (int)($order['order'] ?? 0);
                
                if ($id > 0) {
                    $db->prepare("UPDATE navigation_menu_items SET `order` = ? WHERE id = ?")
                       ->execute([$orderValue, $id]);
                }
            }
            
            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
        } catch (\Exception $e) {
            $db->rollBack();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to update order: ' . $e->getMessage()]);
        }
    }
}
