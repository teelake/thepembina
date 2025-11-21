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
        
        $rangeParam = $this->get('range', '12m');
        $rangeOptions = [
            '7d' => ['label' => '7 Days', 'interval' => 'day', 'length' => 7],
            '30d' => ['label' => '30 Days', 'interval' => 'day', 'length' => 30],
            '90d' => ['label' => '90 Days', 'interval' => 'day', 'length' => 90],
            '12m' => ['label' => '12 Months', 'interval' => 'month', 'length' => 12],
        ];
        if (!isset($rangeOptions[$rangeParam])) {
            $rangeParam = '12m';
        }
        $rangeConfig = $rangeOptions[$rangeParam];

        $endDate = new \DateTimeImmutable('today 23:59:59');
        if ($rangeConfig['interval'] === 'day') {
            $startDate = $endDate->modify('-' . ($rangeConfig['length'] - 1) . ' days')->setTime(0, 0, 0);
        } else {
            $startDate = $endDate->modify('first day of this month')->modify('-' . ($rangeConfig['length'] - 1) . ' months')->setTime(0, 0, 0);
        }

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

        // Trend data by selected range
        if ($rangeConfig['interval'] === 'day') {
            $trendStmt = $db->prepare("
                SELECT DATE(created_at) as bucket,
                       SUM(total) as revenue,
                       COUNT(*) as orders
                FROM orders
                WHERE created_at BETWEEN :start AND :end
                GROUP BY bucket
                ORDER BY bucket
            ");
        } else {
            $trendStmt = $db->prepare("
                SELECT DATE_FORMAT(created_at, '%Y-%m') as bucket,
                       SUM(total) as revenue,
                       COUNT(*) as orders
                FROM orders
                WHERE created_at BETWEEN :start AND :end
                GROUP BY bucket
                ORDER BY bucket
            ");
        }
        $trendStmt->execute([
            'start' => $startDate->format('Y-m-d H:i:s'),
            'end' => $endDate->format('Y-m-d H:i:s')
        ]);
        $trendRows = $trendStmt->fetchAll();

        $trendMap = [];
        foreach ($trendRows as $row) {
            $trendMap[$row['bucket']] = [
                'revenue' => (float)$row['revenue'],
                'orders' => (int)$row['orders']
            ];
        }

        $labels = [];
        $revenuePoints = [];
        $orderPoints = [];
        if ($rangeConfig['interval'] === 'day') {
            $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate->modify('+1 day'));
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $labels[] = $date->format('M d');
                $revenuePoints[] = isset($trendMap[$key]) ? round($trendMap[$key]['revenue'], 2) : 0;
                $orderPoints[] = isset($trendMap[$key]) ? $trendMap[$key]['orders'] : 0;
            }
        } else {
            $period = [];
            $current = $startDate;
            for ($i = 0; $i < $rangeConfig['length']; $i++) {
                $period[] = $current->modify("+{$i} months");
            }
            foreach ($period as $month) {
                $key = $month->format('Y-m');
                $labels[] = $month->format('M Y');
                $revenuePoints[] = isset($trendMap[$key]) ? round($trendMap[$key]['revenue'], 2) : 0;
                $orderPoints[] = isset($trendMap[$key]) ? $trendMap[$key]['orders'] : 0;
            }
        }

        $orderTypeStmt = $db->prepare("
            SELECT order_type, COUNT(*) as total
            FROM orders
            WHERE created_at BETWEEN :start AND :end
            GROUP BY order_type
        ");
        $orderTypeStmt->execute([
            'start' => $startDate->format('Y-m-d H:i:s'),
            'end' => $endDate->format('Y-m-d H:i:s')
        ]);
        $orderTypeRows = $orderTypeStmt->fetchAll();

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
            'orderTypeCounts' => $orderTypeCounts,
            'rangeLabel' => $rangeOptions[$rangeParam]['label'],
            'range' => $rangeParam
        ];

        $data = [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'chartData' => $chartData,
            'range' => $rangeParam,
            'rangeOptions' => $rangeOptions,
            'page_title' => 'Dashboard',
            'current_page' => 'dashboard'
        ];
        
        $this->render('admin/dashboard/index', $data);
    }
}

