<?php
/**
 * Email Class
 * Handles email sending via SMTP
 */

namespace App\Core;

class Email
{
    /**
     * Send email
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email body (HTML)
     * @param string $fromEmail Sender email
     * @param string $fromName Sender name
     * @return bool
     */
    public static function send($to, $subject, $message, $fromEmail = null, $fromName = null, array $attachments = [])
    {
        $fromEmail = $fromEmail ?? (defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'no-reply@thepembina.ca');
        $fromName = $fromName ?? (defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'The Pembina Pint and Restaurant');
        
        // Get SMTP settings from database or constants
        $smtpHost = Helper::getSetting('smtp_host', defined('SMTP_HOST') ? SMTP_HOST : '');
        $smtpUser = Helper::getSetting('smtp_user', defined('SMTP_USER') ? SMTP_USER : '');
        
        // Use PHP mail() function if SMTP not configured
        if (empty($smtpHost) || empty($smtpUser)) {
            self::logEmail("Email: Using PHP mail() - SMTP not configured. To: {$to}, Subject: {$subject}");
            $headerData = self::buildMailHeaders($fromName, $fromEmail, $attachments);
            $body = self::buildBody($message, $attachments, $headerData['boundary']);
            $headerString = $headerData['headers'];
            $result = @mail($to, $subject, $body, $headerString);
            $status = $result ? 'SUCCESS' : 'FAILED';
            self::logEmail("Email sent via PHP mail(): {$status} | To: {$to}, Subject: {$subject}");
            if (!$result) {
                $error = error_get_last();
                if ($error) {
                    self::logEmail("PHP mail() error: {$error['message']} in {$error['file']} on line {$error['line']}");
                }
            }
            return $result;
        }
        
        // Use SMTP if configured
        self::logEmail("Email: Using SMTP. To: {$to}, Subject: {$subject}");
        $result = self::sendSMTP($to, $subject, $message, $fromEmail, $fromName, $attachments);
        $status = $result ? 'SUCCESS' : 'FAILED';
        self::logEmail("Email sent via SMTP: {$status} | To: {$to}, Subject: {$subject}");
        return $result;
    }
    
    /**
     * Send email via SMTP
     * 
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param string $fromEmail
     * @param string $fromName
     * @return bool
     */
    private static function sendSMTP($to, $subject, $message, $fromEmail, $fromName, array $attachments = [])
    {
        // Simple SMTP implementation using socket
        // For production, consider using PHPMailer or SwiftMailer
        
        $smtpHost = defined('SMTP_HOST') ? SMTP_HOST : Helper::getSetting('smtp_host', '');
        $smtpPort = defined('SMTP_PORT') ? SMTP_PORT : (int)Helper::getSetting('smtp_port', 587);
        $smtpUser = defined('SMTP_USER') ? SMTP_USER : Helper::getSetting('smtp_user', '');
        $smtpPass = defined('SMTP_PASS') ? SMTP_PASS : Helper::getSetting('smtp_pass', '');
        
        $smtp = @fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
        
        if (!$smtp) {
            self::logEmail("SMTP Connection Error: {$errstr} ({$errno}) | Host: {$smtpHost}, Port: {$smtpPort}");
            return false;
        }
        
        $response = fgets($smtp, 515);
        
        // EHLO
        fputs($smtp, "EHLO " . $smtpHost . "\r\n");
        $response = fgets($smtp, 515);
        
        // Start TLS if available
        if (strpos($response, 'STARTTLS') !== false) {
            fputs($smtp, "STARTTLS\r\n");
            $response = fgets($smtp, 515);
            stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fputs($smtp, "EHLO " . $smtpHost . "\r\n");
            $response = fgets($smtp, 515);
        }
        
        // Auth
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = fgets($smtp, 515);
        
        fputs($smtp, base64_encode($smtpUser) . "\r\n");
        $response = fgets($smtp, 515);
        
        fputs($smtp, base64_encode($smtpPass) . "\r\n");
        $response = fgets($smtp, 515);
        
        if (strpos($response, '235') === false) {
            self::logEmail("SMTP Authentication failed: {$response} | User: {$smtpUser}, Host: {$smtpHost}");
            @fclose($smtp);
            return false;
        }
        
        // Mail from
        fputs($smtp, "MAIL FROM: <{$fromEmail}>\r\n");
        $response = fgets($smtp, 515);
        
        // RCPT to
        fputs($smtp, "RCPT TO: <{$to}>\r\n");
        $response = fgets($smtp, 515);
        
        // Data
        fputs($smtp, "DATA\r\n");
        $response = fgets($smtp, 515);
        
        $headerData = self::buildMailHeaders($fromName, $fromEmail, $attachments, $to, $subject);
        $body = self::buildBody($message, $attachments, $headerData['boundary']);
        $payload  = $headerData['smtp_headers'];
        $payload .= "\r\n" . $body . "\r\n.\r\n";

        fputs($smtp, $payload);
        $response = fgets($smtp, 515);
        
        // Quit
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);
        
        return strpos($response, '250') !== false;
    }
    
    /**
     * Send order confirmation email
     * 
     * @param array $order
     * @param array $attachments Optional attachments (e.g., receipt PDF)
     * @return bool
     */
    public static function sendOrderConfirmation($order, array $attachments = [])
    {
        $subject = "Order Confirmation - {$order['order_number']}";
        
        // Build order items list
        $itemsHtml = '';
        if (isset($order['items']) && !empty($order['items'])) {
            $itemsHtml = '<table style="width: 100%; border-collapse: collapse; margin: 15px 0;">';
            $itemsHtml .= '<tr style="background-color: #f5f5f5;"><th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Item</th><th style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">Price</th></tr>';
            foreach ($order['items'] as $item) {
                $itemsHtml .= '<tr>';
                $itemsHtml .= '<td style="padding: 8px; border-bottom: 1px solid #eee;">' . htmlspecialchars($item['product_name']) . ' x ' . $item['quantity'] . '</td>';
                $itemsHtml .= '<td style="padding: 8px; text-align: right; border-bottom: 1px solid #eee;">' . Helper::formatCurrency($item['subtotal']) . '</td>';
                $itemsHtml .= '</tr>';
            }
            $itemsHtml .= '</table>';
        }
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #F4A460; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .order-details { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>The Pembina Pint and Restaurant</h1>
                </div>
                <div class='content'>
                    <h2>Thank you for your order!</h2>
                    <p>Your order has been confirmed and is being processed.</p>
                    
                    <div class='order-details'>
                        <h3>Order Details</h3>
                        <p><strong>Order Number:</strong> {$order['order_number']}</p>
                        <p><strong>Order Type:</strong> " . ucfirst($order['order_type']) . "</p>
                        <p><strong>Order Date:</strong> " . date('M d, Y g:i A', strtotime($order['created_at'])) . "</p>
                        {$itemsHtml}
                        <p style='margin-top: 15px;'><strong>Subtotal:</strong> " . Helper::formatCurrency($order['subtotal']) . "</p>
                        <p><strong>Tax:</strong> " . Helper::formatCurrency($order['tax_amount'] ?? 0) . "</p>
                        " . (!empty($order['shipping_amount']) ? "<p><strong>Delivery Fee:</strong> " . Helper::formatCurrency($order['shipping_amount']) . "</p>" : "") . "
                        <p style='font-size: 18px; font-weight: bold; margin-top: 10px;'><strong>Total Amount:</strong> " . Helper::formatCurrency($order['total']) . "</p>
                        <p><strong>Payment Status:</strong> " . ucfirst($order['payment_status']) . "</p>
                        <p><strong>Status:</strong> " . ucfirst($order['status']) . "</p>
                    </div>
                    
                    " . (!empty($attachments) ? "<p style='margin-top: 15px;'><strong>📎 Your receipt is attached to this email.</strong></p>" : "") . "
                    
                    <p>We'll notify you when your order is ready!</p>
                </div>
                <div class='footer'>
                    <p>The Pembina Pint and Restaurant<br>
                    282 Loren Drive, Morden, Manitoba, Canada</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::send($order['email'], $subject, $message, null, null, $attachments);
    }

    /**
     * Send order invoice email (before payment)
     * 
     * @param array $order
     * @return bool
     */
    public static function sendOrderInvoice($order)
    {
        $subject = "Order Invoice - {$order['order_number']} - Payment Required";
        
        // Build order items list
        $itemsHtml = '';
        if (isset($order['items']) && !empty($order['items'])) {
            $itemsHtml = '<table style="width: 100%; border-collapse: collapse; margin: 15px 0;">';
            $itemsHtml .= '<tr style="background-color: #f5f5f5;"><th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Item</th><th style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">Price</th></tr>';
            foreach ($order['items'] as $item) {
                $itemsHtml .= '<tr>';
                $itemsHtml .= '<td style="padding: 8px; border-bottom: 1px solid #eee;">' . htmlspecialchars($item['product_name']) . ' x ' . $item['quantity'] . '</td>';
                $itemsHtml .= '<td style="padding: 8px; text-align: right; border-bottom: 1px solid #eee;">' . Helper::formatCurrency($item['subtotal']) . '</td>';
                $itemsHtml .= '</tr>';
            }
            $itemsHtml .= '</table>';
        }
        
        $paymentUrl = (defined('BASE_URL') ? BASE_URL : '') . '/payment?order_id=' . $order['id'];
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #F4A460; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .order-details { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .button { display: inline-block; padding: 12px 24px; background-color: #F4A460; color: white; text-decoration: none; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>The Pembina Pint and Restaurant</h1>
                </div>
                <div class='content'>
                    <h2>Order Received - Payment Required</h2>
                    <p>Thank you for your order! Please complete payment to confirm your order.</p>
                    
                    <div class='order-details'>
                        <h3>Order Details</h3>
                        <p><strong>Order Number:</strong> {$order['order_number']}</p>
                        <p><strong>Order Type:</strong> " . ucfirst($order['order_type']) . "</p>
                        <p><strong>Order Date:</strong> " . date('M d, Y g:i A', strtotime($order['created_at'])) . "</p>
                        {$itemsHtml}
                        <p style='margin-top: 15px;'><strong>Subtotal:</strong> " . Helper::formatCurrency($order['subtotal']) . "</p>
                        <p><strong>Tax:</strong> " . Helper::formatCurrency($order['tax_amount'] ?? 0) . "</p>
                        " . (!empty($order['shipping_amount']) ? "<p><strong>Delivery Fee:</strong> " . Helper::formatCurrency($order['shipping_amount']) . "</p>" : "") . "
                        <p style='font-size: 18px; font-weight: bold; margin-top: 10px;'><strong>Total Amount:</strong> " . Helper::formatCurrency($order['total']) . "</p>
                        <p><strong>Payment Status:</strong> <span style='color: #d97706; font-weight: bold;'>Pending Payment</span></p>
                    </div>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='{$paymentUrl}' class='button'>Complete Payment</a>
                    </div>
                    
                    <p style='margin-top: 20px;'>Click the button above to complete your payment. Your order will be processed once payment is confirmed.</p>
                </div>
                <div class='footer'>
                    <p>The Pembina Pint and Restaurant<br>
                    282 Loren Drive, Morden, Manitoba, Canada</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::send($order['email'], $subject, $message);
    }

    private static function buildMailHeaders(string $fromName, string $fromEmail, array $attachments, string $to = '', string $subject = ''): array
    {
        $boundary = '=_Boundary_' . md5((string)microtime(true));
        $headers = "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        if (!empty($attachments)) {
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";
        } else {
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        }

        $smtpHeaders = "From: {$fromName} <{$fromEmail}>\r\n";
        $smtpHeaders .= "To: {$to}\r\n";
        $smtpHeaders .= "Subject: {$subject}\r\n";
        if (!empty($attachments)) {
            $smtpHeaders .= "MIME-Version: 1.0\r\n";
            $smtpHeaders .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";
        } else {
            $smtpHeaders .= "MIME-Version: 1.0\r\n";
            $smtpHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
        }

        return [
            'headers' => $headers,
            'smtp_headers' => $smtpHeaders,
            'boundary' => !empty($attachments) ? $boundary : null
        ];
    }

    private static function buildBody(string $message, array $attachments, ?string $boundary): string
    {
        if (empty($attachments)) {
            return $message;
        }

        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $message . "\r\n";

        foreach ($attachments as $attachment) {
            $filename = $attachment['name'] ?? 'attachment.pdf';
            $type = $attachment['type'] ?? 'application/octet-stream';
            $content = $attachment['content'] ?? '';
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: {$type}; name=\"{$filename}\"\r\n";
            $body .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($content)) . "\r\n";
        }

        $body .= "--{$boundary}--";
        return $body;
    }
    
    /**
     * Log email-related errors to php-error.log
     * 
     * @param string $message
     */
    private static function logEmail($message)
    {
        $errorLogFile = defined('ROOT_PATH') ? ROOT_PATH . '/php-error.log' : __DIR__ . '/../../php-error.log';
        $timestamp = date('Y-m-d H:i:s');
        $url = $_SERVER['REQUEST_URI'] ?? 'CLI';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        
        $logMessage = sprintf(
            "[%s] EMAIL: %s | URL: %s | Method: %s | IP: %s\n",
            $timestamp,
            $message,
            $url,
            $method,
            $ip
        );
        
        @file_put_contents($errorLogFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}

