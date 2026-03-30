<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/helpers.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login(): array
    {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $user = $this->userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = 'Invalid email or password.';
            } elseif ($user['status'] !== 'Active') {
                $error = 'Your account is inactive. Contact support.';
            } else {
                $_SESSION['user'] = $user;
                if (($user['role_name'] ?? '') === 'Admin') {
                    redirect('index.php?page=admin');
                }
                if (($user['role_name'] ?? '') === 'Support') {
                    redirect('index.php?page=admin-helpdesk');
                }
                redirect('index.php?page=dashboard');
            }
        }
        return ['view' => 'auth/login', 'data' => ['error' => $error]];
    }

    public function register(): array
    {
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email'] ?? '');
            if ($this->userModel->findByEmail($email)) {
                $error = 'Email already registered.';
            } else {
                $ok = $this->userModel->create([
                    'role_id' => 2,
                    'full_name' => sanitize($_POST['full_name'] ?? ''),
                    'email' => $email,
                    'password_hash' => password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT),
                    'contact_no' => sanitize($_POST['contact_no'] ?? ''),
                    'address' => sanitize($_POST['address'] ?? ''),
                    'payment_preference' => sanitize($_POST['payment_preference'] ?? ''),
                ]);
                if ($ok) {
                    $success = 'Registration successful. Please login.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }

        return ['view' => 'auth/register', 'data' => ['error' => $error, 'success' => $success]];
    }

    public function logout(): void
    {
        session_destroy();
        redirect('index.php?page=login');
    }
}
