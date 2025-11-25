<?php
/**
 * Admin Transaction Controller
 * Handles payment/transaction history reports
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Payment;
use App\Core\Helper;

class TransactionController extends Controller
{
    private $paymentModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin']);
        $this->paymentModel = new Payment();
    }

    /**
     * Transaction/Payment history report
     */
    public function index()
    {
        $filters = [
            'gateway' => $this->get('gateway'),
            'status' => $this->get('status'),
            'from' => $this->get('from'),
            'to' => $this->get('to'),
            'transaction_id' => $this->get('transaction_id'),
            'order_number' => $this->get('order_number'),
            'email' => $this->get('email')
        ];

        $page = (int)($this->get('page', 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $transactions = $this->paymentModel->getAllWithOrders($filters, 'p.created_at DESC', $limit, $offset);
        $statistics = $this->paymentModel->getStatistics($filters);
        
        // Get total count for pagination
        $allTransactions = $this->paymentModel->getAllWithOrders($filters);
        $totalTransactions = count($allTransactions);
        $totalPages = ceil($totalTransactions / $limit);

        // Get unique gateways for filter dropdown
        $gateways = $this->paymentModel->findAll([], 'gateway');
        $uniqueGateways = array_unique(array_column($gateways, 'gateway'));

        $this->render('admin/transactions/index', [
            'transactions' => $transactions,
            'statistics' => $statistics,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalTransactions' => $totalTransactions,
            'gateways' => $uniqueGateways,
            'page_title' => 'Transaction History',
            'current_page' => 'transactions',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    /**
     * Export transactions to CSV
     */
    public function export()
    {
        $filters = [
            'gateway' => $this->get('gateway'),
            'status' => $this->get('status'),
            'from' => $this->get('from'),
            'to' => $this->get('to'),
            'transaction_id' => $this->get('transaction_id'),
            'order_number' => $this->get('order_number'),
            'email' => $this->get('email')
        ];

        $transactions = $this->paymentModel->getAllWithOrders($filters, 'p.created_at DESC');

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="transactions_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV Headers
        $headers = [
            'Transaction ID',
            'Order Number',
            'Customer Email',
            'Gateway',
            'Amount',
            'Currency',
            'Status',
            'Order Status',
            'Date'
        ];
        fputcsv($output, $headers);

        // Write transaction data
        foreach ($transactions as $transaction) {
            fputcsv($output, [
                $transaction['transaction_id'],
                $transaction['order_number'] ?? 'N/A',
                $transaction['email'] ?? 'N/A',
                $transaction['gateway'],
                $transaction['amount'],
                $transaction['currency'],
                ucfirst($transaction['status']),
                ucfirst($transaction['order_status'] ?? 'N/A'),
                date('Y-m-d H:i:s', strtotime($transaction['created_at']))
            ]);
        }

        fclose($output);
        exit;
    }
}

