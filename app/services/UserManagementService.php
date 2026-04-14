<?php

class UserManagementService
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function listUsers(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT id, username, first_name, email, role, is_active, must_change_password, has_completed_tour, last_login_at, created_at
             FROM users WHERE client_id = :cid ORDER BY created_at DESC",
            ['cid' => $clientId]
        );
    }

    public function generateTempPassword(): string
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#$';
        $password = '';
        for ($i = 0; $i < 12; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }

    public function createUser(int $clientId, string $email, string $firstName, string $role): array
    {
        // Check if email already exists
        $existing = $this->userModel->findByEmail($email);
        if ($existing) {
            return ['success' => false, 'error' => 'A user with this email already exists.'];
        }

        // Generate username from email
        $username = strtolower(explode('@', $email)[0]);
        $baseUsername = $username;
        $suffix = 1;
        while ($this->userModel->findByUsername($username)) {
            $username = $baseUsername . $suffix;
            $suffix++;
        }

        // Generate temp password
        $tempPassword = $this->generateTempPassword();
        $hash = password_hash($tempPassword, PASSWORD_BCRYPT);

        $userId = $this->userModel->create([
            'username' => $username,
            'email' => $email,
            'first_name' => $firstName,
            'password' => $hash,
            'role' => $role,
            'must_change_password' => 1,
            'has_completed_tour' => 0,
            'is_active' => 1,
            'invited_by' => $_SESSION['user_id'] ?? null,
            'client_id' => $clientId,
        ]);

        return [
            'success' => true,
            'user_id' => $userId,
            'username' => $username,
            'temp_password' => $tempPassword,
        ];
    }

    public function updateUser(int $userId, int $clientId, array $data): bool
    {
        $user = Database::fetch("SELECT * FROM users WHERE id = :id AND client_id = :cid", ['id' => $userId, 'cid' => $clientId]);
        if (!$user) return false;

        $allowed = ['first_name', 'email', 'role', 'is_active'];
        $filtered = array_intersect_key($data, array_flip($allowed));
        if (empty($filtered)) return false;

        $this->userModel->update($userId, $filtered);
        return true;
    }

    public function deactivateUser(int $userId, int $clientId): bool
    {
        $user = Database::fetch("SELECT * FROM users WHERE id = :id AND client_id = :cid", ['id' => $userId, 'cid' => $clientId]);
        if (!$user) return false;

        // Prevent deactivating yourself
        if ($userId === (int)($_SESSION['user_id'] ?? 0)) {
            return false;
        }

        $this->userModel->update($userId, ['is_active' => 0]);
        return true;
    }

    public function resetPassword(int $userId, int $clientId): ?string
    {
        $user = Database::fetch("SELECT * FROM users WHERE id = :id AND client_id = :cid", ['id' => $userId, 'cid' => $clientId]);
        if (!$user) return null;

        $tempPassword = $this->generateTempPassword();
        $hash = password_hash($tempPassword, PASSWORD_BCRYPT);

        $this->userModel->update($userId, [
            'password' => $hash,
            'must_change_password' => 1,
        ]);

        return $tempPassword;
    }

    public function inviteUser(int $userId, int $clientId, string $tempPassword): array
    {
        $user = Database::fetch("SELECT * FROM users WHERE id = :id AND client_id = :cid", ['id' => $userId, 'cid' => $clientId]);
        if (!$user) return ['success' => false, 'error' => 'User not found'];

        $emailService = new EmailService();
        if (!$emailService->isConfigured()) {
            return ['success' => false, 'error' => 'Email not configured. Set up SMTP settings first.', 'needs_smtp' => true];
        }

        $brandingService = new BrandingService();
        $branding = $brandingService->get($clientId);

        $html = $emailService->buildInvitationHtml(
            $branding['logo_url'] ?? '',
            $branding['primary_color'] ?? '#6366f1',
            $branding['company_name'] ?? APP_NAME,
            $user['email'] ?: $user['username'],
            $tempPassword,
            BASE_URL . '/login'
        );

        $companyName = $branding['company_name'] ?? APP_NAME;
        $result = $emailService->send(
            $user['email'],
            "You've been invited to {$companyName}",
            $html
        );

        return $result;
    }
}
