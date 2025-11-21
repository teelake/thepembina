<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Category;
use App\Core\Helper;

class CategoryController extends Controller
{
    private $categoryModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin', 'data_entry']);
        $this->categoryModel = new Category();
    }

    public function index()
    {
        $categories = $this->categoryModel->findAll([], 'sort_order ASC, name ASC');

        $this->render('admin/categories/index', [
            'categories' => $categories,
            'page_title' => 'Categories',
            'current_page' => 'categories',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function create()
    {
        $this->render('admin/categories/form', [
            'page_title' => 'Create Category',
            'current_page' => 'categories',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/categories?error=Invalid security token');
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'slug' => Helper::slugify($this->post('name')),
            'description' => $this->post('description'),
            'status' => $this->post('status', 'active'),
            'sort_order' => (int)$this->post('sort_order', 0)
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $path = Helper::uploadFile($_FILES['image'], 'uploads/categories', ALLOWED_IMAGE_TYPES);
            if ($path) {
                $data['image'] = $path;
            }
        }

        $id = $this->categoryModel->createCategory($data);
        if ($id) {
            $this->redirect('/admin/categories?success=Category created successfully');
        } else {
            $this->redirect('/admin/categories?error=Failed to create category');
        }
    }

    public function edit()
    {
        $id = (int)($this->params['id'] ?? 0);
        $category = $this->categoryModel->find($id);
        if (!$category) {
            $this->redirect('/admin/categories?error=Category not found');
            return;
        }

        $this->render('admin/categories/form', [
            'category' => $category,
            'page_title' => 'Edit Category',
            'current_page' => 'categories',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $category = $this->categoryModel->find($id);
        if (!$category) {
            $this->redirect('/admin/categories?error=Category not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/categories/{$id}/edit?error=Invalid security token");
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'slug' => Helper::slugify($this->post('name')),
            'description' => $this->post('description'),
            'status' => $this->post('status', 'active'),
            'sort_order' => (int)$this->post('sort_order', 0)
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if (!empty($category['image'])) {
                Helper::deleteFile($category['image']);
            }
            $path = Helper::uploadFile($_FILES['image'], 'uploads/categories', ALLOWED_IMAGE_TYPES);
            if ($path) {
                $data['image'] = $path;
            }
        }

        if ($this->categoryModel->updateCategory($id, $data)) {
            $this->redirect('/admin/categories?success=Category updated successfully');
        } else {
            $this->redirect("/admin/categories/{$id}/edit?error=Failed to update category");
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $category = $this->categoryModel->find($id);
        if (!$category) {
            $this->redirect('/admin/categories?error=Category not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/categories?error=Invalid security token');
            return;
        }

        if (!empty($category['image'])) {
            Helper::deleteFile($category['image']);
        }

        if ($this->categoryModel->delete($id)) {
            $this->redirect('/admin/categories?success=Category deleted successfully');
        } else {
            $this->redirect('/admin/categories?error=Failed to delete category');
        }
    }
}


