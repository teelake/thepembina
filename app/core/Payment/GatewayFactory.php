<?php
/**
 * Payment Gateway Factory
 */

namespace App\Core\Payment;

use App\Core\Helper;

class GatewayFactory
{
    private static $gateways = [];

    /**
     * Get payment gateway instance
     * 
     * @param string $gatewayName
     * @return GatewayInterface|null
     */
    public static function create($gatewayName)
    {
        $gatewayName = strtolower($gatewayName);
        
        if (isset(self::$gateways[$gatewayName])) {
            return self::$gateways[$gatewayName];
        }

        $config = self::getGatewayConfig($gatewayName);
        
        switch ($gatewayName) {
            case 'square':
                $gateway = new SquareGateway($config);
                break;
            
            // Add more gateways here
            // case 'stripe':
            //     $gateway = new StripeGateway($config);
            //     break;
            
            default:
                return null;
        }

        self::$gateways[$gatewayName] = $gateway;
        return $gateway;
    }

    /**
     * Get gateway configuration
     * 
     * @param string $gatewayName
     * @return array
     */
    private static function getGatewayConfig($gatewayName)
    {
        $prefix = 'payment_' . strtolower($gatewayName) . '_';
        
        return [
            'enabled' => Helper::getSetting($prefix . 'enabled', '0') === '1',
            'sandbox' => Helper::getSetting($prefix . 'sandbox', '1') === '1',
            'app_id' => Helper::getSetting($prefix . 'app_id', ''),
            'access_token' => Helper::getSetting($prefix . 'access_token', ''),
            'location_id' => Helper::getSetting($prefix . 'location_id', ''),
            // Add more config keys as needed
        ];
    }

    /**
     * Get all available gateways
     * 
     * @return array
     */
    public static function getAvailableGateways()
    {
        $gateways = [];
        
        // Check Square
        $squareConfig = self::getGatewayConfig('square');
        if ($squareConfig['enabled'] && !empty($squareConfig['access_token'])) {
            $gateways['square'] = 'Square';
        }
        
        // Add more gateways here
        
        return $gateways;
    }
}

