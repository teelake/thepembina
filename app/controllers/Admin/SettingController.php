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
            'payment_default_gateway',
            'payment_square_enabled',
            'payment_square_app_id',
            'payment_square_access_token',
            'payment_square_location_id',
            'payment_square_sandbox',
            'payment_paystack_enabled',
            'payment_paystack_public_key',
            'payment_paystack_secret_key',
            'payment_paystack_merchant_email'
        ];
        foreach ($fields as $field) {
            $value = $_POST[$field] ?? null;
            if ($value === null) {
                continue;
            }
            if (in_array($field, ['payment_square_enabled','payment_square_sandbox','payment_paystack_enabled'])) {
                $value = $value === '1' ? '1' : '0';
            }
            $this->settingModel->updateSetting($field, $value);
        }
        $this->redirect('/admin/settings/payment?success=Payment settings updated');
    }

    public function tax()
    {
        $this->render('admin/settings/tax', [
            'taxRates' => $this->taxCalculator->getAllTaxRates(),
            'page_title' => 'Tax Settings',
            'current_page' => 'tax',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function email()
    {
        $settings = $this->settingModel->getAll();
        $this->render('admin/settings/email', [
            'settings' => $settings,
            'page_title' => 'Email Settings',
            'current_page' => 'settings',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function updateEmail()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings/email');
            return;
        }
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/settings/email?error=Invalid security token');
            return;
        }

        $fields = [
            'smtp_host',
            'smtp_port',
            'smtp_user',
            'smtp_from_email',
            'smtp_from_name'
        ];
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $this->settingModel->updateSetting($field, $_POST[$field]);
            }
        }
        
        // Handle password separately - only update if provided (don't overwrite with empty)
        if (isset($_POST['smtp_pass']) && !empty(trim($_POST['smtp_pass']))) {
            // Trim the password but preserve special characters
            $password = trim($_POST['smtp_pass']);
            $this->settingModel->updateSetting('smtp_pass', $password);
        }
        
        $this->redirect('/admin/settings/email?success=Email settings updated');
    }

    public function testEmail()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings/email');
            return;
        }
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/settings/email?error=Invalid security token');
            return;
        }

        $testEmail = $this->post('test_email', '');
        if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/admin/settings/email?error=Please provide a valid email address');
            return;
        }

        // Send test email
        $subject = "Test Email from The Pembina Pint";
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #F4A460; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Test Email</h1>
                </div>
                <div class='content'>
                    <h2>Email Configuration Test</h2>
                    <p>If you received this email, your SMTP configuration is working correctly!</p>
                    <p><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                    <p><strong>From:</strong> no-reply@thepembina.ca</p>
                    <p><strong>To:</strong> {$testEmail}</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $result = \App\Core\Email::send($testEmail, $subject, $message);
        
        if ($result) {
            $this->redirect('/admin/settings/email?success=Test email sent successfully to ' . htmlspecialchars($testEmail));
        } else {
            $this->redirect('/admin/settings/email?error=Test email failed. Please check your SMTP settings and error logs.');
        }
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

    public function whatsapp()
    {
        $settings = $this->settingModel->getAll();
        $this->render('admin/settings/whatsapp', [
            'settings' => $settings,
            'page_title' => 'WhatsApp Settings',
            'current_page' => 'settings',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function updateWhatsApp()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings/whatsapp');
            return;
        }
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/settings/whatsapp?error=Invalid security token');
            return;
        }

        $fields = [
            'whatsapp_number',
            'whatsapp_message',
            'whatsapp_enabled'
        ];
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                // For enabled checkbox, convert to 1 or 0
                if ($field === 'whatsapp_enabled') {
                    $value = $value === '1' ? '1' : '0';
                }
                $this->settingModel->updateSetting($field, $value);
            } else {
                // If checkbox is not set, it means disabled
                if ($field === 'whatsapp_enabled') {
                    $this->settingModel->updateSetting($field, '0');
                }
            }
        }
        
        $this->redirect('/admin/settings/whatsapp?success=WhatsApp settings updated');
    }
}


