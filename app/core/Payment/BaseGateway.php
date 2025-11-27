<?php
/**
 * Base Payment Gateway Class
 */

namespace App\Core\Payment;

use App\Core\Helper;

abstract class BaseGateway implements GatewayInterface
{
    protected $config = [];
    protected $sandbox = true;

    public function __construct($config = [])
    {
        $this->config = $config;
        $this->sandbox = $config['sandbox'] ?? true;
    }

    /**
     * Get configuration value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getConfig($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Make HTTP request
     * 
     * @param string $url
     * @param array $data
     * @param string $method
     * @param array $headers
     * @return array
     */
    protected function makeRequest($url, $data = [], $method = 'POST', $headers = [])
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        if ($method === 'POST' && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("BaseGateway::makeRequest() - cURL error: {$error} for URL: {$url}");
            return [
                'success' => false,
                'error' => $error,
                'http_code' => 0,
                'data' => ['errors' => [['detail' => $error]]]
            ];
        }

        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("BaseGateway::makeRequest() - JSON decode error: " . json_last_error_msg() . " for URL: {$url}");
            error_log("BaseGateway::makeRequest() - Response: " . substr($response, 0, 500));
        }

        $isSuccess = $httpCode >= 200 && $httpCode < 300;
        
        if (!$isSuccess) {
            error_log("BaseGateway::makeRequest() - HTTP error {$httpCode} for URL: {$url}");
            error_log("BaseGateway::makeRequest() - Response: " . substr($response, 0, 500));
        }

        return [
            'success' => $isSuccess,
            'http_code' => $httpCode,
            'data' => $responseData ?: ['raw_response' => $response]
        ];
    }
}

