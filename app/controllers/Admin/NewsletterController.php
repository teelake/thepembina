<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\NewsletterSubscriber;

class NewsletterController extends Controller
{
    private $subscriberModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin', 'data_entry']);
        $this->subscriberModel = new NewsletterSubscriber();
    }

    public function index()
    {
        $subscribers = $this->subscriberModel->findAll([], 'created_at DESC');

        $this->render('admin/newsletter/index', [
            'subscribers' => $subscribers,
            'page_title' => 'Newsletter Subscribers',
            'current_page' => 'newsletter'
        ]);
    }
}

