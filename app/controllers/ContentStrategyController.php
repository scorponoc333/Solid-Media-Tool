<?php

class ContentStrategyController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $clientId = $GLOBALS['client_id'];
        $service = new ContentStrategyService();

        $themes = $service->getThemes($clientId);
        $schedule = $service->getSchedule($clientId);
        $branding = (new BrandingService())->get($clientId);

        $this->view('content-strategy/index', [
            'pageTitle' => 'Content Strategy',
            'themes' => $themes,
            'schedule' => $schedule,
            'branding' => $branding,
        ]);
    }

    public function saveTheme(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || ($input['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['error' => 'Invalid request.'], 403);
            return;
        }

        $clientId = $GLOBALS['client_id'];
        $service = new ContentStrategyService();

        $themeId = !empty($input['theme_id']) ? (int)$input['theme_id'] : null;

        // Build required_elements from input
        $requiredElements = $input['required_elements'] ?? [];

        $data = [
            'name' => $input['name'] ?? '',
            'description' => $input['description'] ?? '',
            'copy_instructions' => $input['copy_instructions'] ?? '',
            'required_elements' => $requiredElements,
            'default_hashtags' => $input['default_hashtags'] ?? '',
            'image_style_override' => $input['image_style_override'] ?? 'global',
            'samples' => $input['samples'] ?? [],
        ];

        if (empty($data['name'])) {
            @ob_clean();
            $this->json(['error' => 'Theme name is required.'], 400);
            return;
        }

        if ($themeId) {
            $service->updateTheme($themeId, $clientId, $data);
        } else {
            $themeId = $service->createTheme($clientId, $data);
        }

        @ob_clean();
        $this->json(['success' => true, 'theme_id' => $themeId]);
    }

    public function deleteTheme(string $id): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || ($input['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['error' => 'Invalid request.'], 403);
            return;
        }

        $clientId = $GLOBALS['client_id'];
        $service = new ContentStrategyService();
        $service->deleteTheme((int)$id, $clientId);

        @ob_clean();
        $this->json(['success' => true]);
    }

    public function saveSchedule(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || ($input['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['error' => 'Invalid request.'], 403);
            return;
        }

        $clientId = $GLOBALS['client_id'];
        $service = new ContentStrategyService();
        $service->saveSchedule($clientId, $input['schedule'] ?? []);

        @ob_clean();
        $this->json(['success' => true]);
    }

    public function critique(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || ($input['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['error' => 'Invalid request.'], 403);
            return;
        }

        $content = $input['content'] ?? '';
        if (empty(trim($content))) {
            @ob_clean();
            $this->json(['error' => 'No content to analyze.'], 400);
            return;
        }

        $themeContext = null;
        if (!empty($input['theme_id'])) {
            $service = new ContentStrategyService();
            $themeContext = $service->getTheme((int)$input['theme_id'], $GLOBALS['client_id']);
        }

        $service = new ContentStrategyService();
        $result = $service->critiquePost($content, $themeContext);

        @ob_clean();
        $this->json($result);
    }
}
