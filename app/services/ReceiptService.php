<?php

namespace App\Services;

use App\Core\Helper;

class ReceiptService
{
    // Colors matching email exactly - redesigned to match order confirmation email
    // Updated: 2024-12-03 - PDF receipt now matches email design exactly with logo
    private const HEADER_BG = [0.957, 0.643, 0.376]; // #F4A460 (Sandy Brown)
    private const CONTENT_BG = [0.976, 0.976, 0.976]; // #f9f9f9
    private const NOTICE_BG = [0.910, 0.961, 0.914]; // #e8f5e9
    private const NOTICE_BORDER = [0.298, 0.686, 0.314]; // #4caf50
    private const ORDER_DETAILS_BG = [1, 1, 1]; // White
    private const TABLE_HEADER_BG = [0.961, 0.961, 0.961]; // #f5f5f5
    private const TEXT_MAIN = [0.2, 0.2, 0.2]; // #333
    private const TEXT_WHITE = [1, 1, 1]; // White
    private const TEXT_MUTED = [0.4, 0.4, 0.4]; // #666
    private const BORDER_COLOR = [0.867, 0.867, 0.867]; // #ddd
    private const BORDER_LIGHT = [0.933, 0.933, 0.933]; // #eee

    public function generate(array $order, array $payments = []): string
    {
        try {
            $pdf = new SimplePdf();
            $pageWidth = 612; // 8.5in
            $pageHeight = 792; // 11in
            $margin = 40;
            $contentWidth = $pageWidth - ($margin * 2);
            $centerX = $pageWidth / 2;
            
            // Track positions for background drawing
            $positions = [];
            
            // ===== LOGO (Centered at top, 100pt from top) =====
            // Try JPG first (no background), then PNG (will convert with white background)
            $basePath = defined('PUBLIC_PATH') ? PUBLIC_PATH . '/images' : __DIR__ . '/../../public/images';
            $logoPath = null;
            if (file_exists($basePath . '/the-pembina.jpg')) {
                $logoPath = $basePath . '/the-pembina.jpg';
            } elseif (file_exists($basePath . '/logo.jpg')) {
                $logoPath = $basePath . '/logo.jpg';
            } elseif (file_exists($basePath . '/logo.png')) {
                $logoPath = $basePath . '/logo.png';
            }
            
            $logoSize = 100; // Logo size in points
            $logoY = $pageHeight - 30; // 30pt from top of page (minimal top margin)
            
            if ($logoPath && file_exists($logoPath)) {
                $logoX = $centerX - ($logoSize / 2); // Center the logo horizontally
                $pdf->addImage($logoPath, $logoX, $logoY, $logoSize);
            }
            
            // Position cursor after logo with minimal spacing
            $currentY = $logoY - $logoSize - 5; // 5pt spacing after logo (minimal)
            $pdf->setCursor($currentY);
            
            // ===== HEADER (Orange background, white text) =====
            $headerHeight = 40; // Optimized height
            $headerTopY = $currentY;
            $headerBottomY = $headerTopY - $headerHeight;
            $positions['header'] = ['top' => $headerTopY, 'bottom' => $headerBottomY];
            
            // Draw header background FIRST
            $pdf->addRectangle($margin, $headerBottomY, $contentWidth, $headerHeight, self::HEADER_BG, true);
            
            // Header text "Thank you for your order!" (centered in orange header, white, bold)
            // Position text in the center of the header vertically
            $headerTextY = $headerTopY - ($headerHeight / 2) + 4;
            $pdf->setCursor($headerTextY);
            $pdf->addLine('Thank you for your order!', 16, $centerX, self::TEXT_WHITE, 'center');
            
            // Move cursor below header with minimal spacing
            $currentY = $headerBottomY - 5;
            $pdf->setCursor($currentY);
            $contentStartY = $currentY;
            
            // ===== NOTICE BOX (Green background) =====
            $noticeBoxY = $pdf->getCursor();
            $noticeBoxHeight = 55; // Optimized height to fit text without overlap
            $noticePadding = 10;
            $positions['notice'] = ['top' => $noticeBoxY, 'bottom' => $noticeBoxY - $noticeBoxHeight];
            
            // Draw notice background and border FIRST
            $pdf->addRectangle($margin, $noticeBoxY - $noticeBoxHeight, $contentWidth, $noticeBoxHeight, self::NOTICE_BG, true);
            $pdf->addRectangle($margin, $noticeBoxY - $noticeBoxHeight, 3, $noticeBoxHeight, self::NOTICE_BORDER, true);
            
            // Notice text (left-aligned within padding) - goes in GREEN box
            // Using text instead of emoji to avoid encoding issues
            $orderNumber = $order['order_number'] ?? $order['id'];
            $pdf->setCursor($noticeBoxY - $noticePadding);
            $pdf->addLine('This email is your official receipt for Order #' . $orderNumber . '.', 10, $margin + $noticePadding, self::TEXT_MAIN, 'left');
            $pdf->addSpacing(6);
            $pdf->addLine('You can save or print this email, or download a PDF receipt anytime from your account or the Track Order page.', 9, $margin + $noticePadding, self::TEXT_MAIN, 'left');
            
            // Move cursor below notice box
            $currentY = $noticeBoxY - $noticeBoxHeight - 8;
            $pdf->setCursor($currentY);
            
            // "Your order has been confirmed..." text (left-aligned, outside boxes)
            $pdf->addLine('Your order has been confirmed and is being processed.', 11, $margin, self::TEXT_MAIN, 'left');
            $pdf->addSpacing(20); // Increased spacing to ensure ORDER DETAILS is clearly outside green container
            
            // ===== ORDER DETAILS BOX (White background matching email) =====
            $detailsBoxTopY = $pdf->getCursor();
            
            // Draw white background box first (will be behind text)
            // Use a large estimated height to cover all order details
            $estimatedDetailsHeight = 500; // Large enough for most orders - matches email white box
            $pdf->addRectangle($margin, $detailsBoxTopY - $estimatedDetailsHeight, $contentWidth, $estimatedDetailsHeight, self::ORDER_DETAILS_BG, true);
            
            // "Order Details" heading (left-aligned)
            $pdf->setCursor($detailsBoxTopY);
            $pdf->addLine('Order Details', 16, $margin, self::TEXT_MAIN, 'left');
            $pdf->addSpacing(12);
            
            // Order info
            $infoLeftX = $margin + 15;
            $infoRightX = $margin + $contentWidth - 15;
            
            $orderType = ucfirst($order['order_type'] ?? 'pickup');
            $orderDate = date('M d, Y g:i A', strtotime($order['created_at'] ?? 'now'));
            
            $pdf->addTableRow(['Order Number:', $orderNumber], [$infoLeftX, $infoRightX], 12, self::TEXT_MAIN, ['left', 'right']);
            $pdf->addSpacing(8);
            $pdf->addTableRow(['Order Type:', $orderType], [$infoLeftX, $infoRightX], 12, self::TEXT_MAIN, ['left', 'right']);
            $pdf->addSpacing(8);
            $pdf->addTableRow(['Order Date:', $orderDate], [$infoLeftX, $infoRightX], 12, self::TEXT_MAIN, ['left', 'right']);
            $pdf->addSpacing(15);
            
            // ===== ITEMS TABLE =====
            $tableStartY = $pdf->getCursor();
            if (isset($order['items']) && !empty($order['items'])) {
                // Table header background
                $tableHeaderHeight = 30;
                $tableHeaderY = $tableStartY;
                $pdf->addRectangle($margin, $tableHeaderY - $tableHeaderHeight, $contentWidth, $tableHeaderHeight, self::TABLE_HEADER_BG, true);
                
                // Header text
                $pdf->setCursor($tableHeaderY - 10);
                $pdf->addTableRow(
                    ['Item', 'Price'],
                    [$infoLeftX, $infoRightX],
                    12,
                    self::TEXT_MAIN,
                    ['left', 'right']
                );
                
                // Header bottom border
                $pdf->addHorizontalRule($margin, $contentWidth, 1, self::BORDER_COLOR);
                
                $pdf->setCursor($tableHeaderY - $tableHeaderHeight - 8);
                
                // Items rows
                foreach ($order['items'] as $item) {
                    $productName = htmlspecialchars($item['product_name'] ?? 'Unknown Product');
                    $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                    $subtotal = isset($item['subtotal']) ? (float)$item['subtotal'] : 0;
                    
                    $itemText = $productName . ' x ' . $quantity;
                    $priceText = Helper::formatCurrency($subtotal);
                    
                    $pdf->addTableRow([$itemText, $priceText], [$infoLeftX, $infoRightX], 11, self::TEXT_MAIN, ['left', 'right']);
                    $pdf->addHorizontalRule($margin, $contentWidth, 0.5, self::BORDER_LIGHT);
                    $pdf->addSpacing(4);
                }
            }
            
            $pdf->addSpacing(15);
            
            // Totals
            $subtotal = isset($order['subtotal']) ? (float)$order['subtotal'] : 0;
            $taxAmount = isset($order['tax_amount']) ? (float)$order['tax_amount'] : 0;
            $shippingAmount = isset($order['shipping_amount']) ? (float)$order['shipping_amount'] : 0;
            $total = isset($order['total']) ? (float)$order['total'] : ($subtotal + $taxAmount + $shippingAmount);
            
            $pdf->addTableRow(['Subtotal:', Helper::formatCurrency($subtotal)], [$infoLeftX, $infoRightX], 12, self::TEXT_MAIN, ['left', 'right']);
            $pdf->addSpacing(8);
            $pdf->addTableRow(['Tax:', Helper::formatCurrency($taxAmount)], [$infoLeftX, $infoRightX], 12, self::TEXT_MAIN, ['left', 'right']);
            $pdf->addSpacing(8);
            
            if ($shippingAmount > 0) {
                $pdf->addTableRow(['Delivery Fee:', Helper::formatCurrency($shippingAmount)], [$infoLeftX, $infoRightX], 12, self::TEXT_MAIN, ['left', 'right']);
                $pdf->addSpacing(8);
            }
            
            // Total Amount (larger, bold)
            $pdf->addTableRow(['Total Amount:', Helper::formatCurrency($total)], [$infoLeftX, $infoRightX], 18, self::TEXT_MAIN, ['left', 'right']);
            $pdf->addSpacing(10);
            
            $paymentStatus = ucfirst($order['payment_status'] ?? 'pending');
            $status = ucfirst($order['status'] ?? 'pending');
            
            $pdf->addTableRow(['Payment Status:', $paymentStatus], [$infoLeftX, $infoRightX], 12, self::TEXT_MAIN, ['left', 'right']);
            $pdf->addSpacing(8);
            $pdf->addTableRow(['Status:', $status], [$infoLeftX, $infoRightX], 12, self::TEXT_MAIN, ['left', 'right']);
            
            $detailsBoxBottomY = $pdf->getCursor();
            $positions['details'] = ['top' => $detailsBoxTopY, 'bottom' => $detailsBoxBottomY - 10];
            
            // Move cursor properly below order details
            $pdf->setCursor($detailsBoxBottomY);
            $pdf->addSpacing(12);
            
            // "Current Status" and closing message (left-aligned)
            $pdf->addLine('Current Status: ' . $status, 12, $margin, self::TEXT_MAIN, 'left');
            $pdf->addSpacing(6);
            $pdf->addLine("We'll notify you when your order is ready!", 12, $margin, self::TEXT_MAIN, 'left');
            
            $contentEndY = $pdf->getCursor();
            
            // ===== FOOTER =====
            $footerY = 60;
            $pdf->setCursor($footerY);
            $pdf->addLine('The Pembina Pint and Restaurant', 12, $centerX, self::TEXT_MUTED, 'center');
            $pdf->addSpacing(6);
            $pdf->addLine('282 Loren Drive, Morden, Manitoba, Canada', 12, $centerX, self::TEXT_MUTED, 'center');
            
            // Draw content background (light gray) - draw it behind everything
            $contentHeight = $contentStartY - $contentEndY;
            $pdf->addRectangle($margin, $contentEndY, $contentWidth, $contentHeight, self::CONTENT_BG, true);

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
}
