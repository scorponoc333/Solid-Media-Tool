<?php

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        $branding = (new BrandingService())->get($GLOBALS['client_id']);
        $this->viewOnly('auth/login', ['branding' => $branding]);
    }

    public function login(): void
    {
        if (!$this->verifyCsrf()) {
            $this->redirect('/login');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $branding = (new BrandingService())->get($GLOBALS['client_id']);
            $this->viewOnly('auth/login', [
                'branding' => $branding,
                'error' => 'Please enter both username and password.',
            ]);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if (!$user || !$userModel->verifyPassword($password, $user['password'])) {
            $branding = (new BrandingService())->get($GLOBALS['client_id']);
            $this->viewOnly('auth/login', [
                'branding' => $branding,
                'error' => 'Invalid credentials.',
            ]);
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['first_name'] = $user['first_name'] ?? '';
        $_SESSION['role'] = $user['role'];
        $_SESSION['avatar_url'] = $user['avatar_url'] ?? '';

        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
