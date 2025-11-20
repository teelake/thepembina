<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Page;
use App\Core\AuditTrail;
use App\Core\Helper;

class PageController extends Controller
{
    private $pageModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin']);
        $this->pageModel = new Page();
    }

    public function index()
    {
        $pages = $this->pageModel->findAll([], 'created_at DESC');
        $this->render('admin/pages/index', [
            'pages' => $pages,
            'page_title' => 'Pages',
            'current_page' => 'pages',
            'use_tinymce' => true
        ]);
    }

    public function create()
    {
        $this->render('admin/pages/form', [
            'page_title' => 'Create Page',
            'current_page' => 'pages',
            'use_tinymce' => true
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/pages');
            return;
        }
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/pages?error=Invalid security token');
            return;
        }

        $data = [
            'title' => $this->post('title'),
            'slug' => $this->post('slug') ?: Helper::slugify($this->post('title')),
            'content' => $this->post('content'),
            'status' => $this->post('status', 'published'),
            'meta_title' => $this->post('meta_title'),
            'meta_description' => $this->post('meta_description')
        ];

        $id = $this->pageModel->create($data);
        if ($id) {
            AuditTrail::log('page_create', 'page', $id, 'Created page');
            $this->redirect('/admin/pages?success=Page created successfully');
        } else {
            $this->redirect('/admin/pages?error=Failed to create page');
        }
    }

    public function edit()
    {
        $id = (int)($this->params['id'] ?? 0);
        $page = $this->pageModel->find($id);
        if (!$page) {
            $this->redirect('/admin/pages?error=Page not found');
            return;
        }

        $this->render('admin/pages/form', [
            'pageData' => $page,
            'page_title' => 'Edit Page',
            'current_page' => 'pages',
            'use_tinymce' => true
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/pages');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $page = $this->pageModel->find($id);
        if (!$page) {
            $this->redirect('/admin/pages?error=Page not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/pages/{$id}/edit?error=Invalid security token");
            return;
        }

        $data = [
            'title' => $this->post('title'),
            'slug' => $this->post('slug') ?: Helper::slugify($this->post('title')),
            'content' => $this->post('content'),
            'status' => $this->post('status', 'published'),
            'meta_title' => $this->post('meta_title'),
            'meta_description' => $this->post('meta_description')
        ];

        if ($this->pageModel->update($id, $data)) {
            AuditTrail::log('page_update', 'page', $id, 'Updated page');
            $this->redirect('/admin/pages?success=Page updated successfully');
        } else {
            $this->redirect("/admin/pages/{$id}/edit?error=Failed to update page");
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/pages');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $page = $this->pageModel->find($id);
        if (!$page) {
            $this->redirect('/admin/pages?error=Page not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/pages?error=Invalid security token');
            return;
        }

        if ($this->pageModel->delete($id)) {
            AuditTrail::log('page_delete', 'page', $id, 'Deleted page');
            $this->redirect('/admin/pages?success=Page deleted successfully');
        } else {
            $this->redirect('/admin/pages?error=Failed to delete page');
        }
    }
}


