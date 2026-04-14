<?php

class GeneratorController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'editor');

        $clientId = $GLOBALS['client_id'];
        $strategyService = new ContentStrategyService();

        $themes = $strategyService->getActiveThemes($clientId);
        $schedule = $strategyService->getSchedule($clientId);

        $this->view('generator/index', [
            'pageTitle' => 'Content Generator',
            'themes' => $themes,
            'schedule' => $schedule,
        ]);
    }

    public function generateWeek(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'editor');

        // Read JSON input first (Plan & Generate sends JSON)
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        // Verify CSRF from either JSON body or POST
        $csrfToken = $input['csrf_token'] ?? ($_POST['csrf_token'] ?? '');
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
            $this->json(['error' => 'Invalid request.'], 403);
            return;
        }

        // Release session lock so browser isn't blocked during AI generation
        session_write_close();

        $aiService = new AIService();
        $brandingService = new BrandingService();
        $memoryService = new ContentMemoryService();
        $strategyService = new ContentStrategyService();
        $clientId = $GLOBALS['client_id'];

        $brandContext = $brandingService->getContext($clientId);
        $memoryContext = $memoryService->getContext($clientId);

        $days = $input['days'] ?? null;
        $themeIds = $input['theme_ids'] ?? null;

        $themeData = [];
        if (!empty($days) && is_array($days)) {
            foreach ($days as $i => $day) {
                $td = ['day' => $day];
                $tid = $themeIds[$i] ?? null;
                if ($tid) {
                    $theme = $strategyService->getTheme((int)$tid, $clientId);
                    if ($theme) {
                        $td['theme_name'] = $theme['name'];
                        $td['description'] = $theme['description'] ?? '';
                        $td['copy_instructions'] = $theme['copy_instructions'] ?? '';
                        $td['required_elements'] = $theme['required_elements'] ?? [];
                        $td['default_hashtags'] = $theme['default_hashtags'] ?? '';
                        $td['image_style_override'] = $theme['image_style_override'] ?? 'global';
                        $td['samples'] = $theme['samples'] ?? [];
                    }
                }
                $themeData[] = $td;
            }
        }

        $posts = $aiService->generateWeekContent($brandContext, $memoryContext, $themeData);

        @ob_clean();
        $this->json(['posts' => $posts]);
    }

    public function generateSingle(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'editor');

        if (!$this->verifyCsrf()) {
            $this->json(['error' => 'Invalid request.'], 403);
        }

        $topic = trim($_POST['topic'] ?? '');
        $postType = $_POST['post_type'] ?? 'educational';

        if (!$topic) {
            $this->json(['error' => 'Please enter a topic.'], 400);
        }

        session_write_close();

        $aiService = new AIService();
        $brandingService = new BrandingService();
        $memoryService = new ContentMemoryService();
        $clientId = $GLOBALS['client_id'];

        $brandContext = $brandingService->getContext($clientId);
        $memoryContext = $memoryService->getContext($clientId);

        $post = $aiService->generateSinglePost($topic, $postType, $brandContext, $memoryContext);

        $this->json(['post' => $post]);
    }

    public function regenerateText(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'editor');

        $content = $_POST['content'] ?? '';
        $instructions = $_POST['instructions'] ?? 'Rewrite this with a fresh angle';

        session_write_close();

        $aiService = new AIService();
        $newContent = $aiService->regenerateText($content, $instructions);

        $this->json(['content' => $newContent]);
    }

    public function regenerateImage(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'editor');

        $prompt = $_POST['prompt'] ?? '';

        // Release session lock so the browser isn't blocked during the long image generation
        session_write_close();

        $aiService = new AIService();
        $result = $aiService->generateImage($prompt);

        // Check if result is an error (starts with ERROR:)
        if (is_string($result) && str_starts_with($result, 'ERROR:')) {
            $errorData = json_decode(substr($result, 6), true) ?: ['error' => 'Unknown error', 'code' => 'UNKNOWN'];
            @ob_clean();
            $this->json([
                'error' => $errorData['error'] ?? 'Image generation failed',
                'error_code' => $errorData['code'] ?? 'UNKNOWN',
                'auto_retry' => $errorData['auto_retry'] ?? false,
            ], 500);
            return;
        }

        @ob_clean();
        $this->json(['image_url' => $result]);
    }
}
