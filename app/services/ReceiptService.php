<?php

namespace App\Services;

use App\Core\Helper;

class ReceiptService
{
    private const HEADER_COLS = [40, 500];
    private const ADDRESS_COLS = [40, 300];
    private const SUMMARY_COLS = [360, 520];
    private const ITEMS_COLS = [40, 240, 320, 400, 480];
    private const PAYMENT_COLS = [40, 320, 420];
    private const THEME_COLOR = [0.957, 0.643, 0.376];
    private const TEXT_MAIN = [0.2, 0.2, 0.2];
    private const TEXT_MUTED = [0.45, 0.45, 0.45];
    private const TEXT_LIGHT = [1, 1, 1];
    private const LINE_COLOR = [0.78, 0.78, 0.78];

    public function generate(array $order, array $payments = []): string
    {
        try {
            $pdf = new SimplePdf();
            $logoPath = defined('PUBLIC_PATH') ? PUBLIC_PATH . '/images/logo.png' : __DIR__ . '/../../public/images/logo.png';
            $currentTop = $pdf->getCursor();
            if (file_exists($logoPath)) {
                $pdf->addImage($logoPath, 450, $currentTop + 40, 100);
            }
            $pdf->addRectangle(40, $currentTop + 28, 70, 10, self::THEME_COLOR);
            $pdf->addRectangle(540, $currentTop + 10, 50, 10, self::THEME_COLOR);

            $businessName = defined('BUSINESS_NAME') ? BUSINESS_NAME : 'The Pembina Pint and Restaurant';
            $businessPhone = defined('BUSINESS_PHONE') ? BUSINESS_PHONE : '';
            $businessEmail = defined('BUSINESS_EMAIL') ? BUSINESS_EMAIL : 'no-reply@thepembina.ca';

            $pdf->addTableRow(['Invoice', ''], self::HEADER_COLS, 24, self::TEXT_MAIN);
            $pdf->addTableRow(['#' . ($order['order_number'] ?? $order['id']), ''], self::HEADER_COLS, 14, self::TEXT_MUTED);
            $pdf->addTableRow([
                'Date: ' . date('M d, Y g:i A', strtotime($order['created_at'])),
                'Support: ' . $businessEmail
            ], self::HEADER_COLS, 10, self::TEXT_MUTED, ['left', 'right']);
            $pdf->addTableRow([
                'Order Type: ' . ucfirst($order['order_type'] ?? 'pickup'),
                !empty($businessPhone) ? 'Phone: ' . $businessPhone : ''
            ], self::HEADER_COLS, 10, self::TEXT_MUTED, ['left', 'right']);
            $pdf->addSpacing(8);
            $pdf->addHorizontalRule(40, 520, 0.6, self::LINE_COLOR);
            $pdf->addSpacing(8);

            $billingAddress = $this->formatAddress(json_decode($order['billing_address'] ?? '[]', true), 'Billing');
            $shippingData = $order['shipping_address'] ? json_decode($order['shipping_address'], true) : null;
            $shippingAddress = $this->formatAddress($shippingData, $order['order_type'] === 'delivery' ? 'Delivery' : 'Pickup');

            $pdf->addTableRow(['Billing Address', 'Shipping Address'], self::ADDRESS_COLS, 12, self::TEXT_MAIN);
            $pdf->addHorizontalRule(40, 520, 0.6, self::LINE_COLOR);
            $maxLines = max(count($billingAddress), count($shippingAddress));
            for ($i = 0; $i < $maxLines; $i++) {
                $pdf->addTableRow([
                    $billingAddress[$i] ?? '',
                    $shippingAddress[$i] ?? ''
                ], self::ADDRESS_COLS, 10, self::TEXT_MUTED);
            }
            $pdf->addSpacing(10);
            $pdf->addHorizontalRule(40, 520, 0.6, self::LINE_COLOR);
            $pdf->addSpacing(8);

            $pdf->addLine('Items', 14, 40, self::TEXT_MAIN);
            $pdf->addSpacing(4);
            $this->addItemsTable($pdf, $order['items'] ?? []);

            $subtotal = isset($order['subtotal']) ? $order['subtotal'] : 0;
            $taxAmount = isset($order['tax_amount']) ? $order['tax_amount'] : 0;
            $shippingAmount = isset($order['shipping_amount']) ? $order['shipping_amount'] : 0;
            $total = isset($order['total']) ? $order['total'] : ($subtotal + $taxAmount + $shippingAmount);
            $discount = isset($order['discount_amount']) ? $order['discount_amount'] : 0;
            $paidAmount = $this->calculatePaidAmount($payments);
            $grandTotal = $total - $discount;
            $totalDue = max(0, $grandTotal - $paidAmount);

            $this->addTotalsTable($pdf, [
                'Subtotal' => Helper::formatCurrency($subtotal),
                'Tax' => Helper::formatCurrency($taxAmount),
                'Delivery' => Helper::formatCurrency($shippingAmount),
                'Discount' => $discount > 0 ? '-' . Helper::formatCurrency($discount) : Helper::formatCurrency(0),
                'Grand Total' => Helper::formatCurrency($grandTotal),
                'Total Amount Paid' => Helper::formatCurrency($paidAmount),
                'Total Due' => Helper::formatCurrency($totalDue),
            ]);

            $pdf->addLine('Payments', 14, 40, self::TEXT_MAIN);
            $pdf->addSpacing(4);
            $this->addPaymentsTable($pdf, $payments);

            $pdf->addSpacing(12);
            $pdf->addHorizontalRule(40, 520, 0.6, self::LINE_COLOR);
            $pdf->addSpacing(6);
            $pdf->addLine('Thank you for choosing ' . $businessName . '!', 12, 120, self::TEXT_MAIN);
            $pdf->addLine('We look forward to serving you again.', 11, 120, self::TEXT_MUTED);
            $pdf->addSpacing(6);
            $pdf->addLine('Please note that depending on availability, your order will be ready within the advised window.', 9, 40, self::TEXT_MUTED);

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
     * Render items table with headers.
     */
    private function addItemsTable(SimplePdf $pdf, array $items): void
    {
        $headerY = $pdf->getCursor() + 18;
        $pdf->addRectangle(40, $headerY, 520, 18, self::THEME_COLOR);
        $pdf->addTableRow(
            ['Product Description', 'Qty', 'Unit Price', 'Discount', 'Total'],
            self::ITEMS_COLS,
            12,
            self::TEXT_LIGHT,
            ['left', 'center', 'right', 'right', 'right']
        );
        $pdf->addHorizontalRule(40, 520, 0.6, self::LINE_COLOR);

        if (empty($items)) {
            $pdf->addTableRow(
                ['No items found in order.', '', '', '', ''],
                self::ITEMS_COLS,
                11,
                self::TEXT_MUTED,
                ['left', 'center', 'right', 'right', 'right']
            );
            $pdf->addSpacing(4);
            $pdf->addHorizontalRule(40, 520, 0.6, self::LINE_COLOR);
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
                    '—',
                    Helper::formatCurrency($subtotal)
                ],
                self::ITEMS_COLS,
                11,
                self::TEXT_MAIN,
                ['left', 'center', 'right', 'right', 'right']
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
                $pdf->addTableRow(
                    ['   ' . strip_tags($options), '', '', '', ''],
                    self::ITEMS_COLS,
                    9,
                    self::TEXT_MUTED,
                    ['left', 'center', 'right', 'right', 'right']
                );
            }
        }

        $pdf->addSpacing(4);
        $pdf->addHorizontalRule(40, 520, 0.6, self::LINE_COLOR);
        $pdf->addSpacing(6);
    }

    /**
     * Render payments information table.
     */
    private function addPaymentsTable(SimplePdf $pdf, array $payments): void
    {
        $columns = self::PAYMENT_COLS;
        $headerY = $pdf->getCursor() + 16;
        $pdf->addRectangle(40, $headerY, 520, 16, self::THEME_COLOR);
        $pdf->addTableRow(['Method', 'Status', 'Amount'], $columns, 12, self::TEXT_LIGHT, ['left', 'center', 'right']);
        $pdf->addHorizontalRule(40, 520, 0.6, self::LINE_COLOR);

        if (empty($payments)) {
            $pdf->addTableRow(
                ['Payment record not found.', '', ''],
                $columns,
                11,
                self::TEXT_MUTED,
                ['left', 'center', 'right']
            );
            $pdf->addSpacing(4);
            $pdf->addHorizontalRule(40, 520, 0.6, self::LINE_COLOR);
            $pdf->addSpacing(6);
            return;
        }

        foreach ($payments as $payment) {
            $gateway = isset($payment['gateway']) ? ucfirst($payment['gateway']) : 'Unknown';
            $status = isset($payment['status']) ? ucfirst($payment['status']) : 'Unknown';
            $amount = isset($payment['amount']) ? Helper::formatCurrency($payment['amount']) : Helper::formatCurrency(0);

            $pdf->addTableRow([$gateway, $status, $amount], $columns, 11, self::TEXT_MAIN, ['left', 'center', 'right']);

            if (!empty($payment['transaction_id'])) {
                $pdf->addTableRow(
                    ['   Txn: ' . $payment['transaction_id'], '', ''],
                    $columns,
                    9,
                    self::TEXT_MUTED,
                    ['left', 'center', 'right']
                );
            }
        }

        $pdf->addSpacing(4);
        $pdf->addHorizontalRule(40, 520, 0.6, self::LINE_COLOR);
        $pdf->addSpacing(6);
    }

    /**
     * Render totals table aligned to right.
     */
    private function addTotalsTable(SimplePdf $pdf, array $rows): void
    {
        foreach ($rows as $label => $value) {
            if ($label === 'Total Due') {
                $highlightY = $pdf->getCursor() + 18;
                $pdf->addRectangle(self::SUMMARY_COLS[0] - 20, $highlightY, 220, 18, self::THEME_COLOR);
                $pdf->addTableRow([$label, $value], self::SUMMARY_COLS, 12, self::TEXT_LIGHT, ['left', 'right']);
            } else {
                $pdf->addTableRow([$label, $value], self::SUMMARY_COLS, 11, self::TEXT_MAIN, ['left', 'right']);
            }
        }
        $pdf->addSpacing(10);
        $pdf->addHorizontalRule(40, 520, 0.6, self::LINE_COLOR);
        $pdf->addSpacing(8);
    }

    /**
     * Format address lines for output.
     */
    private function formatAddress(?array $address, string $fallbackTitle): array
    {
        if (empty($address)) {
            return [$fallbackTitle . ' details not provided.'];
        }

        $lines = [];
        $name = trim(($address['first_name'] ?? '') . ' ' . ($address['last_name'] ?? ''));
        if ($name !== '') {
            $lines[] = $name;
        }
        if (!empty($address['address_line1'])) {
            $lines[] = $address['address_line1'];
        }
        if (!empty($address['address_line2'])) {
            $lines[] = $address['address_line2'];
        }
        $cityLine = trim(($address['city'] ?? '') . ', ' . ($address['province'] ?? '') . ' ' . ($address['postal_code'] ?? ''));
        if (trim($cityLine, ', ') !== '') {
            $lines[] = $cityLine;
        }
        if (!empty($address['country'])) {
            $lines[] = $address['country'];
        }
        if (!empty($address['email'])) {
            $lines[] = $address['email'];
        }
        if (!empty($address['phone'])) {
            $lines[] = 'Phone: ' . $address['phone'];
        }

        if (empty($lines)) {
            $lines[] = $fallbackTitle . ' details not provided.';
        }

        return $lines;
    }

    private function calculatePaidAmount(array $payments): float
    {
        $total = 0;
        foreach ($payments as $payment) {
            $status = strtolower($payment['status'] ?? '');
            if (in_array($status, ['paid', 'completed', 'success', 'approved'], true)) {
                $total += (float)($payment['amount'] ?? 0);
            }
        }
        return $total;
    }
}

