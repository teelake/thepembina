<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;
use App\Models\Role;
use App\Core\Helper;

class UserController extends Controller
{
    private $userModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin']);
        $this->userModel = new User();
    }

    public function index()
    {
        $users = $this->userModel->findAll([], 'created_at DESC');

        $this->render('admin/users/index', [
            'users' => $users,
            'page_title' => 'Users',
            'current_page' => 'users',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function create()
    {
        $this->render('admin/users/form', [
            'page_title' => 'Create User',
            'current_page' => 'users',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/users?error=Invalid security token');
            return;
        }

        $data = [
            'first_name' => $this->post('first_name'),
            'last_name' => $this->post('last_name'),
            'email' => $this->post('email'),
            'phone' => $this->post('phone'),
            'password' => $this->post('password'),
            'role_id' => (int)$this->post('role_id'),
            'status' => $this->post('status', 'active')
        ];

        if ($this->userModel->findByEmail($data['email'])) {
            $this->redirect('/admin/users?error=Email already exists');
            return;
        }

        $userId = $this->userModel->createUser($data);
        if ($userId) {
            Helper::logActivity('user_create', 'user', $userId, 'Created user from admin');
            $this->redirect('/admin/users?success=User created successfully');
        } else {
            $this->redirect('/admin/users?error=Failed to create user');
        }
    }

    public function edit()
    {
        $id = (int)($this->params['id'] ?? 0);
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->redirect('/admin/users?error=User not found');
            return;
        }

        $this->render('admin/users/form', [
            'user' => $user,
            'page_title' => 'Edit User',
            'current_page' => 'users',
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->redirect('/admin/users?error=User not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/users/{$id}/edit?error=Invalid security token");
            return;
        }

        $data = [
            'first_name' => $this->post('first_name'),
            'last_name' => $this->post('last_name'),
            'email' => $this->post('email'),
            'phone' => $this->post('phone'),
            'role_id' => (int)$this->post('role_id'),
            'status' => $this->post('status', 'active')
        ];

        if (!empty($this->post('password'))) {
            $data['password'] = $this->post('password');
        }

        if ($this->userModel->updateUser($id, $data)) {
            Helper::logActivity('user_update', 'user', $id, 'Updated user');
            $this->redirect('/admin/users?success=User updated successfully');
        } else {
            $this->redirect("/admin/users/{$id}/edit?error=Failed to update user");
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->redirect('/admin/users?error=User not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/users?error=Invalid security token');
            return;
        }

        if ($this->userModel->delete($id)) {
            Helper::logActivity('user_delete', 'user', $id, 'Deleted user');
            $this->redirect('/admin/users?success=User deleted successfully');
        } else {
            $this->redirect('/admin/users?error=Failed to delete user');
        }
    }
}


