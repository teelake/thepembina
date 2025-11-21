<?php

namespace App\Services;

use App\Core\Helper;

class ReceiptService
{
    public function generate(array $order, array $payments = []): string
    {
        $pdf = new SimplePdf();
        $logoPath = PUBLIC_PATH . '/images/logo.png';
        if (file_exists($logoPath)) {
            $pdf->addImage($logoPath, 40, 720, 110);
        }

        $pdf->addLine(BUSINESS_NAME, 18, 170);
        $pdf->addLine('Official Receipt', 14, 170);
        $pdf->addLine(BUSINESS_ADDRESS, 11, 170);
        if (!empty(BUSINESS_PHONE)) {
            $pdf->addLine('Phone: ' . BUSINESS_PHONE, 11, 170);
        }
        $pdf->addLine('Email: ' . BUSINESS_EMAIL, 11, 170);
        $pdf->addSpacing(20);

        $pdf->addLine('Order Details', 14);
        $pdf->addLine('Order #: ' . ($order['order_number'] ?? $order['id']));
        $pdf->addLine('Order Date: ' . date('M d, Y g:i A', strtotime($order['created_at'])));
        $pdf->addLine('Customer Email: ' . ($order['email'] ?? 'Guest'));
        if (!empty($order['phone'])) {
            $pdf->addLine('Customer Phone: ' . $order['phone']);
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
            $pdf->addLine($summary, 12, 50);
            if (!empty($item['options'])) {
                $options = $item['options'];
                if ($this->looksLikeJson($options)) {
                    $decoded = json_decode($options, true);
                    if (is_array($decoded)) {
                        $options = implode(', ', array_map(
                            fn($key, $value) => ucfirst($key) . ': ' . $value,
                            array_keys($decoded),
                            $decoded
                        ));
                    }
                }
                $pdf->addLine('   ' . strip_tags($options), 10, 50);
            }
        }

        $pdf->addSpacing(8);
        $pdf->addLine('Totals', 14);
        $pdf->addLine('Subtotal: ' . Helper::formatCurrency($order['subtotal']), 12, 50);
        $pdf->addLine('Tax: ' . Helper::formatCurrency($order['tax_amount']), 12, 50);
        if (!empty($order['shipping_amount'])) {
            $pdf->addLine('Delivery: ' . Helper::formatCurrency($order['shipping_amount']), 12, 50);
        }
        $pdf->addLine('Grand Total: ' . Helper::formatCurrency($order['total']), 14, 50);
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
                $pdf->addLine($line, 12, 50);
                if (!empty($payment['transaction_id'])) {
                    $pdf->addLine('   Txn: ' . $payment['transaction_id'], 10, 50);
                }
            }
        } else {
            $pdf->addLine('Payment record not found.', 12, 50);
        }

        $pdf->addSpacing(12);
        $pdf->addLine('Thank you for choosing ' . BUSINESS_NAME . '!', 12, 120);
        $pdf->addLine('We look forward to serving you again.', 11, 120);

        return $pdf->output();
    }

    public function getFilename(array $order): string
    {
        return 'receipt-' . ($order['order_number'] ?? $order['id']) . '.pdf';
    }

    private function looksLikeJson(string $value): bool
    {
        $value = trim($value);
        if ($value === '') {
            return false;
        }
        return ($value[0] === '{' && substr($value, -1) === '}')
            || ($value[0] === '[' && substr($value, -1) === ']');
    }
}

