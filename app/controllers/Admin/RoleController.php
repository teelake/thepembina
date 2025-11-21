<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Helper;
use App\Models\Role;
use App\Models\Permission;

class RoleController extends Controller
{
    private $roleModel;
    private $permissionModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin']);
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
    }

    public function index()
    {
        $roles = $this->roleModel->getAllWithPermissionCount();
        $this->render('admin/roles/index', [
            'roles' => $roles,
            'page_title' => 'Roles & Access',
            'current_page' => 'roles',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function create()
    {
        $permissions = $this->permissionModel->findAll([], 'name ASC');
        $this->render('admin/roles/form', [
            'permissions' => $permissions,
            'assignedPermissions' => [],
            'page_title' => 'Create Role',
            'current_page' => 'roles',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/roles');
            return;
        }
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/roles?error=Invalid security token');
            return;
        }

        $data = $this->collectData();
        $roleId = $this->roleModel->create($data);

        $permissions = $this->getPermissionInput();
        $this->roleModel->syncPermissions($roleId, $permissions);

        $this->redirect('/admin/roles?success=Role created successfully');
    }

    public function edit()
    {
        $id = (int)($this->params['id'] ?? 0);
        $role = $this->roleModel->find($id);
        if (!$role) {
            $this->redirect('/admin/roles?error=Role not found');
            return;
        }

        $permissions = $this->permissionModel->findAll([], 'name ASC');
        $assigned = $this->roleModel->getPermissions($id);

        $this->render('admin/roles/form', [
            'role' => $role,
            'permissions' => $permissions,
            'assignedPermissions' => $assigned,
            'page_title' => 'Edit Role',
            'current_page' => 'roles',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/roles');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $role = $this->roleModel->find($id);
        if (!$role) {
            $this->redirect('/admin/roles?error=Role not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/roles/{$id}/edit?error=Invalid security token");
            return;
        }

        $data = $this->collectData();
        $this->roleModel->update($id, $data);
        $permissions = $this->getPermissionInput();
        $this->roleModel->syncPermissions($id, $permissions);

        $this->redirect('/admin/roles?success=Role updated successfully');
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/roles');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $role = $this->roleModel->find($id);
        if (!$role) {
            $this->redirect('/admin/roles?error=Role not found');
            return;
        }

        if (in_array($role['slug'], ['super_admin', 'admin', 'customer'])) {
            $this->redirect('/admin/roles?error=Protected roles cannot be deleted');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/roles?error=Invalid security token');
            return;
        }

        $this->roleModel->delete($id);
        $this->redirect('/admin/roles?success=Role deleted');
    }

    private function collectData(): array
    {
        $title = trim($this->post('name'));
        $slug = $this->post('slug');
        return [
            'name' => $title,
            'slug' => $slug ? Helper::slugify($slug) : Helper::slugify($title),
            'description' => $this->post('description')
        ];
    }

    private function getPermissionInput(): array
    {
        $permissions = $this->post('permissions', []);
        if (!is_array($permissions)) {
            $permissions = [];
        }
        return array_map('intval', $permissions);
    }
}


