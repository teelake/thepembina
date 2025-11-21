<?php

namespace App\Services;

use App\Core\Helper;

class ReceiptService
{
    public function generate(array $order, array $payments = []): string
    {
        $pdf = new SimplePdf();
        $pdf->addLine('The Pembina Pint and Restaurant', 16);
        $pdf->addLine('Official Receipt', 14);
        $pdf->addSpacing(10);

        $pdf->addLine('Order #: ' . ($order['order_number'] ?? $order['id']));
        $pdf->addLine('Order Date: ' . date('M d, Y g:i A', strtotime($order['created_at'])));
        $pdf->addLine('Customer: ' . ($order['email'] ?? 'Guest'));
        if (!empty($order['phone'])) {
            $pdf->addLine('Phone: ' . $order['phone']);
        }
        $pdf->addLine('Fulfilment: ' . ucfirst($order['order_type']));
        $pdf->addSpacing(10);

        $pdf->addLine('Items', 14);
        foreach ($order['items'] as $item) {
            $summary = sprintf(
                '%sx %s - %s',
                $item['quantity'],
                $item['product_name'],
                Helper::formatCurrency($item['subtotal'])
            );
            $pdf->addLine($summary);
            if (!empty($item['options'])) {
                $pdf->addLine('   ' . strip_tags($item['options']), 10);
            }
        }

        $pdf->addSpacing(8);
        $pdf->addLine('Totals', 14);
        $pdf->addLine('Subtotal: ' . Helper::formatCurrency($order['subtotal']));
        $pdf->addLine('Tax: ' . Helper::formatCurrency($order['tax_amount']));
        if (!empty($order['shipping_amount'])) {
            $pdf->addLine('Delivery: ' . Helper::formatCurrency($order['shipping_amount']));
        }
        $pdf->addLine('Grand Total: ' . Helper::formatCurrency($order['total']), 14);
        $pdf->addSpacing(8);

        $pdf->addLine('Payments', 14);
        if (!empty($payments)) {
            foreach ($payments as $payment) {
                $line = sprintf(
                    '%s (%s) - %s',
                    ucfirst($payment['gateway']),
                    ucfirst($payment['status']),
                    Helper::formatCurrency($payment['amount'])
                );
                $pdf->addLine($line);
                if (!empty($payment['transaction_id'])) {
                    $pdf->addLine('   Txn: ' . $payment['transaction_id'], 10);
                }
            }
        } else {
            $pdf->addLine('Payment record not found.');
        }

        $pdf->addSpacing(12);
        $pdf->addLine('Thank you for dining with us!', 12, 140);

        return $pdf->output();
    }
}


