<?php
/**
 * Canadian Tax Calculator
 * Handles GST/PST/HST calculation by province
 */

namespace App\Core;

use App\Core\Database;

class TaxCalculator
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Calculate tax for province
     * 
     * @param float $amount Subtotal amount
     * @param string $provinceCode Province code (e.g., 'MB', 'ON')
     * @return array ['gst' => float, 'pst' => float, 'hst' => float, 'total_tax' => float]
     */
    public function calculate($amount, $provinceCode)
    {
        $taxRate = $this->getTaxRate($provinceCode);
        
        if (!$taxRate) {
            // Default to Manitoba if province not found
            $taxRate = [
                'gst_rate' => 0.05,
                'pst_rate' => 0.07,
                'hst_rate' => 0.00
            ];
        }

        $gst = 0;
        $pst = 0;
        $hst = 0;

        if ($taxRate['hst_rate'] > 0) {
            // HST provinces (combined tax)
            $hst = $amount * $taxRate['hst_rate'];
        } else {
            // GST + PST provinces
            $gst = $amount * $taxRate['gst_rate'];
            $pst = $amount * $taxRate['pst_rate'];
        }

        $totalTax = $gst + $pst + $hst;

        return [
            'gst' => round($gst, 2),
            'pst' => round($pst, 2),
            'hst' => round($hst, 2),
            'total_tax' => round($totalTax, 2),
            'gst_rate' => $taxRate['gst_rate'],
            'pst_rate' => $taxRate['pst_rate'],
            'hst_rate' => $taxRate['hst_rate']
        ];
    }

    /**
     * Get tax rate for province
     * 
     * @param string $provinceCode
     * @return array|null
     */
    public function getTaxRate($provinceCode)
    {
        $stmt = $this->db->prepare("SELECT * FROM tax_rates WHERE province_code = :code AND is_active = 1");
        $stmt->execute(['code' => strtoupper($provinceCode)]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get all tax rates
     * 
     * @return array
     */
    public function getAllTaxRates()
    {
        $stmt = $this->db->query("SELECT * FROM tax_rates WHERE is_active = 1 ORDER BY province");
        return $stmt->fetchAll();
    }

    /**
     * Update tax rate
     * 
     * @param string $provinceCode
     * @param array $rates
     * @return bool
     */
    public function updateTaxRate($provinceCode, $rates)
    {
        $stmt = $this->db->prepare("UPDATE tax_rates SET gst_rate = :gst_rate, pst_rate = :pst_rate, hst_rate = :hst_rate WHERE province_code = :code");
        return $stmt->execute([
            'code' => strtoupper($provinceCode),
            'gst_rate' => $rates['gst_rate'] ?? 0,
            'pst_rate' => $rates['pst_rate'] ?? 0,
            'hst_rate' => $rates['hst_rate'] ?? 0
        ]);
    }
}

