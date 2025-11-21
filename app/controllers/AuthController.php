<?php
/**
 * Authentication Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Core\Helper;

class AuthController extends Controller
{
    private $userModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->userModel = new User();
    }

    /**
     * Login page
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCSRF()) {
                $this->render('auth/login', [
                    'error' => 'Invalid security token',
                    'csrfField' => $this->csrf->getTokenField()
                ]);
                return;
            }

            $email = $this->post('email');
            $password = $this->post('password');

            $user = $this->userModel->verifyLogin($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role_slug'];

                Helper::logActivity('login', 'user', $user['id'], 'User logged in');

                $redirect = $user['role_slug'] === 'customer' ? '/account' : '/admin';
                $this->redirect($redirect);
            } else {
                $this->render('auth/login', [
                    'error' => 'Invalid email or password',
                    'csrfField' => $this->csrf->getTokenField()
                ]);
            }
        } else {
            if (isset($_SESSION['user_id'])) {
                $this->redirect('/');
            }
            $this->render('auth/login', [
                'csrfField' => $this->csrf->getTokenField()
            ]);
        }
    }

    /**
     * Register page
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCSRF()) {
                $this->render('auth/register', [
                    'error' => 'Invalid security token',
                    'csrfField' => $this->csrf->getTokenField()
                ]);
                return;
            }

            $data = [
                'first_name' => $this->post('first_name'),
                'last_name' => $this->post('last_name'),
                'email' => $this->post('email'),
                'phone' => $this->post('phone'),
                'password' => $this->post('password'),
                'role_id' => 4 // Customer role
            ];

            // Validation
            $rules = [
                'first_name' => 'required|min:2',
                'last_name' => 'required|min:2',
                'email' => 'required|email',
                'password' => 'required|min:6'
            ];

            if (!$this->validator->validate($data, $rules)) {
                $this->render('auth/register', [
                    'error' => $this->validator->getFirstError(),
                    'data' => $data,
                    'csrfField' => $this->csrf->getTokenField()
                ]);
                return;
            }

            // Check if email exists
            if ($this->userModel->findByEmail($data['email'])) {
                $this->render('auth/register', [
                    'error' => 'Email already registered',
                    'data' => $data,
                    'csrfField' => $this->csrf->getTokenField()
                ]);
                return;
            }

            // Create user
            $userId = $this->userModel->createUser($data);
            
            if ($userId) {
                Helper::logActivity('register', 'user', $userId, 'New user registered');
                $this->redirect('/login?registered=1');
            } else {
                $this->render('auth/register', [
                    'error' => 'Registration failed. Please try again.',
                    'csrfField' => $this->csrf->getTokenField()
                ]);
            }
        } else {
            if (isset($_SESSION['user_id'])) {
                $this->redirect('/');
            }
            $this->render('auth/register', [
                'csrfField' => $this->csrf->getTokenField()
            ]);
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        if (isset($_SESSION['user_id'])) {
            Helper::logActivity('logout', 'user', $_SESSION['user_id'], 'User logged out');
        }
        
        session_destroy();
        $this->redirect('/');
    }

    /**
     * Forgot password
     */
    public function forgotPassword()
    {
        // Implementation for password reset
        $this->render('auth/forgot-password');
    }

    /**
     * Reset password
     */
    public function resetPassword()
    {
        // Implementation for password reset
        $this->render('auth/reset-password');
    }
}

