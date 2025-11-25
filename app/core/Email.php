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
        $fromEmail = $fromEmail ?? SMTP_FROM_EMAIL;
        $fromName = $fromName ?? SMTP_FROM_NAME;
        
        // Use PHP mail() function if SMTP not configured
        if (empty(SMTP_HOST) || empty(SMTP_USER)) {
            $headerData = self::buildMailHeaders($fromName, $fromEmail, $attachments);
            $body = self::buildBody($message, $attachments, $headerData['boundary']);
            $headerString = $headerData['headers'];
            return mail($to, $subject, $body, $headerString);
        }
        
        // Use SMTP if configured
        return self::sendSMTP($to, $subject, $message, $fromEmail, $fromName, $attachments);
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
                        <p><strong>Total Amount:</strong> " . Helper::formatCurrency($order['total']) . "</p>
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
}

