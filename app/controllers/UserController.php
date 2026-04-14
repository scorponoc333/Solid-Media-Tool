<?php

class UserController extends Controller
{
    public function index(): void
    {
        $this->requireRole('admin');

        $clientId = $GLOBALS['client_id'];
        $service = new UserManagementService();
        $approvalService = new ApprovalService();
        $emailService = new EmailService();

        $users = $service->listUsers($clientId);
        $approvalSettings = $approvalService->getSettings($clientId);
        $smtpConfigured = $emailService->isConfigured();

        $this->view('users/index', [
            'pageTitle' => 'User Management',
            'users' => $users,
            'approvalSettings' => $approvalSettings,
            'smtpConfigured' => $smtpConfigured,
        ]);
    }

    public function create(): void
    {
        $this->requireRole('admin');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || ($input['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['error' => 'Invalid request.'], 403);
            return;
        }

        $email = trim($input['email'] ?? '');
        $firstName = trim($input['first_name'] ?? '');
        $role = $input['role'] ?? 'editor';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            @ob_clean();
            $this->json(['error' => 'Valid email address is required.'], 400);
            return;
        }

        if (!in_array($role, ['editor', 'reviewer'], true)) {
            $role = 'editor';
        }

        $clientId = $GLOBALS['client_id'];
        $service = new UserManagementService();
        $result = $service->createUser($clientId, $email, $firstName, $role);

        if (!$result['success']) {
            @ob_clean();
            $this->json(['error' => $result['error']], 400);
            return;
        }

        // Try to send invitation email
        $inviteResult = $service->inviteUser($result['user_id'], $clientId, $result['temp_password']);

        @ob_clean();
        $this->json([
            'success' => true,
            'user_id' => $result['user_id'],
            'username' => $result['username'],
            'email_sent' => $inviteResult['success'] ?? false,
            'email_error' => $inviteResult['error'] ?? null,
            'needs_smtp' => $inviteResult['needs_smtp'] ?? false,
            // Only show temp password if email wasn't sent (so admin can share it manually)
            'temp_password' => ($inviteResult['success'] ?? false) ? null : $result['temp_password'],
        ]);
    }

    public function update(string $id): void
    {
        $this->requireRole('admin');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || ($input['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['error' => 'Invalid request.'], 403);
            return;
        }

        $service = new UserManagementService();
        $updated = $service->updateUser((int)$id, $GLOBALS['client_id'], $input);

        @ob_clean();
        $this->json(['success' => $updated]);
    }

    public function deactivate(string $id): void
    {
        $this->requireRole('admin');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || ($input['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['error' => 'Invalid request.'], 403);
            return;
        }

        $service = new UserManagementService();
        $result = $service->deactivateUser((int)$id, $GLOBALS['client_id']);

        @ob_clean();
        $this->json(['success' => $result]);
    }

    public function resendInvite(string $id): void
    {
        $this->requireRole('admin');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || ($input['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['error' => 'Invalid request.'], 403);
            return;
        }

        $service = new UserManagementService();
        $clientId = $GLOBALS['client_id'];

        $tempPassword = $service->resetPassword((int)$id, $clientId);
        if (!$tempPassword) {
            @ob_clean();
            $this->json(['error' => 'User not found.'], 404);
            return;
        }

        $inviteResult = $service->inviteUser((int)$id, $clientId, $tempPassword);

        @ob_clean();
        $this->json([
            'success' => true,
            'email_sent' => $inviteResult['success'] ?? false,
            'email_error' => $inviteResult['error'] ?? null,
            'temp_password' => ($inviteResult['success'] ?? false) ? null : $tempPassword,
        ]);
    }

    public function saveApprovalSettings(): void
    {
        $this->requireRole('admin');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || ($input['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['error' => 'Invalid request.'], 403);
            return;
        }

        $approvalService = new ApprovalService();
        $approvalService->saveSettings($GLOBALS['client_id'], $input);

        @ob_clean();
        $this->json(['success' => true]);
    }
}
