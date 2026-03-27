<?php

class BrandingController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $brandingService = new BrandingService();
        $branding = $brandingService->get($GLOBALS['client_id']);

        $this->view('branding/index', [
            'pageTitle' => 'Branding Settings',
            'branding' => $branding,
        ]);
    }

    public function save(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json(['error' => 'Invalid request.'], 403);
        }

        $brandingService = new BrandingService();
        $clientId = $GLOBALS['client_id'];

        $data = [
            'company_name' => trim($_POST['company_name'] ?? ''),
            'primary_color' => trim($_POST['primary_color'] ?? '#6366f1'),
            'secondary_color' => trim($_POST['secondary_color'] ?? '#8b5cf6'),
            'tagline' => trim($_POST['tagline'] ?? ''),
            'website' => trim($_POST['website'] ?? ''),
            'particles_enabled' => isset($_POST['particles_enabled']) ? 1 : 0,
        ];

        if (!empty($_FILES['logo']['tmp_name'])) {
            $logoPath = $this->handleUpload('logo');
            if ($logoPath) {
                $data['logo_url'] = $logoPath;
            }
        }

        if (!empty($_FILES['login_bg']['tmp_name'])) {
            $bgPath = $this->handleUpload('login_bg');
            if ($bgPath) {
                $data['login_bg_url'] = $bgPath;
            }
        }

        $brandingService->save($clientId, $data);

        // Update user first name if provided
        $firstName = trim($_POST['first_name'] ?? '');
        if ($firstName !== '' && isset($_SESSION['user_id'])) {
            $db = Database::connect();
            $stmt = $db->prepare('UPDATE users SET first_name = ? WHERE id = ?');
            $stmt->execute([$firstName, $_SESSION['user_id']]);
            $_SESSION['first_name'] = $firstName;
        }

        $this->redirect('/branding?saved=1');
    }

    public function saveApi(): void
    {
        $this->requireAuth();

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || ($input['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'Invalid request'], 403);
            return;
        }

        $envFile = APP_ROOT . '/config/env.php';
        $envContent = file_get_contents($envFile);

        // Update OpenRouter key
        $orKey = trim($input['openrouter_key'] ?? '');
        $envContent = preg_replace(
            "/define\('OPENROUTER_API_KEY',\s*'[^']*'\)/",
            "define('OPENROUTER_API_KEY', '" . addslashes($orKey) . "')",
            $envContent
        );

        // Update OpenRouter model
        $orModel = trim($input['openrouter_model'] ?? 'openai/gpt-4o-mini');
        $envContent = preg_replace(
            "/define\('OPENROUTER_MODEL',\s*'[^']*'\)/",
            "define('OPENROUTER_MODEL', '" . addslashes($orModel) . "')",
            $envContent
        );

        // Update Kie key
        $kieKey = trim($input['kie_key'] ?? '');
        $envContent = preg_replace(
            "/define\('KIE_API_KEY',\s*'[^']*'\)/",
            "define('KIE_API_KEY', '" . addslashes($kieKey) . "')",
            $envContent
        );

        if (file_put_contents($envFile, $envContent)) {
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'error' => 'Could not write config file']);
        }
    }

    public function testApi(): void
    {
        $this->requireAuth();

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || ($input['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['ok' => false, 'error' => 'Invalid request'], 403);
            return;
        }

        $service = $input['service'] ?? '';
        $key = trim($input['key'] ?? '');

        if ($service === 'openrouter') {
            if (empty($key)) {
                $this->json(['ok' => false, 'error' => 'No key provided']);
                return;
            }
            // Quick test call
            $payload = json_encode([
                'model' => OPENROUTER_MODEL,
                'messages' => [['role' => 'user', 'content' => 'Respond with exactly: OK']],
                'max_tokens' => 10,
            ]);
            $ch = curl_init(OPENROUTER_URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $key,
                ],
                CURLOPT_TIMEOUT => 15,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $this->json(['ok' => true]);
            } else {
                $data = json_decode($response, true);
                $err = $data['error']['message'] ?? "HTTP {$httpCode}";
                $this->json(['ok' => false, 'error' => $err]);
            }
        } elseif ($service === 'kie') {
            if (empty($key)) {
                $this->json(['ok' => false, 'error' => 'No key provided']);
                return;
            }
            // Test by creating a minimal task
            $payload = json_encode([
                'model' => KIE_MODEL,
                'input' => ['prompt' => 'A simple blue circle on white background, test', 'resolution' => '1K'],
            ]);
            $ch = curl_init(KIE_CREATE_URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $key,
                ],
                CURLOPT_TIMEOUT => 15,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode($response, true);
            if ($httpCode === 200 && ($data['code'] ?? 0) === 200) {
                $this->json(['ok' => true]);
            } else {
                $err = $data['msg'] ?? "HTTP {$httpCode}";
                $this->json(['ok' => false, 'error' => $err]);
            }
        } else {
            $this->json(['ok' => false, 'error' => 'Unknown service']);
        }
    }

    private function handleUpload(string $field): ?string
    {
        $file = $_FILES[$field] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        if (!in_array($file['type'], $allowed, true)) {
            return null;
        }

        if ($file['size'] > MAX_UPLOAD_SIZE) {
            return null;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $field . '_' . time() . '.' . $ext;
        $destination = UPLOAD_DIR . $filename;

        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return BASE_URL . '/uploads/' . $filename;
        }

        return null;
    }
}
