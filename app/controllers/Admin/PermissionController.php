<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Helper;
use App\Models\Permission;

class PermissionController extends Controller
{
    private $permissionModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin']);
        $this->permissionModel = new Permission();
    }

    public function index()
    {
        $permissions = $this->permissionModel->findAll([], 'name ASC');
        $this->render('admin/permissions/index', [
            'permissions' => $permissions,
            'page_title' => 'Permissions',
            'current_page' => 'permissions',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function create()
    {
        $this->render('admin/permissions/form', [
            'page_title' => 'Create Permission',
            'current_page' => 'permissions',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/permissions');
            return;
        }
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/permissions?error=Invalid security token');
            return;
        }

        $data = $this->collectData();
        $this->permissionModel->create($data);

        $this->redirect('/admin/permissions?success=Permission created');
    }

    public function edit()
    {
        $id = (int)($this->params['id'] ?? 0);
        $permission = $this->permissionModel->find($id);
        if (!$permission) {
            $this->redirect('/admin/permissions?error=Permission not found');
            return;
        }

        $this->render('admin/permissions/form', [
            'permission' => $permission,
            'page_title' => 'Edit Permission',
            'current_page' => 'permissions',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/permissions');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $permission = $this->permissionModel->find($id);
        if (!$permission) {
            $this->redirect('/admin/permissions?error=Permission not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/permissions/{$id}/edit?error=Invalid security token");
            return;
        }

        $data = $this->collectData();
        $this->permissionModel->update($id, $data);

        $this->redirect('/admin/permissions?success=Permission updated');
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/permissions');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $permission = $this->permissionModel->find($id);
        if (!$permission) {
            $this->redirect('/admin/permissions?error=Permission not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/permissions?error=Invalid security token');
            return;
        }

        $this->permissionModel->delete($id);
        $this->redirect('/admin/permissions?success=Permission deleted');
    }

    private function collectData(): array
    {
        $name = trim($this->post('name'));
        $slug = $this->post('slug');
        return [
            'name' => $name,
            'slug' => $slug ? Helper::slugify($slug) : Helper::slugify($name),
            'description' => $this->post('description')
        ];
    }
}


