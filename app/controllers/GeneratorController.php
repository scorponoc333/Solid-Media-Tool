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

    /**
     * Start an async image generation job — returns immediately with job ID.
     */
    public function startImageJob(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'editor');

        $prompt = $_POST['prompt'] ?? '';
        $uid = $_POST['uid'] ?? '';

        if (empty($prompt)) {
            @ob_clean();
            $this->json(['error' => 'No prompt provided'], 400);
            return;
        }

        session_write_close();

        $aiService = new AIService();

        // Clean prompt (same as generateImage)
        $cleanPrompt = $prompt;
        $cleanPrompt = preg_replace('/#\w+\s*/u', '', $cleanPrompt);
        $cleanPrompt = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{27BF}\x{FE00}-\x{FE0F}\x{1F900}-\x{1F9FF}\x{200D}\x{20E3}\x{2702}-\x{27B0}\x{2300}-\x{23FF}]/u', '', $cleanPrompt);
        $cleanPrompt = preg_replace('/\d{3}[-.]?\d{3}[-.]?\d{4}/', '', $cleanPrompt);
        $cleanPrompt = preg_replace('#https?://\S+#i', '', $cleanPrompt);
        $cleanPrompt = preg_replace('/\b(call us|contact us|visit|schedule|sign up|learn more|click here|check out)\b.*/i', '', $cleanPrompt);
        $cleanPrompt = preg_replace('/\s+/', ' ', trim($cleanPrompt));
        if (mb_strlen($cleanPrompt) > 300) $cleanPrompt = mb_substr($cleanPrompt, 0, 300);
        if (empty($cleanPrompt)) $cleanPrompt = $prompt;

        $artService = new ArtDirectionService();
        $artModifiers = $artService->buildImagePromptModifiers($GLOBALS['client_id']);
        if (!empty($artModifiers)) {
            $cleanPrompt .= '. ' . mb_substr($artModifiers, 0, 200);
        }

        // Create Kie.ai task (returns instantly)
        $createResult = $aiService->kieCreateTask($cleanPrompt);
        if (is_array($createResult) && isset($createResult['error'])) {
            @ob_clean();
            $this->json(['error' => $createResult['error'], 'error_code' => $createResult['code'] ?? 'UNKNOWN'], 500);
            return;
        }

        $taskId = $createResult;

        // Store job in database
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO image_jobs (client_id, uid, kie_task_id, prompt, status) VALUES (?, ?, ?, ?, 'processing')");
        $stmt->execute([$GLOBALS['client_id'], $uid, $taskId, $cleanPrompt]);
        $jobId = $db->lastInsertId();

        @ob_clean();
        $this->json(['job_id' => (int)$jobId, 'kie_task_id' => $taskId, 'uid' => $uid]);
    }

    /**
     * Check status of image generation jobs — lightweight poll endpoint.
     */
    public function checkImageJobs(): void
    {
        $this->requireAuth();

        $clientId = $GLOBALS['client_id'];
        $db = Database::connect();

        // Get all processing jobs for this client
        $stmt = $db->prepare("SELECT * FROM image_jobs WHERE client_id = ? AND status = 'processing' ORDER BY created_at ASC");
        $stmt->execute([$clientId]);
        $jobs = $stmt->fetchAll();

        $aiService = new AIService();
        $results = [];

        foreach ($jobs as $job) {
            // Quick single poll (no blocking wait)
            $pollResult = $aiService->kiePollResult($job['kie_task_id'], 3); // 3 second max

            if (is_string($pollResult) && !empty($pollResult)) {
                // Image is ready — download and watermark
                $localPath = $aiService->downloadImage($pollResult);
                $finalUrl = $localPath ?: $pollResult;

                // Watermark
                if ($localPath) {
                    $watermarked = $aiService->watermarkImage($localPath);
                    if ($watermarked) $finalUrl = $watermarked;
                }

                // Update job as completed
                $upd = $db->prepare("UPDATE image_jobs SET status = 'completed', image_url = ? WHERE id = ?");
                $upd->execute([$finalUrl, $job['id']]);

                $results[] = ['job_id' => (int)$job['id'], 'uid' => $job['uid'], 'status' => 'completed', 'image_url' => $finalUrl];
            } elseif (is_array($pollResult) && isset($pollResult['error'])) {
                // Failed
                $upd = $db->prepare("UPDATE image_jobs SET status = 'failed', error_message = ? WHERE id = ?");
                $upd->execute([$pollResult['error'], $job['id']]);

                $results[] = ['job_id' => (int)$job['id'], 'uid' => $job['uid'], 'status' => 'failed', 'error' => $pollResult['error']];
            } else {
                // Still processing
                $results[] = ['job_id' => (int)$job['id'], 'uid' => $job['uid'], 'status' => 'processing'];
            }
        }

        // Also get recently completed jobs (last 5 minutes) so the client can pick them up
        $stmt2 = $db->prepare("SELECT id, uid, status, image_url, error_message FROM image_jobs WHERE client_id = ? AND status IN ('completed','failed') AND updated_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY updated_at DESC");
        $stmt2->execute([$clientId]);
        $recent = $stmt2->fetchAll();

        foreach ($recent as $r) {
            // Don't duplicate
            $found = false;
            foreach ($results as $existing) {
                if ($existing['job_id'] === (int)$r['id']) { $found = true; break; }
            }
            if (!$found) {
                $results[] = [
                    'job_id' => (int)$r['id'],
                    'uid' => $r['uid'],
                    'status' => $r['status'],
                    'image_url' => $r['image_url'] ?? null,
                    'error' => $r['error_message'] ?? null,
                ];
            }
        }

        @ob_clean();
        $this->json(['jobs' => $results]);
    }
}
