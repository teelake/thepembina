<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\NewsletterSubscriber;

class NewsletterController extends Controller
{
    private $subscriberModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->subscriberModel = new NewsletterSubscriber();
    }

    public function subscribe()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 405);
        }

        if (!$this->verifyCSRF()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid security token'], 403);
        }

        $email = filter_var($this->post('email'), FILTER_VALIDATE_EMAIL);
        $name = trim($this->post('name'));

        if (!$email) {
            $this->jsonResponse(['success' => false, 'message' => 'Please enter a valid email address'], 422);
        }

        $subscriberId = $this->subscriberModel->createSubscriber([
            'email' => $email,
            'name' => $name
        ]);

        if ($subscriberId) {
            $this->jsonResponse(['success' => true, 'message' => 'Thank you for joining our newsletter!']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Unable to subscribe at this time.'], 500);
        }
    }
}


