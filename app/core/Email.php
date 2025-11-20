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
    public static function send($to, $subject, $message, $fromEmail = null, $fromName = null)
    {
        $fromEmail = $fromEmail ?? SMTP_FROM_EMAIL;
        $fromName = $fromName ?? SMTP_FROM_NAME;
        
        // Use PHP mail() function if SMTP not configured
        if (empty(SMTP_HOST) || empty(SMTP_USER)) {
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
            $headers .= "Reply-To: {$fromEmail}\r\n";
            
            return mail($to, $subject, $message, $headers);
        }
        
        // Use SMTP if configured
        return self::sendSMTP($to, $subject, $message, $fromEmail, $fromName);
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
    private static function sendSMTP($to, $subject, $message, $fromEmail, $fromName)
    {
        // Simple SMTP implementation using socket
        // For production, consider using PHPMailer or SwiftMailer
        
        $smtp = fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 30);
        
        if (!$smtp) {
            error_log("SMTP Connection Error: {$errstr} ({$errno})");
            return false;
        }
        
        $response = fgets($smtp, 515);
        
        // EHLO
        fputs($smtp, "EHLO " . SMTP_HOST . "\r\n");
        $response = fgets($smtp, 515);
        
        // Start TLS if available
        if (strpos($response, 'STARTTLS') !== false) {
            fputs($smtp, "STARTTLS\r\n");
            $response = fgets($smtp, 515);
            stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fputs($smtp, "EHLO " . SMTP_HOST . "\r\n");
            $response = fgets($smtp, 515);
        }
        
        // Auth
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = fgets($smtp, 515);
        
        fputs($smtp, base64_encode(SMTP_USER) . "\r\n");
        $response = fgets($smtp, 515);
        
        fputs($smtp, base64_encode(SMTP_PASS) . "\r\n");
        $response = fgets($smtp, 515);
        
        if (strpos($response, '235') === false) {
            error_log("SMTP Authentication failed: {$response}");
            fclose($smtp);
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
        
        // Headers and body
        $emailData = "From: {$fromName} <{$fromEmail}>\r\n";
        $emailData .= "To: {$to}\r\n";
        $emailData .= "Subject: {$subject}\r\n";
        $emailData .= "MIME-Version: 1.0\r\n";
        $emailData .= "Content-Type: text/html; charset=UTF-8\r\n";
        $emailData .= "\r\n";
        $emailData .= $message . "\r\n";
        $emailData .= ".\r\n";
        
        fputs($smtp, $emailData);
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
     * @return bool
     */
    public static function sendOrderConfirmation($order)
    {
        $subject = "Order Confirmation - {$order['order_number']}";
        
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
                        <p><strong>Total Amount:</strong> CAD " . number_format($order['total'], 2) . "</p>
                        <p><strong>Status:</strong> " . ucfirst($order['status']) . "</p>
                    </div>
                    
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
        
        return self::send($order['email'], $subject, $message);
    }
}

