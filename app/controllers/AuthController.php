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

    public function unauthorized()
    {
        $this->render('auth/unauthorized', [
            'page_title' => 'Unauthorized'
        ]);
    }

    /**
     * Forgot password
     */
    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCSRF()) {
                $this->render('auth/forgot-password', [
                    'error' => 'Invalid security token',
                    'csrfField' => $this->csrf->getTokenField()
                ]);
                return;
            }

            $email = trim($this->post('email'));
            
            if (empty($email)) {
                $this->render('auth/forgot-password', [
                    'error' => 'Please enter your email address',
                    'csrfField' => $this->csrf->getTokenField()
                ]);
                return;
            }

            $user = $this->userModel->findByEmail($email);
            
            if ($user) {
                // Generate reset token
                $token = Helper::generateToken();
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Save token to database
                $this->userModel->update($user['id'], [
                    'password_reset_token' => $token,
                    'password_reset_expires' => $expires
                ]);

                // Send reset email
                $resetUrl = BASE_URL . '/reset-password/' . $token;
                $message = "<h2>Password Reset Request</h2>";
                $message .= "<p>Hello {$user['first_name']},</p>";
                $message .= "<p>You requested to reset your password. Click the link below to reset it:</p>";
                $message .= "<p><a href='{$resetUrl}' style='display:inline-block;padding:10px 20px;background-color:#F4A460;color:#fff;text-decoration:none;border-radius:5px;'>Reset Password</a></p>";
                $message .= "<p>Or copy and paste this URL into your browser:</p>";
                $message .= "<p>{$resetUrl}</p>";
                $message .= "<p>This link will expire in 1 hour.</p>";
                $message .= "<p>If you didn't request this, please ignore this email.</p>";

                $sent = \App\Core\Email::send(
                    $user['email'],
                    'Password Reset Request - The Pembina Pint',
                    $message
                );

                if ($sent) {
                    \App\Core\Logger::info('Password reset email sent', [
                        'user_id' => $user['id'],
                        'email' => $user['email'],
                        'url' => $_SERVER['REQUEST_URI'] ?? null
                    ]);
                } else {
                    \App\Core\Logger::error('Failed to send password reset email', [
                        'user_id' => $user['id'],
                        'email' => $user['email'],
                        'url' => $_SERVER['REQUEST_URI'] ?? null
                    ]);
                }

                // Always show success message (security: don't reveal if email exists)
                $this->render('auth/forgot-password', [
                    'success' => 'If an account exists with that email, a password reset link has been sent.',
                    'csrfField' => $this->csrf->getTokenField()
                ]);
            } else {
                // Don't reveal if email exists (security best practice)
                $this->render('auth/forgot-password', [
                    'success' => 'If an account exists with that email, a password reset link has been sent.',
                    'csrfField' => $this->csrf->getTokenField()
                ]);
            }
        } else {
            if (isset($_SESSION['user_id'])) {
                $this->redirect('/');
            }
            $this->render('auth/forgot-password', [
                'csrfField' => $this->csrf->getTokenField()
            ]);
        }
    }

    /**
     * Reset password
     */
    public function resetPassword()
    {
        $token = $this->params['token'] ?? '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCSRF()) {
                $this->render('auth/reset-password', [
                    'error' => 'Invalid security token',
                    'token' => $token,
                    'csrfField' => $this->csrf->getTokenField()
                ]);
                return;
            }

            $token = $this->post('token', $token);
            $password = $this->post('password');
            $confirmPassword = $this->post('confirm_password');

            if (empty($password) || strlen($password) < 8) {
                $this->render('auth/reset-password', [
                    'error' => 'Password must be at least 8 characters long',
                    'token' => $token,
                    'csrfField' => $this->csrf->getTokenField()
                ]);
                return;
            }

            if ($password !== $confirmPassword) {
                $this->render('auth/reset-password', [
                    'error' => 'Passwords do not match',
                    'token' => $token,
                    'csrfField' => $this->csrf->getTokenField()
                ]);
                return;
            }

            // Find user by token
            $stmt = $this->userModel->db->prepare("SELECT * FROM users WHERE password_reset_token = :token AND password_reset_expires > NOW()");
            $stmt->execute(['token' => $token]);
            $user = $stmt->fetch();

            if (!$user) {
                $this->render('auth/reset-password', [
                    'error' => 'Invalid or expired reset token. Please request a new password reset.',
                    'token' => $token,
                    'csrfField' => $this->csrf->getTokenField()
                ]);
                return;
            }

            // Update password and clear reset token
            $this->userModel->updateUser($user['id'], [
                'password' => $password,
                'password_reset_token' => null,
                'password_reset_expires' => null
            ]);

            \App\Core\Logger::info('Password reset successful', [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'url' => $_SERVER['REQUEST_URI'] ?? null
            ]);

            $this->redirect('/login?reset=success');
        } else {
            if (empty($token)) {
                $this->redirect('/forgot-password?error=Invalid reset token');
                return;
            }

            // Verify token is valid
            $stmt = $this->userModel->db->prepare("SELECT * FROM users WHERE password_reset_token = :token AND password_reset_expires > NOW()");
            $stmt->execute(['token' => $token]);
            $user = $stmt->fetch();

            if (!$user) {
                $this->redirect('/forgot-password?error=Invalid or expired reset token');
                return;
            }

            $this->render('auth/reset-password', [
                'token' => $token,
                'csrfField' => $this->csrf->getTokenField()
            ]);
        }
    }
}

