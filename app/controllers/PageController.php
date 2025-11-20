<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Page;

class PageController extends Controller
{
    private $pageModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->pageModel = new Page();
    }

    public function view()
    {
        $slug = $this->params['slug'] ?? '';
        $page = $this->pageModel->findBySlug($slug);

        if (!$page) {
            throw new \Exception('Page not found', 404);
        }

        $this->render('pages/view', [
            'page' => $page,
            'page_title' => $page['title'],
            'meta_description' => $page['meta_description']
        ]);
    }
}


