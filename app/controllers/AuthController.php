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

        // Admins: check if wizard/setup is needed before tour
        if ($user['role'] === 'admin') {
            $wizardService = new WizardService();
            if (!$wizardService->isSetupComplete($GLOBALS['client_id'])) {
                $_SESSION['needs_wizard'] = true;
                // Tour will start after wizard completes
            } elseif (empty($user['has_completed_tour'])) {
                $_SESSION['needs_tour'] = true;
            }
        } elseif (empty($user['has_completed_tour'])) {
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

    public function easterEggEmail(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            @ob_clean();
            $this->json(['error' => 'Valid email required'], 400);
            return;
        }

        $emailService = new EmailService();

        // Build the hacker-themed email
        $html = $this->buildEasterEggEmailHtml();

        $result = $emailService->send($email, 'You Found the Easter Egg — Jason Hogan // AI Full-Stack Developer', $html);

        @ob_clean();
        $this->json(['success' => $result['success'] ?? false]);
    }

    private function buildEasterEggEmailHtml(): string
    {
        $blue = '#1a3a6b';
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#000;font-family:\'Helvetica Neue\',Arial,sans-serif;-webkit-font-smoothing:antialiased">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#000;padding:40px 0">
<tr><td align="center">
<table width="580" cellpadding="0" cellspacing="0" style="max-width:580px;width:100%">

<!-- Header -->
<tr><td style="background:linear-gradient(165deg,' . $blue . ' 0%,#0a0a1a 100%);border-radius:20px 20px 0 0;padding:44px 40px;text-align:center">
    <div style="font-size:11px;font-weight:700;letter-spacing:0.3em;text-transform:uppercase;color:rgba(255,255,255,0.3);margin-bottom:14px">// DEVELOPER PROFILE //</div>
    <div style="font-size:36px;font-weight:900;color:#fff;margin-bottom:4px;letter-spacing:-1px">Jason Hogan</div>
    <div style="font-size:13px;color:rgba(255,255,255,0.4);font-family:monospace">AI Full-Stack Developer &bull; Automation Architect &bull; Innovation Expert</div>
</td></tr>

<!-- Accent -->
<tr><td style="height:3px;background:linear-gradient(90deg,' . $blue . ',rgba(26,58,107,0.3),transparent)"></td></tr>

<!-- Body -->
<tr><td style="background:#0a0a12;padding:40px;color:#c0c8d8">

    <!-- Intro -->
    <div style="font-size:15px;line-height:1.8;margin-bottom:28px;color:#8899bb">
        You just discovered a hidden Easter egg inside a live AI-powered enterprise application. That application &mdash; every screen, animation, API integration, and cinematic transition &mdash; was built by <strong style="color:#fff">Jason Hogan</strong>.
    </div>

    <!-- Skills pills -->
    <div style="margin-bottom:28px">
        <div style="font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:rgba(255,255,255,0.25);margin-bottom:12px">// CORE CAPABILITIES</div>
        <div style="display:flex;flex-wrap:wrap;gap:6px">' .
        implode('', array_map(function($s) {
            return '<span style="display:inline-block;padding:5px 12px;background:rgba(26,58,107,0.3);border:1px solid rgba(26,58,107,0.5);border-radius:100px;font-size:11px;font-weight:600;color:#7aa2d4">' . $s . '</span>';
        }, ['AI Agent Development', 'Full-Stack Engineering', 'Multi-Agent Orchestration', 'Enterprise Automation', 'UI/UX Design', 'PHP / MySQL / JS', 'OpenAI / Claude API', 'Computer Vision', 'Brand Systems', 'Video Production']))
        . '</div>
    </div>

    <!-- What I build section -->
    <div style="background:rgba(26,58,107,0.12);border-left:3px solid ' . $blue . ';border-radius:0 12px 12px 0;padding:20px 24px;margin-bottom:28px">
        <div style="font-size:13px;font-weight:700;color:#fff;margin-bottom:8px">What I Build</div>
        <div style="font-size:13px;line-height:1.8;color:#8899bb">
            I build AI-powered systems that automate entire business workflows &mdash; from content generation engines that write, design, and publish across platforms, to multi-agent personality systems that handle customer interactions autonomously. My work sits at the intersection of AI, full-stack engineering, and enterprise automation. I don\'t just write code; I architect intelligent systems that think, adapt, and scale.
        </div>
    </div>

    <!-- Achievements -->
    <div style="margin-bottom:28px">
        <div style="font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:rgba(255,255,255,0.25);margin-bottom:12px">// NOTABLE</div>
        <div style="font-size:13px;line-height:2;color:#8899bb">
            &bull; Amazon #3 Bestseller: <em style="color:#fff">From Likes 2 Loyalty</em> (Office Automation)<br>
            &bull; 3x IABC Award Winner (Excellence + Merit)<br>
            &bull; Google AI Essentials Certified<br>
            &bull; Creator of AI Agent Builders (automated AI news platform)<br>
            &bull; 11,000+ LinkedIn followers
        </div>
    </div>

    <!-- CTA buttons -->
    <div style="text-align:center;margin-bottom:24px">
        <div style="font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:rgba(255,255,255,0.25);margin-bottom:14px">// GET IN TOUCH</div>
        <table width="100%" cellpadding="0" cellspacing="0"><tr>
            <td align="center" style="padding:0 4px">
                <a href="mailto:me@jasonhogan.ca" style="display:inline-block;padding:14px 28px;background:' . $blue . ';color:#fff;font-size:14px;font-weight:600;text-decoration:none;border-radius:8px;min-width:140px">Email Jason</a>
            </td>
            <td align="center" style="padding:0 4px">
                <a href="tel:+15879837066" style="display:inline-block;padding:14px 28px;background:rgba(26,58,107,0.3);border:1px solid rgba(26,58,107,0.6);color:#7aa2d4;font-size:14px;font-weight:600;text-decoration:none;border-radius:8px;min-width:140px">Call: 587-983-7066</a>
            </td>
        </tr></table>
    </div>

    <!-- PS -->
    <div style="border-top:1px solid rgba(255,255,255,0.06);padding-top:20px;margin-top:8px">
        <div style="font-size:12px;color:rgba(255,255,255,0.2);line-height:1.7;font-style:italic">
            <strong style="color:rgba(255,255,255,0.35)">P.S.</strong> Don\'t bother checking out jasonhogan.ca &mdash; I\'ve been too busy building systems that are far too cool, and I haven\'t had time to update my own site. You know how it is. The cobbler\'s kids have no shoes. But I promise, the work I do for clients is on another level entirely.
        </div>
    </div>

</td></tr>

<!-- Footer -->
<tr><td style="background:#050510;border-radius:0 0 20px 20px;padding:24px 40px;text-align:center">
    <div style="font-size:11px;color:#334155;font-family:monospace;line-height:1.8">
        <a href="https://www.linkedin.com/in/jasonhogan333" style="color:#4477aa;text-decoration:none">LinkedIn</a>
        &nbsp;&bull;&nbsp;
        <a href="https://jasonhogan.ca" style="color:#4477aa;text-decoration:none">jasonhogan.ca</a>
        &nbsp;&bull;&nbsp;
        <a href="mailto:me@jasonhogan.ca" style="color:#4477aa;text-decoration:none">me@jasonhogan.ca</a>
        <br>
        <span style="color:#1a2233">// You found the Easter egg. You\'re one of the cool ones. //</span>
    </div>
</td></tr>

</table>
</td></tr>
</table>
</body></html>';
    }

    public function forgotPassword(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            @ob_clean();
            $this->json(['error' => 'Please enter a valid email address.'], 400);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        // Always return success to prevent email enumeration
        if (!$user) {
            @ob_clean();
            $this->json(['success' => true]);
            return;
        }

        $service = new UserManagementService();
        $tempPassword = $service->resetPassword($user['id'], $user['client_id']);

        if ($tempPassword) {
            $service->inviteUser($user['id'], $user['client_id'], $tempPassword);
        }

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
