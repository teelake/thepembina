<?php

namespace App\Services;

use App\Core\Helper;

class ReceiptService
{
    private const HEADER_COLS = [40, 500];
    private const ADDRESS_COLS = [40, 300];
    private const SUMMARY_COLS = [360, 520];
    private const ITEMS_COLS = [40, 320, 420, 520];
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
            
            $businessName = defined('BUSINESS_NAME') ? BUSINESS_NAME : 'The Pembina Pint and Restaurant';
            $businessPhone = defined('BUSINESS_PHONE') ? BUSINESS_PHONE : '';
            $businessEmail = defined('BUSINESS_EMAIL') ? BUSINESS_EMAIL : 'no-reply@thepembina.ca';
            
            // Simple centered design matching payment success page
            $centerX = 306; // Center of page (612/2)
            $startY = 50;
            $pdf->setCursor($startY);
            
            // Logo at top center (smaller, like payment success)
            if (file_exists($logoPath)) {
                $logoX = $centerX - 40; // Center logo (80px wide / 2)
                $pdf->addImage($logoPath, $logoX, $startY, 80);
                $pdf->addSpacing(100);
            }
            
            // Success checkmark circle (exactly like payment success page)
            $circleY = $pdf->getCursor();
            $circleX = $centerX - 32; // Center circle (64px wide / 2)
            $pdf->addRectangle($circleX, $circleY, 64, 64, [0.2, 0.7, 0.3], true); // Green circle background (square will look circular)
            // Add checkmark - need to position it in the center of the circle
            // The circle is at Y, so checkmark should be at Y + (64/2) - (fontSize/2) approximately
            $checkmarkY = $circleY + 20; // Approximate center of 64px circle
            $pdf->setCursor($checkmarkY);
            $pdf->addLine('✓', 36, $centerX, [1, 1, 1], 'center'); // White checkmark
            $pdf->setCursor($circleY + 64); // Move cursor past the circle
            $pdf->addSpacing(25);
            
            // Simple title - "Invoice" (like "Payment Successful!")
            $pdf->addLine('Invoice', 24, $centerX, self::TEXT_MAIN, 'center');
            $pdf->addSpacing(8);
            $pdf->addLine('Thank you for your order', 12, $centerX, self::TEXT_MUTED, 'center');
            $pdf->addSpacing(25);
            
            // Order details in simple gray box (exactly like payment success page)
            $detailsY = $pdf->getCursor();
            $boxPadding = 20;
            $boxWidth = 480;
            $boxX = $centerX - ($boxWidth / 2);
            
            // Calculate height needed
            $detailsHeight = 100; // Base height for 4-5 items
            
            $pdf->addRectangle($boxX, $detailsY, $boxWidth, $detailsHeight, [0.96, 0.96, 0.96]); // Light gray background
            
            // Format order number
            $orderNumber = $order['order_number'] ?? $order['id'];
            if (is_numeric($orderNumber)) {
                $orderNumber = str_pad($orderNumber, 10, '0', STR_PAD_LEFT);
            }
            
            // Format date
            $orderDate = $order['created_at'] ?? date('Y-m-d H:i:s');
            if (is_string($orderDate)) {
                $timestamp = strtotime($orderDate);
                $formattedDate = $timestamp !== false ? date('M d, Y g:i A', $timestamp) : $orderDate;
            } else {
                $formattedDate = date('M d, Y g:i A');
            }
            
            $detailsStartY = $detailsY + $boxPadding;
            $pdf->setCursor($detailsStartY);
            
            // Simple key-value pairs (centered layout, like payment success page)
            $leftX = $boxX + $boxPadding;
            $rightX = $boxX + $boxWidth - $boxPadding;
            $lineSpacing = 18;
            
            $pdf->addTableRow(['Order Number:', $orderNumber], [$leftX, $rightX], 12, self::TEXT_MAIN, ['left', 'right']);
            $pdf->addSpacing($lineSpacing - 12);
            $pdf->addTableRow(['Order Type:', ucfirst($order['order_type'] ?? 'pickup')], [$leftX, $rightX], 12, self::TEXT_MAIN, ['left', 'right']);
            $pdf->addSpacing($lineSpacing - 12);
            
            // Total Amount - highlighted like payment success page
            $subtotal = isset($order['subtotal']) ? (float)$order['subtotal'] : 0;
            $taxAmount = isset($order['tax_amount']) ? (float)$order['tax_amount'] : 0;
            $shippingAmount = isset($order['shipping_amount']) ? (float)$order['shipping_amount'] : 0;
            $total = isset($order['total']) ? (float)$order['total'] : ($subtotal + $taxAmount + $shippingAmount);
            $discount = isset($order['discount_amount']) ? (float)$order['discount_amount'] : 0;
            $grandTotal = $total - $discount;
            
            $pdf->addTableRow(['Total Amount:', Helper::formatCurrency($grandTotal)], [$leftX, $rightX], 14, self::TEXT_MAIN, ['left', 'right']);
            $pdf->addSpacing($lineSpacing - 12);
            
            $paymentStatus = isset($order['payment_status']) ? ucfirst($order['payment_status']) : 'Pending';
            $statusColor = strtolower($paymentStatus) === 'paid' ? [0.2, 0.7, 0.3] : [0.85, 0.6, 0.2];
            $pdf->addTableRow(['Payment Status:', $paymentStatus], [$leftX, $rightX], 12, $statusColor, ['left', 'right']);
            
            $pdf->setCursor($detailsY + $detailsHeight);
            $pdf->addSpacing(25);

            // Items section - very simple, minimal design
            $pdf->addLine('Order Items', 14, $centerX, self::TEXT_MAIN, 'center');
            $pdf->addSpacing(12);
            $this->addItemsTable($pdf, $order['items'] ?? []);
            
            $pdf->addSpacing(15);
            
            // Simple footer - centered like payment success page
            $pdf->addLine('Thank you for choosing ' . $businessName . '!', 12, $centerX, self::TEXT_MAIN, 'center');
            $pdf->addSpacing(6);
            $pdf->addLine('We look forward to serving you again.', 10, $centerX, self::TEXT_MUTED, 'center');

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
     * Render items table - very simple design matching payment success page
     */
    private function addItemsTable(SimplePdf $pdf, array $items): void
    {
        $centerX = 306;
        $leftMargin = 80;
        $rightMargin = 520;
        
        // Very simple header - no background, just text
        $pdf->addTableRow(
            ['Item', 'Qty', 'Price', 'Total'],
            [$leftMargin, 200, 400, $rightMargin],
            10,
            self::TEXT_MUTED,
            ['left', 'center', 'right', 'right']
        );
        $pdf->addHorizontalRule($leftMargin, $rightMargin, 0.5, self::LINE_COLOR);
        $pdf->addSpacing(8);

        if (empty($items)) {
            $pdf->addTableRow(
                ['No items found in order.', '', '', ''],
                [$leftMargin, 200, 400, $rightMargin],
                11,
                self::TEXT_MUTED,
                ['left', 'center', 'right', 'right']
            );
            $pdf->addSpacing(8);
            return;
        }

        foreach ($items as $item) {
            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
            $productName = isset($item['product_name']) ? $item['product_name'] : 'Unknown Product';
            $price = isset($item['price']) ? (float)$item['price'] : 0;
            
            // Calculate subtotal - ensure we have a valid value
            $subtotal = isset($item['subtotal']) ? (float)$item['subtotal'] : 0;
            if ($subtotal == 0 && $price > 0) {
                $subtotal = $price * $quantity;
            }
            // If price is 0 but subtotal exists, calculate price from subtotal
            if ($price == 0 && $subtotal > 0 && $quantity > 0) {
                $price = $subtotal / $quantity;
            }
            
            // Ensure we have valid numeric values
            $price = max(0, $price);
            $subtotal = max(0, $subtotal);

            $pdf->addTableRow(
                [
                    $productName,
                    (string)$quantity,
                    Helper::formatCurrency($price),
                    Helper::formatCurrency($subtotal)
                ],
                [$leftMargin, 200, 400, $rightMargin],
                11,
                self::TEXT_MAIN,
                ['left', 'center', 'right', 'right']
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
                    ['   ' . strip_tags($options), '', '', ''],
                    [$leftMargin, 200, 400, $rightMargin],
                    9,
                    self::TEXT_MUTED,
                    ['left', 'center', 'right', 'right']
                );
            }
            
            $pdf->addSpacing(4);
        }

        $pdf->addSpacing(8);
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
            if ($label === 'Total' || $label === 'Total Due') {
                $highlightY = $pdf->getCursor() + 18;
                $pdf->addRectangle(self::SUMMARY_COLS[0] - 20, $highlightY, 200, 18, self::THEME_COLOR);
                $pdf->addTableRow([$label, $value], self::SUMMARY_COLS, 13, self::TEXT_LIGHT, ['left', 'right']);
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

