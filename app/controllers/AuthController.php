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
            $this->loginError('Please enter both username and password.');
            return;
        }

        $result = $this->authenticateUser($username, $password);
        if (is_string($result)) {
            $this->loginError($result);
            return;
        }

        $this->populateSession($result);
        $this->redirect('/dashboard');
    }

    /**
     * AJAX login — returns JSON so the login page can play a transition animation.
     */
    public function loginAjax(): void
    {
        if (!$this->verifyCsrf()) {
            $this->json(['error' => 'Invalid session. Please refresh and try again.'], 403);
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$password) {
            $this->json(['error' => 'Please enter both username and password.'], 400);
            return;
        }

        $result = $this->authenticateUser($username, $password);
        if (is_string($result)) {
            $this->json(['error' => $result], 401);
            return;
        }

        $this->populateSession($result);
        $_SESSION['just_logged_in'] = true;

        $this->json([
            'success' => true,
            'first_name' => $result['first_name'] ?? '',
        ]);
    }

    /**
     * Shared authentication: returns user array on success, or error string on failure.
     */
    private function authenticateUser(string $username, string $password): array|string
    {
        $userModel = new User();
        $user = $userModel->findByUsername($username);
        if (!$user) {
            $user = $userModel->findByEmail($username);
        }

        if (!$user || !$userModel->verifyPassword($password, $user['password'])) {
            return 'Invalid credentials.';
        }

        if (isset($user['is_active']) && !$user['is_active']) {
            return 'Your account has been deactivated. Contact your administrator.';
        }

        return $user;
    }

    /**
     * Set session variables after successful authentication.
     */
    private function populateSession(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['first_name'] = $user['first_name'] ?? '';
        $_SESSION['role'] = $user['role'];
        $_SESSION['avatar_url'] = $user['avatar_url'] ?? '';
        $_SESSION['is_active'] = $user['is_active'] ?? 1;

        if (!empty($user['must_change_password'])) {
            $_SESSION['must_change_password'] = true;
        }
        if (empty($user['has_completed_tour'])) {
            $_SESSION['needs_tour'] = true;
        }

        (new User())->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    private function loginError(string $message): void
    {
        $branding = (new BrandingService())->get($GLOBALS['client_id']);
        $this->viewOnly('auth/login', [
            'branding' => $branding,
            'error' => $message,
        ]);
    }

    public function changePassword(): void
    {
        $this->requireAuth();

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || ($input['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['error' => 'Invalid request.'], 403);
            return;
        }

        $newPassword = $input['new_password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';

        if (strlen($newPassword) < 8) {
            @ob_clean();
            $this->json(['error' => 'Password must be at least 8 characters.'], 400);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            @ob_clean();
            $this->json(['error' => 'Passwords do not match.'], 400);
            return;
        }

        $userModel = new User();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $userModel->update($_SESSION['user_id'], [
            'password' => $hash,
            'must_change_password' => 0,
        ]);

        unset($_SESSION['must_change_password']);

        @ob_clean();
        $this->json(['success' => true]);
    }

    public function completeTour(): void
    {
        $this->requireAuth();

        $userModel = new User();
        $userModel->update($_SESSION['user_id'], ['has_completed_tour' => 1]);
        unset($_SESSION['needs_tour']);

        @ob_clean();
        $this->json(['success' => true]);
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
