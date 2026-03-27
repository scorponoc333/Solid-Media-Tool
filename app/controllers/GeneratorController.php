<?php

class GeneratorController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->view('generator/index', [
            'pageTitle' => 'Content Generator',
        ]);
    }

    public function generateWeek(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json(['error' => 'Invalid request.'], 403);
        }

        $aiService = new AIService();
        $brandingService = new BrandingService();
        $memoryService = new ContentMemoryService();
        $clientId = $GLOBALS['client_id'];

        $brandContext = $brandingService->getContext($clientId);
        $memoryContext = $memoryService->getContext($clientId);

        $posts = $aiService->generateWeekContent($brandContext, $memoryContext);

        $this->json(['posts' => $posts]);
    }

    public function generateSingle(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json(['error' => 'Invalid request.'], 403);
        }

        $topic = trim($_POST['topic'] ?? '');
        $postType = $_POST['post_type'] ?? 'educational';

        if (!$topic) {
            $this->json(['error' => 'Please enter a topic.'], 400);
        }

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

        $content = $_POST['content'] ?? '';
        $instructions = $_POST['instructions'] ?? 'Rewrite this with a fresh angle';

        $aiService = new AIService();
        $newContent = $aiService->regenerateText($content, $instructions);

        $this->json(['content' => $newContent]);
    }

    public function regenerateImage(): void
    {
        $this->requireAuth();

        $prompt = $_POST['prompt'] ?? '';

        $aiService = new AIService();
        $imageUrl = $aiService->generateImage($prompt);

        $this->json(['image_url' => $imageUrl]);
    }
}
