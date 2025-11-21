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

        // Trend data (last 12 months)
        $trendRows = $db->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month_key,
                   DATE_FORMAT(created_at, '%b %Y') as month_label,
                   SUM(total) as revenue,
                   COUNT(*) as orders
            FROM orders
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
            GROUP BY month_key, month_label
        ")->fetchAll();

        $trendMap = [];
        foreach ($trendRows as $row) {
            $trendMap[$row['month_key']] = [
                'label' => $row['month_label'],
                'revenue' => (float)$row['revenue'],
                'orders' => (int)$row['orders']
            ];
        }

        $labels = [];
        $revenuePoints = [];
        $orderPoints = [];
        $current = new \DateTimeImmutable('first day of this month');
        for ($i = 11; $i >= 0; $i--) {
            $month = $current->modify("-{$i} months");
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $revenuePoints[] = isset($trendMap[$key]) ? round($trendMap[$key]['revenue'], 2) : 0;
            $orderPoints[] = isset($trendMap[$key]) ? $trendMap[$key]['orders'] : 0;
        }

        $orderTypeRows = $db->query("
            SELECT order_type, COUNT(*) as total
            FROM orders
            GROUP BY order_type
        ")->fetchAll();

        $orderTypeLabels = [];
        $orderTypeCounts = [];
        foreach ($orderTypeRows as $row) {
            $orderTypeLabels[] = ucfirst($row['order_type'] ?? 'unknown');
            $orderTypeCounts[] = (int)$row['total'];
        }

        $chartData = [
            'labels' => $labels,
            'revenue' => $revenuePoints,
            'orders' => $orderPoints,
            'orderTypeLabels' => $orderTypeLabels,
            'orderTypeCounts' => $orderTypeCounts
        ];

        $data = [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'chartData' => $chartData,
            'page_title' => 'Dashboard',
            'current_page' => 'dashboard'
        ];
        
        $this->render('admin/dashboard/index', $data);
    }
}

