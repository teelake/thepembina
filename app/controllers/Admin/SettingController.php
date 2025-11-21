<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Setting;
use App\Core\Helper;
use App\Core\TaxCalculator;

class SettingController extends Controller
{
    private $settingModel;
    private $taxCalculator;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin']);
        $this->settingModel = new Setting();
        $this->taxCalculator = new TaxCalculator();
    }

    public function index()
    {
        $this->render('admin/settings/index', [
            'settings' => $this->settingModel->getAll(),
            'page_title' => 'Settings',
            'current_page' => 'settings',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings');
            return;
        }
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/settings?error=Invalid security token');
            return;
        }

        $fields = ['site_name','site_email','site_phone','site_address'];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $this->settingModel->updateSetting($field, $_POST[$field]);
            }
        }
        $this->redirect('/admin/settings?success=Settings updated');
    }

    public function payment()
    {
        $this->render('admin/settings/payment', [
            'settings' => $this->settingModel->getAll(),
            'page_title' => 'Payment Settings',
            'current_page' => 'settings',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function updatePayment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings/payment');
            return;
        }
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/settings/payment?error=Invalid security token');
            return;
        }

        $fields = [
            'payment_square_enabled',
            'payment_square_app_id',
            'payment_square_access_token',
            'payment_square_location_id',
            'payment_square_sandbox'
        ];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                if (in_array($field, ['payment_square_enabled','payment_square_sandbox'])) {
                    $value = $value === '1' ? '1' : '0';
                }
                $this->settingModel->updateSetting($field, $value);
            }
        }
        $this->redirect('/admin/settings/payment?success=Payment settings updated');
    }

    public function tax()
    {
        $this->render('admin/settings/tax', [
            'taxRates' => $this->taxCalculator->getAllTaxRates(),
            'page_title' => 'Tax Settings',
            'current_page' => 'settings',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function updateTax()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings/tax');
            return;
        }
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/settings/tax?error=Invalid security token');
            return;
        }

        $code = $this->post('province_code');
        $rates = [
            'gst_rate' => (float)$this->post('gst_rate', 0),
            'pst_rate' => (float)$this->post('pst_rate', 0),
            'hst_rate' => (float)$this->post('hst_rate', 0)
        ];

        $this->taxCalculator->updateTaxRate($code, $rates);
        $this->redirect('/admin/settings/tax?success=Tax rates updated');
    }
}


