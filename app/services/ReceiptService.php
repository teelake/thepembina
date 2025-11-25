<?php

namespace App\Services;

use App\Core\Helper;

class ReceiptService
{
    private const LABEL_COL = 40;
    private const VALUE_COL = 250;
    private const ITEMS_COLS = [40, 330, 400, 480];

    public function generate(array $order, array $payments = []): string
    {
        try {
            $pdf = new SimplePdf();
            $logoPath = defined('PUBLIC_PATH') ? PUBLIC_PATH . '/images/logo.png' : __DIR__ . '/../../public/images/logo.png';
            if (file_exists($logoPath)) {
                $pdf->addImage($logoPath, 40, 720, 110);
            }

            $businessName = defined('BUSINESS_NAME') ? BUSINESS_NAME : 'The Pembina Pint and Restaurant';
            $businessAddress = defined('BUSINESS_ADDRESS') ? BUSINESS_ADDRESS : '282 Loren Drive, Morden, Manitoba, Canada';
            $businessPhone = defined('BUSINESS_PHONE') ? BUSINESS_PHONE : '';
            $businessEmail = defined('BUSINESS_EMAIL') ? BUSINESS_EMAIL : 'no-reply@thepembina.ca';

            $pdf->addLine($businessName, 18, 170);
            $pdf->addLine('Official Receipt', 14, 170);
            $pdf->addLine($businessAddress, 11, 170);
            if (!empty($businessPhone)) {
                $pdf->addLine('Phone: ' . $businessPhone, 11, 170);
            }
            $pdf->addLine('Email: ' . $businessEmail, 11, 170);
            $pdf->addSpacing(12);
            $pdf->addHorizontalRule();
            $pdf->addSpacing(8);

            $pdf->addLine('Order Details', 14);
            $pdf->addSpacing(4);
            $this->addKeyValueRows($pdf, [
                'Order #' => $order['order_number'] ?? $order['id'],
                'Order Date' => date('M d, Y g:i A', strtotime($order['created_at'])),
                'Customer Email' => $order['email'] ?? 'Guest',
                'Customer Phone' => $order['phone'] ?? 'N/A',
                'Fulfilment' => ucfirst($order['order_type'] ?? 'pickup')
            ]);

            $pdf->addLine('Items', 14);
            $pdf->addSpacing(4);
            $this->addItemsTable($pdf, $order['items'] ?? []);

            $pdf->addLine('Totals', 14);
            $pdf->addSpacing(4);
            $subtotal = isset($order['subtotal']) ? $order['subtotal'] : 0;
            $taxAmount = isset($order['tax_amount']) ? $order['tax_amount'] : 0;
            $shippingAmount = isset($order['shipping_amount']) ? $order['shipping_amount'] : 0;
            $total = isset($order['total']) ? $order['total'] : ($subtotal + $taxAmount + $shippingAmount);

            $this->addKeyValueRows($pdf, [
                'Subtotal' => Helper::formatCurrency($subtotal),
                'Tax' => Helper::formatCurrency($taxAmount),
                'Delivery' => Helper::formatCurrency($shippingAmount),
                'Grand Total' => Helper::formatCurrency($total),
            ], [self::LABEL_COL, 420]);

            $pdf->addLine('Payments', 14);
            $pdf->addSpacing(4);
            $this->addPaymentsTable($pdf, $payments);

            $pdf->addSpacing(12);
            $pdf->addHorizontalRule();
            $pdf->addSpacing(6);
            $pdf->addLine('Thank you for choosing ' . $businessName . '!', 12, 120);
            $pdf->addLine('We look forward to serving you again.', 11, 120);

            return $pdf->output();
        } catch (\Exception $e) {
            $errorLogFile = defined('ROOT_PATH') ? ROOT_PATH . '/php-error.log' : __DIR__ . '/../../php-error.log';
            $timestamp = date('Y-m-d H:i:s');
            $url = $_SERVER['REQUEST_URI'] ?? 'CLI';
            $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';

            $logMessage = sprintf(
                "[%s] RECEIPT SERVICE ERROR: %s | File: %s | Line: %d | URL: %s | Method: %s | IP: %s\n",
                $timestamp,
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $url,
                $method,
                $ip
            );
            $logMessage .= sprintf("Stack Trace:\n%s\n", $e->getTraceAsString());

            @file_put_contents($errorLogFile, $logMessage, FILE_APPEND | LOCK_EX);
            throw $e;
        }
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

    /**
     * Render label/value rows as a two-column table.
     */
    private function addKeyValueRows(SimplePdf $pdf, array $rows, array $positions = [self::LABEL_COL, self::VALUE_COL]): void
    {
        foreach ($rows as $label => $value) {
            $pdf->addTableRow(
                [$label . ':', (string)$value],
                $positions
            );
        }
        $pdf->addSpacing(4);
        $pdf->addHorizontalRule();
        $pdf->addSpacing(6);
    }

    /**
     * Render items table with headers.
     */
    private function addItemsTable(SimplePdf $pdf, array $items): void
    {
        $pdf->addTableRow(['Item', 'Qty', 'Price', 'Subtotal'], self::ITEMS_COLS, 12);
        $pdf->addHorizontalRule(40, 520);

        if (empty($items)) {
            $pdf->addTableRow(['No items found in order.', '', '', ''], self::ITEMS_COLS);
            $pdf->addSpacing(4);
            $pdf->addHorizontalRule();
            $pdf->addSpacing(6);
            return;
        }

        foreach ($items as $item) {
            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
            $productName = isset($item['product_name']) ? $item['product_name'] : 'Unknown Product';
            $price = isset($item['price']) ? $item['price'] : ($item['subtotal'] ?? 0);
            $subtotal = isset($item['subtotal']) ? $item['subtotal'] : $price * $quantity;

            $pdf->addTableRow(
                [
                    $productName,
                    (string)$quantity,
                    Helper::formatCurrency($price),
                    Helper::formatCurrency($subtotal)
                ],
                self::ITEMS_COLS
            );

            if (!empty($item['options'])) {
                $options = $item['options'];
                if (is_string($options) && $this->looksLikeJson($options)) {
                    $decoded = json_decode($options, true);
                    if (is_array($decoded)) {
                        $options = implode(', ', array_map(
                            function ($key, $value) {
                                return ucfirst($key) . ': ' . $value;
                            },
                            array_keys($decoded),
                            $decoded
                        ));
                    }
                }
                $pdf->addTableRow(['   ' . strip_tags($options), '', '', ''], self::ITEMS_COLS, 9);
            }
        }

        $pdf->addSpacing(4);
        $pdf->addHorizontalRule();
        $pdf->addSpacing(6);
    }

    /**
     * Render payments information table.
     */
    private function addPaymentsTable(SimplePdf $pdf, array $payments): void
    {
        $columns = [self::LABEL_COL, 320, 420];
        $pdf->addTableRow(['Method', 'Status', 'Amount'], $columns, 12);
        $pdf->addHorizontalRule(40, 520);

        if (empty($payments)) {
            $pdf->addTableRow(['Payment record not found.', '', ''], $columns);
            $pdf->addSpacing(4);
            $pdf->addHorizontalRule();
            $pdf->addSpacing(6);
            return;
        }

        foreach ($payments as $payment) {
            $gateway = isset($payment['gateway']) ? ucfirst($payment['gateway']) : 'Unknown';
            $status = isset($payment['status']) ? ucfirst($payment['status']) : 'Unknown';
            $amount = isset($payment['amount']) ? Helper::formatCurrency($payment['amount']) : Helper::formatCurrency(0);

            $pdf->addTableRow([$gateway, $status, $amount], $columns);

            if (!empty($payment['transaction_id'])) {
                $pdf->addTableRow(['   Txn: ' . $payment['transaction_id'], '', ''], $columns, 9);
            }
        }

        $pdf->addSpacing(4);
        $pdf->addHorizontalRule();
        $pdf->addSpacing(6);
    }
}

