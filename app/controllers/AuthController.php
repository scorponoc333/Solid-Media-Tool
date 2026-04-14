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
        $html = $this->buildEasterEggEmailHtml();

        // Generate the profile HTML file for attachment
        $profilePath = ProfilePdfService::generateFile();

        // Send with subject
        $result = $emailService->send(
            $email,
            'You\'ve Just Met One of the Best AI Developers — Jason Hogan',
            $html
        );

        @ob_clean();
        $this->json(['success' => $result['success'] ?? false]);
    }

    private function buildEasterEggEmailHtml(): string
    {
        $blue = '#1a3a6b';
        $lb = '#e8f0fe';
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:\'Helvetica Neue\',Arial,sans-serif;-webkit-font-smoothing:antialiased">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:40px 0">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%">

<!-- Header gradient -->
<tr><td style="background:linear-gradient(165deg,' . $blue . ' 0%,#0d1b3e 100%);border-radius:16px 16px 0 0;padding:36px 40px;text-align:center">
    <div style="font-size:32px;margin-bottom:8px">&#x1F916;</div>
    <div style="font-size:11px;font-weight:600;letter-spacing:0.25em;text-transform:uppercase;color:rgba(255,255,255,0.4);margin-bottom:10px">AI TRANSMISSION</div>
    <div style="font-size:30px;font-weight:900;color:#fff;letter-spacing:-0.5px">Jason Hogan</div>
    <div style="font-size:13px;color:rgba(255,255,255,0.5);margin-top:4px">AI Full-Stack Developer &bull; Automation Architect &bull; Innovation Expert</div>
</td></tr>

<!-- Accent bar -->
<tr><td style="height:3px;background:linear-gradient(90deg,' . $blue . ',' . $lb . ',transparent)"></td></tr>

<!-- Body — LIGHT MODE -->
<tr><td style="background:#ffffff;padding:40px">

    <!-- Robot intro -->
    <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:24px">
        <div style="font-size:24px;flex-shrink:0">&#x1F916;</div>
        <div style="font-size:15px;color:#334155;line-height:1.8">
            <strong style="color:#1a1a2e">Hello, human.</strong> I see you\'ve been introduced to my creator today. Allow me to tell you about the person who built me &mdash; and the remarkable systems he engineers.
        </div>
    </div>

    <div style="font-size:14px;color:#475569;line-height:1.8;margin-bottom:24px">
        <strong style="color:#1a1a2e">Jason Hogan</strong> is a full-stack developer, multimedia producer, and AI systems architect based in Edmonton, Alberta. He builds robust, media-rich solutions that combine intelligent automation with polished user experiences. He is, quite simply, a jack of all trades who has mastered them all &mdash; and he works alongside a team of AI agents that he custom-built himself.
    </div>

    <!-- Skills as tiles -->
    <div style="font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#94a3b8;margin-bottom:10px">CORE CAPABILITIES</div>
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px"><tr>
        <td style="padding:4px" width="50%"><div style="background:' . $lb . ';border:1px solid #c8d8f0;border-radius:8px;padding:10px 14px;font-size:12px;font-weight:600;color:' . $blue . ';text-align:center">Full-Stack Web Development</div></td>
        <td style="padding:4px" width="50%"><div style="background:' . $lb . ';border:1px solid #c8d8f0;border-radius:8px;padding:10px 14px;font-size:12px;font-weight:600;color:' . $blue . ';text-align:center">AI Agent Development</div></td>
    </tr><tr>
        <td style="padding:4px"><div style="background:' . $lb . ';border:1px solid #c8d8f0;border-radius:8px;padding:10px 14px;font-size:12px;font-weight:600;color:' . $blue . ';text-align:center">AI Automation Systems</div></td>
        <td style="padding:4px"><div style="background:' . $lb . ';border:1px solid #c8d8f0;border-radius:8px;padding:10px 14px;font-size:12px;font-weight:600;color:' . $blue . ';text-align:center">Multi-Agent Orchestration</div></td>
    </tr><tr>
        <td style="padding:4px"><div style="background:' . $lb . ';border:1px solid #c8d8f0;border-radius:8px;padding:10px 14px;font-size:12px;font-weight:600;color:' . $blue . ';text-align:center">UI/UX Design</div></td>
        <td style="padding:4px"><div style="background:' . $lb . ';border:1px solid #c8d8f0;border-radius:8px;padding:10px 14px;font-size:12px;font-weight:600;color:' . $blue . ';text-align:center">Innovation &amp; R&amp;D</div></td>
    </tr><tr>
        <td style="padding:4px"><div style="background:' . $lb . ';border:1px solid #c8d8f0;border-radius:8px;padding:10px 14px;font-size:12px;font-weight:600;color:' . $blue . ';text-align:center">Video Production</div></td>
        <td style="padding:4px"><div style="background:' . $lb . ';border:1px solid #c8d8f0;border-radius:8px;padding:10px 14px;font-size:12px;font-weight:600;color:' . $blue . ';text-align:center">AI Systems Architecture</div></td>
    </tr></table>

    <!-- What he builds -->
    <div style="background:#f8fafc;border-left:4px solid ' . $blue . ';border-radius:0 10px 10px 0;padding:20px 24px;margin-bottom:24px">
        <div style="font-size:14px;font-weight:700;color:#1a1a2e;margin-bottom:8px">&#x1F916; What My Creator Builds</div>
        <div style="font-size:13px;line-height:1.8;color:#475569">
            Jason develops code <em>and</em> multimedia. He creates AI-powered platforms that automate entire business workflows &mdash; content engines that write, design, and publish across social platforms. Multi-agent systems where each AI has a role. Enterprise dashboards with cinematic animations. Every solution is robust, media-rich, and functional. He built me, and I help him build everything else.
        </div>
    </div>

    <!-- Achievements -->
    <div style="font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#94a3b8;margin-bottom:10px">NOTABLE ACHIEVEMENTS</div>
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px">
        <tr><td style="padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#475569"><strong style="color:#1a1a2e">&#x1F4DA; Amazon #3 Bestseller:</strong> <em>From Likes 2 Loyalty</em> (Office Automation)</td></tr>
        <tr><td style="padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#475569"><strong style="color:#1a1a2e">&#x1F3C6; 3x IABC Award Winner</strong> &mdash; Excellence + Merit in digital &amp; visual</td></tr>
        <tr><td style="padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#475569"><strong style="color:#1a1a2e">&#x1F916; AI Agent Builders</strong> &mdash; Fully autonomous AI news platform</td></tr>
        <tr><td style="padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#475569"><strong style="color:#1a1a2e">&#x1F393; Google AI Essentials</strong> Certified (2025)</td></tr>
        <tr><td style="padding:8px 0;font-size:13px;color:#475569"><strong style="color:#1a1a2e">&#x1F465; 11,000+ LinkedIn Followers</strong> &mdash; AI marketing &amp; automation</td></tr>
    </table>

    <!-- CTA buttons -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px"><tr>
        <td align="center" style="padding:4px">
            <a href="mailto:me@jasonhogan.ca" style="display:inline-block;padding:14px 32px;background:' . $blue . ';color:#fff;font-size:14px;font-weight:600;text-decoration:none;border-radius:8px">&#x2709; Email Jason</a>
        </td>
        <td align="center" style="padding:4px">
            <a href="tel:+15879837066" style="display:inline-block;padding:14px 32px;background:#f1f5f9;border:1px solid #e2e8f0;color:' . $blue . ';font-size:14px;font-weight:600;text-decoration:none;border-radius:8px">&#x1F4DE; 587-983-7066</a>
        </td>
    </tr></table>

    <!-- PS -->
    <div style="border-top:1px solid #e2e8f0;padding-top:16px">
        <div style="font-size:12px;color:#94a3b8;line-height:1.7;font-style:italic">
            <strong style="color:#64748b">P.S.</strong> Don\'t bother visiting jasonhogan.ca &mdash; he\'s been too busy building systems that are far too impressive, and hasn\'t had time to update his own site. You know how it is. The cobbler\'s kids have no shoes. But trust me &mdash; the work he does for clients is on another level entirely.
        </div>
        <div style="font-size:11px;color:#cbd5e1;margin-top:12px;font-family:monospace">
            &#x1F916; // This message was composed by Jason\'s AI assistant. The opinions expressed are factually accurate. //
        </div>
    </div>

</td></tr>

<!-- Footer -->
<tr><td style="background:#f8fafc;border-radius:0 0 16px 16px;padding:24px 40px;text-align:center;border-top:1px solid #e2e8f0">
    <div style="font-size:12px;color:#64748b;line-height:1.8">
        <a href="https://www.linkedin.com/in/jasonhogan333" style="color:' . $blue . ';text-decoration:none">LinkedIn</a>
        &nbsp;&bull;&nbsp;
        <a href="https://jasonhogan.ca" style="color:' . $blue . ';text-decoration:none">jasonhogan.ca</a>
        &nbsp;&bull;&nbsp;
        <a href="mailto:me@jasonhogan.ca" style="color:' . $blue . ';text-decoration:none">me@jasonhogan.ca</a>
    </div>
    <div style="font-size:10px;color:#cbd5e1;margin-top:8px">&copy; ' . date('Y') . ' Jason Hogan &bull; Edmonton, AB, Canada</div>
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
