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
        
        // Get SMTP settings from database (prioritize) or constants
        // Database settings take precedence so admin can update via backend
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
        
        // Prioritize database settings over constants (admin can update via backend)
        $smtpHost = trim(Helper::getSetting('smtp_host', defined('SMTP_HOST') ? SMTP_HOST : ''));
        $smtpPort = (int)Helper::getSetting('smtp_port', defined('SMTP_PORT') ? SMTP_PORT : 587);
        $smtpUser = trim(Helper::getSetting('smtp_user', defined('SMTP_USER') ? SMTP_USER : ''));
        $smtpPass = Helper::getSetting('smtp_pass', defined('SMTP_PASS') ? SMTP_PASS : '');
        
        // Trim password but preserve special characters
        $smtpPass = trim($smtpPass);
        
        // Validate required settings
        if (empty($smtpHost) || empty($smtpUser) || empty($smtpPass)) {
            self::logEmail("SMTP Configuration Error: Missing required settings | Host: " . ($smtpHost ?: 'empty') . ", User: " . ($smtpUser ?: 'empty') . ", Pass: " . ($smtpPass ? 'set' : 'empty'));
            return false;
        }
        
        $smtp = @fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
        
        if (!$smtp) {
            self::logEmail("SMTP Connection Error: {$errstr} ({$errno}) | Host: {$smtpHost}, Port: {$smtpPort}");
            return false;
        }
        
        $response = self::readResponse($smtp);
        
        // EHLO
        fputs($smtp, "EHLO " . $smtpHost . "\r\n");
        $response = self::readResponse($smtp);
        
        // Start TLS if available
        if (stripos($response, 'STARTTLS') !== false) {
            fputs($smtp, "STARTTLS\r\n");
            self::readResponse($smtp);
            stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fputs($smtp, "EHLO " . $smtpHost . "\r\n");
            $response = self::readResponse($smtp);
        }
        
        // Auth
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = self::readResponse($smtp);
        if (strpos($response, '334') !== 0) {
            self::logEmail("SMTP AUTH LOGIN not accepted: {$response}");
            fclose($smtp);
            return false;
        }

        // Send username
        fputs($smtp, base64_encode($smtpUser) . "\r\n");
        $response = self::readResponse($smtp);
        
        if (strpos($response, '334') === false) {
            self::logEmail("SMTP Username not accepted: {$response} | User: {$smtpUser}");
            @fclose($smtp);
            return false;
        }

        // Send password (base64 encoded)
        fputs($smtp, base64_encode($smtpPass) . "\r\n");
        $response = self::readResponse($smtp);
        
        if (strpos($response, '235') === false) {
            // Log error but don't expose password
            $passLength = strlen($smtpPass);
            $passSet = !empty($smtpPass) ? 'Yes' : 'No';
            $passPreview = $passLength > 0 ? (substr($smtpPass, 0, 1) . str_repeat('*', min($passLength - 1, 10)) . ($passLength > 11 ? '...' : '')) : 'empty';
            
            // Check for common error codes
            $errorDetails = '';
            if (strpos($response, '535') !== false) {
                $errorDetails = ' - This usually means incorrect username or password. Please verify your SMTP credentials in Email Settings.';
            } elseif (strpos($response, '534') !== false) {
                $errorDetails = ' - Authentication mechanism not supported.';
            } elseif (strpos($response, '504') !== false) {
                $errorDetails = ' - Authentication mechanism not supported by server.';
            }
            
            self::logEmail("SMTP Authentication failed: {$response}{$errorDetails} | User: {$smtpUser}, Host: {$smtpHost}, Port: {$smtpPort}, Password Set: {$passSet}, Password Length: {$passLength}, Password Preview: {$passPreview}");
            @fclose($smtp);
            return false;
        }
        
        // Mail from
        fputs($smtp, "MAIL FROM: <{$fromEmail}>\r\n");
        $response = self::readResponse($smtp);
        
        // RCPT to
        fputs($smtp, "RCPT TO: <{$to}>\r\n");
        $response = self::readResponse($smtp);
        
        // Data
        fputs($smtp, "DATA\r\n");
        $response = self::readResponse($smtp);
        
        $headerData = self::buildMailHeaders($fromName, $fromEmail, $attachments, $to, $subject);
        $body = self::buildBody($message, $attachments, $headerData['boundary']);
        $payload  = $headerData['smtp_headers'];
        $payload .= "\r\n" . $body . "\r\n.\r\n";

        fputs($smtp, $payload);
        $response = self::readResponse($smtp);
        
        // Quit
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);
        
        return strpos($response, '250') !== false;
    }

    /**
     * Read complete SMTP response, handling multi-line replies.
     */
    private static function readResponse($smtp): string
    {
        $response = '';
        while (($line = fgets($smtp, 515)) !== false) {
            $response .= $line;
            if (strlen($line) < 4) {
                break;
            }
            if ($line[3] !== '-') {
                break;
            }
        }
        return trim($response);
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
        // Customer emails should come FROM no-reply@thepembina.ca
        $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'no-reply@thepembina.ca';
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'The Pembina Pint and Restaurant';
        
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
                    
                    <div style='margin: 20px 0; text-align: center;'>
                        <a href='" . (defined('BASE_URL') ? BASE_URL : '') . "/track-order?order_number=" . urlencode($order['order_number']) . "&email=" . urlencode($order['email']) . "' 
                           style='display: inline-block; padding: 12px 24px; background-color: #F4A460; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                            Track Your Order
                        </a>
                    </div>
                    
                    <p style='margin-top: 15px;'><strong>Current Status:</strong> " . ucfirst($order['status']) . "</p>
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
        
        // Send FROM no-reply@thepembina.ca TO customer
        return self::send($order['email'], $subject, $message, $fromEmail, $fromName, $attachments);
    }

    /**
     * Send order invoice email (before payment)
     * 
     * @param array $order
     * @return bool
     */
    public static function sendOrderInvoice($order)
    {
        // Customer emails should come FROM no-reply@thepembina.ca
        $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'no-reply@thepembina.ca';
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'The Pembina Pint and Restaurant';
        
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
                    
                    <div style='margin: 20px 0; text-align: center;'>
                        <a href='" . (defined('BASE_URL') ? BASE_URL : '') . "/track-order?order_number=" . urlencode($order['order_number']) . "&email=" . urlencode($order['email']) . "' 
                           style='display: inline-block; padding: 12px 24px; background-color: #8B4513; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 10px;'>
                            Track Your Order
                        </a>
                    </div>
                </div>
                <div class='footer'>
                    <p>The Pembina Pint and Restaurant<br>
                    282 Loren Drive, Morden, Manitoba, Canada</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Send FROM no-reply@thepembina.ca TO customer (no attachments for invoice)
        return self::send($order['email'], $subject, $message, $fromEmail, $fromName, []);
    }

    /**
     * Send order notification email to admin (orders@thepembina.ca)
     * 
     * @param array $order
     * @return bool
     */
    public static function sendOrderNotification($order)
    {
        // Order notifications should go TO orders@thepembina.ca FROM no-reply@thepembina.ca
        $notificationEmail = 'orders@thepembina.ca';
        $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'no-reply@thepembina.ca';
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'The Pembina Pint and Restaurant';
        $subject = "New Order Received - #{$order['order_number']}";
        
        // Build order items list
        $itemsHtml = '';
        if (isset($order['items']) && !empty($order['items'])) {
            $itemsHtml = '<table style="width: 100%; border-collapse: collapse; margin: 15px 0;">';
            $itemsHtml .= '<tr style="background-color: #f5f5f5;"><th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Item</th><th style="padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">Qty</th><th style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">Price</th></tr>';
            foreach ($order['items'] as $item) {
                $itemsHtml .= '<tr>';
                $itemsHtml .= '<td style="padding: 8px; border-bottom: 1px solid #eee;">' . htmlspecialchars($item['product_name']) . '</td>';
                $itemsHtml .= '<td style="padding: 8px; text-align: center; border-bottom: 1px solid #eee;">' . $item['quantity'] . '</td>';
                $itemsHtml .= '<td style="padding: 8px; text-align: right; border-bottom: 1px solid #eee;">' . Helper::formatCurrency($item['subtotal']) . '</td>';
                $itemsHtml .= '</tr>';
            }
            $itemsHtml .= '</table>';
        }
        
        // Format billing address
        $billingAddress = json_decode($order['billing_address'] ?? '[]', true);
        $billingHtml = '';
        if (!empty($billingAddress)) {
            $billingHtml = '<p><strong>Name:</strong> ' . htmlspecialchars(($billingAddress['first_name'] ?? '') . ' ' . ($billingAddress['last_name'] ?? '')) . '</p>';
            $billingHtml .= '<p><strong>Email:</strong> ' . htmlspecialchars($order['email'] ?? '') . '</p>';
            $billingHtml .= '<p><strong>Phone:</strong> ' . htmlspecialchars($order['phone'] ?? '') . '</p>';
            if (!empty($billingAddress['address_line1'])) {
                $billingHtml .= '<p><strong>Address:</strong> ' . htmlspecialchars($billingAddress['address_line1']);
                if (!empty($billingAddress['address_line2'])) {
                    $billingHtml .= ', ' . htmlspecialchars($billingAddress['address_line2']);
                }
                $billingHtml .= '</p>';
            }
            if (!empty($billingAddress['city'])) {
                $billingHtml .= '<p><strong>City:</strong> ' . htmlspecialchars($billingAddress['city']);
                if (!empty($billingAddress['province'])) {
                    $billingHtml .= ', ' . htmlspecialchars($billingAddress['province']);
                }
                if (!empty($billingAddress['postal_code'])) {
                    $billingHtml .= ' ' . htmlspecialchars($billingAddress['postal_code']);
                }
                $billingHtml .= '</p>';
            }
        }
        
        // Format shipping address if delivery
        $shippingHtml = '';
        if ($order['order_type'] === 'delivery' && !empty($order['shipping_address'])) {
            $shippingAddress = json_decode($order['shipping_address'], true);
            if (!empty($shippingAddress)) {
                $shippingHtml = '<h3 style="margin-top: 20px; color: #8B4513;">Delivery Address</h3>';
                $shippingHtml .= '<p><strong>Name:</strong> ' . htmlspecialchars(($shippingAddress['first_name'] ?? '') . ' ' . ($shippingAddress['last_name'] ?? '')) . '</p>';
                if (!empty($shippingAddress['address_line1'])) {
                    $shippingHtml .= '<p><strong>Address:</strong> ' . htmlspecialchars($shippingAddress['address_line1']);
                    if (!empty($shippingAddress['address_line2'])) {
                        $shippingHtml .= ', ' . htmlspecialchars($shippingAddress['address_line2']);
                    }
                    $shippingHtml .= '</p>';
                }
                if (!empty($shippingAddress['city'])) {
                    $shippingHtml .= '<p><strong>City:</strong> ' . htmlspecialchars($shippingAddress['city']);
                    if (!empty($shippingAddress['province'])) {
                        $shippingHtml .= ', ' . htmlspecialchars($shippingAddress['province']);
                    }
                    if (!empty($shippingAddress['postal_code'])) {
                        $shippingHtml .= ' ' . htmlspecialchars($shippingAddress['postal_code']);
                    }
                    $shippingHtml .= '</p>';
                }
                if (!empty($order['delivery_instructions'])) {
                    $shippingHtml .= '<p><strong>Delivery Instructions:</strong> ' . htmlspecialchars($order['delivery_instructions']) . '</p>';
                }
            }
        } elseif ($order['order_type'] === 'pickup' && !empty($order['pickup_time'])) {
            $shippingHtml = '<p><strong>Pickup Time:</strong> ' . htmlspecialchars($order['pickup_time']) . '</p>';
        }
        
        $adminUrl = (defined('BASE_URL') ? BASE_URL : '') . '/admin/orders/' . $order['id'];
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 700px; margin: 0 auto; padding: 20px; }
                .header { background-color: #F4A460; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .order-details { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .button { display: inline-block; padding: 12px 24px; background-color: #F4A460; color: white; text-decoration: none; border-radius: 5px; margin: 15px 0; }
                .alert { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>New Order Received</h1>
                </div>
                <div class='content'>
                    <div class='alert'>
                        <strong>⚠️ Action Required:</strong> A new order has been placed and requires your attention.
                    </div>
                    
                    <div class='order-details'>
                        <h2 style='color: #8B4513; margin-top: 0;'>Order #{$order['order_number']}</h2>
                        <p><strong>Order Type:</strong> " . ucfirst($order['order_type']) . "</p>
                        <p><strong>Order Date:</strong> " . date('M d, Y g:i A', strtotime($order['created_at'])) . "</p>
                        <p><strong>Status:</strong> <span style='color: #d97706; font-weight: bold;'>" . ucfirst($order['status']) . "</span></p>
                        <p><strong>Payment Status:</strong> <span style='color: #d97706; font-weight: bold;'>" . ucfirst($order['payment_status']) . "</span></p>
                    </div>
                    
                    <div class='order-details'>
                        <h3 style='color: #8B4513;'>Order Items</h3>
                        {$itemsHtml}
                        <p style='margin-top: 15px;'><strong>Subtotal:</strong> " . Helper::formatCurrency($order['subtotal']) . "</p>
                        <p><strong>Tax:</strong> " . Helper::formatCurrency($order['tax_amount'] ?? 0) . "</p>
                        " . (!empty($order['shipping_amount']) ? "<p><strong>Delivery Fee:</strong> " . Helper::formatCurrency($order['shipping_amount']) . "</p>" : "") . "
                        <p style='font-size: 18px; font-weight: bold; margin-top: 10px; color: #8B4513;'><strong>Total Amount:</strong> " . Helper::formatCurrency($order['total']) . "</p>
                    </div>
                    
                    <div class='order-details'>
                        <h3 style='color: #8B4513;'>Customer Information</h3>
                        {$billingHtml}
                        {$shippingHtml}
                    </div>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='{$adminUrl}' class='button'>View Order in Admin Panel</a>
                    </div>
                </div>
                <div class='footer'>
                    <p>The Pembina Pint and Restaurant<br>
                    Order Management System</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Send TO orders@thepembina.ca FROM no-reply@thepembina.ca
        return self::send($notificationEmail, $subject, $message, $fromEmail, $fromName);
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

