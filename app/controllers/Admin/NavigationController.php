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
    private $navigationTableExists;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin', 'data_entry']);
        $this->menuItemModel = new NavigationMenuItem();
        $this->categoryModel = new Category();
        $this->navigationTableExists = $this->menuItemModel->tableExists();
    }

    private function renderMigrationNeeded()
    {
        $this->render('admin/navigation/migration-needed', [
            'page_title' => 'Navigation Management',
            'current_page' => 'navigation',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    /**
     * Navigation management page
     */
    public function index()
    {
        try {
            $categories = $this->categoryModel->getAllWithCount();
            $navCategories = [];
            foreach ($categories as $cat) {
                if (isset($cat['show_in_nav']) && $cat['show_in_nav'] == 1) {
                    $navCategories[] = $cat;
                }
            }
            usort($navCategories, function($a, $b) {
                $orderA = isset($a['nav_order']) ? (int)$a['nav_order'] : 999;
                $orderB = isset($b['nav_order']) ? (int)$b['nav_order'] : 999;
                return $orderA <=> $orderB;
            });

            $menuItems = [];
            if ($this->navigationTableExists) {
                $menuItems = $this->menuItemModel->getAllItems();
            }

            $view = $this->navigationTableExists ? 'admin/navigation/index' : 'admin/navigation/index-simple';

            $this->render($view, [
                'categories' => $categories,
                'navCategories' => $navCategories,
                'menuItems' => $menuItems,
                'hasCustomNav' => $this->navigationTableExists,
                'page_title' => 'Navigation Management',
                'current_page' => 'navigation',
                'csrfField' => $this->csrf->getTokenField()
            ]);
        } catch (\Exception $e) {
            $this->logError("Navigation page error: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->render('admin/navigation/error', [
                'error' => $e->getMessage(),
                'page_title' => 'Navigation Management - Error',
                'current_page' => 'navigation',
                'csrfField' => $this->csrf->getTokenField()
            ]);
        }
    }

    /**
     * Create menu item form
     */
    public function create()
    {
        if (!$this->navigationTableExists) {
            $this->renderMigrationNeeded();
            return;
        }

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
            $this->logError("Navigation create form error: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
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

        if (!$this->navigationTableExists) {
            $this->redirect('/admin/navigation?error=' . urlencode('Navigation menu table not found. Please run the migration first.'));
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
        if (!$this->navigationTableExists) {
            $this->renderMigrationNeeded();
            return;
        }

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
            $this->logError("Navigation edit form error: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
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

        if (!$this->navigationTableExists) {
            $this->redirect('/admin/navigation?error=' . urlencode('Navigation menu table not found. Please run the migration first.'));
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

        if (!$this->navigationTableExists) {
            $this->redirect('/admin/navigation?error=' . urlencode('Navigation menu table not found. Please run the migration first.'));
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
     * Update navigation settings (simple category-based)
     */
    public function updateSettings()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/navigation');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/navigation?error=Invalid security token');
            return;
        }

        $navCategories = $this->post('nav_categories', []);
        $navOrders = $this->post('nav_order', []);

        try {
            $db = $this->categoryModel->db;
            $db->beginTransaction();

            // First, set all categories to not show in nav
            $db->query("UPDATE categories SET show_in_nav = 0, nav_order = 0");

            // Then update selected categories
            foreach ($navCategories as $catId) {
                $catId = (int)$catId;
                $order = isset($navOrders[$catId]) ? (int)$navOrders[$catId] : 0;
                
                $stmt = $db->prepare("UPDATE categories SET show_in_nav = 1, nav_order = ? WHERE id = ?");
                $stmt->execute([$order, $catId]);
            }

            $db->commit();
            $this->redirect('/admin/navigation?success=Navigation settings updated successfully');
        } catch (\Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            $this->logError("Navigation update error: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->redirect('/admin/navigation?error=Failed to update navigation settings');
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

        if (!$this->navigationTableExists) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Navigation menu table not found. Please run the migration first.']);
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
