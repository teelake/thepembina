<?php
/**
 * Admin Dashboard Controller
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Core\Database;

class DashboardController extends Controller
{
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin', 'data_entry']);
    }

    /**
     * Dashboard index
     */
    public function index()
    {
        $db = Database::getInstance()->getConnection();
        
        // Get statistics
        $stats = [
            'total_orders' => (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
            'pending_orders' => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
            'total_products' => (int)$db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn(),
            'total_customers' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role_id = 4")->fetchColumn(),
            'today_orders' => (int)$db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
            'today_revenue' => (float)$db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'paid'")->fetchColumn()
        ];
        
        // Recent orders
        $orderModel = new Order();
        $recentOrders = $orderModel->findAll([], 'created_at DESC', 10);
        
        // Top products
        $topProducts = $db->query("
            SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as revenue
            FROM order_items oi
            INNER JOIN products p ON oi.product_id = p.id
            INNER JOIN orders o ON oi.order_id = o.id
            WHERE o.payment_status = 'paid'
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT 5
        ")->fetchAll();
        
        $data = [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'page_title' => 'Dashboard',
            'current_page' => 'dashboard'
        ];
        
        $this->render('admin/dashboard/index', $data);
    }
}

