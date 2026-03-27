<?php

class AIService
{
    private string $openRouterKey;
    private string $openRouterModel;
    private string $kieKey;

    public function __construct()
    {
        $this->openRouterKey  = OPENROUTER_API_KEY;
        $this->openRouterModel = OPENROUTER_MODEL;
        $this->kieKey = KIE_API_KEY;
    }

    // ──────────────────────────────────────────
    //  TEXT GENERATION  (OpenRouter)
    // ──────────────────────────────────────────

    public function generateWeekContent(array $brandContext, array $memoryContext): array
    {
        $company  = $brandContext['company_name'] ?? 'the company';
        $days     = ['Monday', 'Wednesday', 'Friday'];
        $types    = ['educational', 'promotional', 'engagement'];
        $results  = [];

        // Build memory exclusion list — flatten DB rows to simple strings
        $rawTopics = $memoryContext['topics'] ?? [];
        $rawAngles = $memoryContext['recent_angles'] ?? [];
        $usedTopics = array_column($rawTopics, 'topic');
        $usedAngles = array_column($rawAngles, 'angle');
        $exclusion  = '';
        if ($usedTopics) {
            $exclusion .= "\nAVOID these recently-used topics: " . implode(', ', array_slice($usedTopics, 0, 15));
        }
        if ($usedAngles) {
            $exclusion .= "\nAVOID these recently-used angles: " . implode(', ', array_slice($usedAngles, 0, 10));
        }

        $systemPrompt = "You are a senior social media strategist for {$company}. "
            . "Create engaging, on-brand social media posts. "
            . "Return valid JSON only — no markdown fences, no extra text.";

        $userPrompt = "Generate 3 social media posts for {$company} to publish on Monday, Wednesday, and Friday.\n"
            . "Each post must have a DIFFERENT topic and angle.\n"
            . "Post types in order: educational, promotional, engagement.\n"
            . $exclusion . "\n\n"
            . "Return a JSON array of 3 objects, each with these exact keys:\n"
            . "day, title, content, post_type, topic, keywords, angle, image_prompt\n"
            . "- title: catchy headline (max 80 chars)\n"
            . "- content: full post caption (120-280 chars, include relevant hashtags)\n"
            . "- topic: one-word or short phrase topic\n"
            . "- keywords: comma-separated relevant keywords\n"
            . "- angle: the creative approach used\n"
            . "- image_prompt: a detailed prompt to generate a matching image\n";

        $response = $this->callOpenRouter($systemPrompt, $userPrompt);

        if ($response !== null) {
            $decoded = $this->parseJson($response);
            if (is_array($decoded) && count($decoded) >= 1) {
                return $decoded;
            }
        }

        // Fallback if API fails or key missing
        return $this->fallbackWeekContent($company, $days, $types);
    }

    public function generateSinglePost(string $topic, string $postType, array $brandContext, array $memoryContext): array
    {
        $company = $brandContext['company_name'] ?? 'the company';

        $rawAngles = $memoryContext['recent_angles'] ?? [];
        $usedAngles = array_column($rawAngles, 'angle');
        $exclusion  = $usedAngles ? "\nAVOID these angles: " . implode(', ', array_slice($usedAngles, 0, 10)) : '';

        $systemPrompt = "You are a senior social media strategist for {$company}. "
            . "Return valid JSON only — no markdown fences, no extra text.";

        $userPrompt = "Create one {$postType} social media post about \"{$topic}\" for {$company}.\n"
            . $exclusion . "\n\n"
            . "Return a single JSON object with these keys:\n"
            . "title, content, post_type, topic, keywords, angle, image_prompt\n"
            . "- title: catchy headline (max 80 chars)\n"
            . "- content: full post caption (120-280 chars, include relevant hashtags)\n"
            . "- topic: \"{$topic}\"\n"
            . "- post_type: \"{$postType}\"\n"
            . "- keywords: comma-separated relevant keywords\n"
            . "- angle: the creative approach used\n"
            . "- image_prompt: detailed prompt to generate a matching image\n";

        $response = $this->callOpenRouter($systemPrompt, $userPrompt);

        if ($response !== null) {
            $decoded = $this->parseJson($response);
            if (is_array($decoded) && isset($decoded['title'])) {
                return $decoded;
            }
            // Might have returned wrapped in an array
            if (is_array($decoded) && isset($decoded[0]['title'])) {
                return $decoded[0];
            }
        }

        // Fallback
        return [
            'title' => "Post about {$topic}",
            'content' => "AI generation unavailable. Please check your OpenRouter API key in Branding > API Settings. Topic: {$topic}",
            'post_type' => $postType,
            'topic' => $topic,
            'keywords' => $topic,
            'angle' => "{$postType} perspective",
            'image_prompt' => "Professional social media image about {$topic} for {$company}",
        ];
    }

    public function regenerateText(string $originalContent, string $instruction): string
    {
        $systemPrompt = "You are a social media copywriter. Rewrite the given post with a fresh angle. "
            . "Return ONLY the new post text, nothing else.";

        $userPrompt = "Original post:\n\"{$originalContent}\"\n\n"
            . "Instruction: {$instruction}\n\n"
            . "Rewrite this post with a fresh perspective. Keep it the same length. Include hashtags.";

        $response = $this->callOpenRouter($systemPrompt, $userPrompt);

        return $response ?? "Could not regenerate. Check your OpenRouter API key. Original: {$originalContent}";
    }

    private function callOpenRouter(string $system, string $user): ?string
    {
        if (empty($this->openRouterKey)) {
            return null;
        }

        $payload = json_encode([
            'model' => $this->openRouterModel,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'temperature' => 0.8,
            'max_tokens' => 2000,
        ]);

        $ch = curl_init(OPENROUTER_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openRouterKey,
                'HTTP-Referer: ' . BASE_URL,
                'X-OpenRouter-Title: ' . APP_NAME,
            ],
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            error_log("OpenRouter error [{$httpCode}]: {$error} | Response: " . substr($response, 0, 500));
            return null;
        }

        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? null;
    }

    // ──────────────────────────────────────────
    //  IMAGE GENERATION  (Kie.ai — NanoBanana2)
    // ──────────────────────────────────────────

    public function generateImage(string $prompt): string
    {
        if (empty($this->kieKey)) {
            return 'https://placehold.co/1080x1080/6366f1/ffffff?text=Add+Kie+API+Key';
        }

        // Step 1: Create task
        $taskId = $this->kieCreateTask($prompt);
        if (!$taskId) {
            return 'https://placehold.co/1080x1080/ef4444/ffffff?text=Image+Gen+Failed';
        }

        // Step 2: Poll for result (max ~60 seconds)
        $imageUrl = $this->kiePollResult($taskId);
        if (!$imageUrl) {
            return 'https://placehold.co/1080x1080/f59e0b/ffffff?text=Image+Timeout';
        }

        // Step 3: Download and save locally
        $localPath = $this->downloadImage($imageUrl);
        if (!$localPath) {
            return $imageUrl;
        }

        // Step 4: Watermark with logo + website
        $watermarked = $this->watermarkImage($localPath);
        return $watermarked ?: $localPath;
    }

    /**
     * Stamp the brand logo (bottom-left) and website (bottom-right)
     * onto the image with a subtle dark gradient fade at the bottom.
     */
    private function watermarkImage(string $imageWebUrl): ?string
    {
        // Get branding data
        $brandingService = new BrandingService();
        $brand = $brandingService->get($GLOBALS['client_id']);
        $logoUrl  = $brand['logo_url'] ?? '';
        $website  = $brand['website'] ?? '';

        // Nothing to stamp if no logo and no website
        if (empty($logoUrl) && empty($website)) {
            return $imageWebUrl;
        }

        // Convert web URL to local file path
        $filename = basename(parse_url($imageWebUrl, PHP_URL_PATH));
        $localFile = UPLOAD_DIR . $filename;

        if (!file_exists($localFile)) {
            return $imageWebUrl;
        }

        // Load image
        $info = getimagesize($localFile);
        if (!$info) return $imageWebUrl;

        $mime = $info['mime'];
        $img = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($localFile),
            'image/png'  => imagecreatefrompng($localFile),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($localFile) : null,
            default      => null,
        };
        if (!$img) return $imageWebUrl;

        $imgW = imagesx($img);
        $imgH = imagesy($img);

        // ── Dark gradient at the bottom (bottom 20% of image) ──
        $gradientHeight = (int)($imgH * 0.20);
        $gradientStart  = $imgH - $gradientHeight;

        for ($y = 0; $y < $gradientHeight; $y++) {
            // Alpha goes from 0 (transparent at top of gradient) to ~85 (dark at bottom)
            $alpha = (int)(85 * ($y / $gradientHeight));
            $color = imagecolorallocatealpha($img, 0, 0, 0, 127 - (int)(($alpha / 100) * 127));
            imageline($img, 0, $gradientStart + $y, $imgW, $gradientStart + $y, $color);
        }

        $padding = (int)($imgW * 0.03); // 3% padding from edges
        $bottomY = $imgH - $padding;

        // ── Logo (bottom-left) ──
        if (!empty($logoUrl)) {
            $logoFilename = basename(parse_url($logoUrl, PHP_URL_PATH));
            $logoLocal = UPLOAD_DIR . $logoFilename;

            if (file_exists($logoLocal)) {
                $logoInfo = getimagesize($logoLocal);
                if ($logoInfo) {
                    $logoMime = $logoInfo['mime'];
                    $logoImg = match ($logoMime) {
                        'image/jpeg' => imagecreatefromjpeg($logoLocal),
                        'image/png'  => imagecreatefrompng($logoLocal),
                        'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($logoLocal) : null,
                        'image/gif'  => imagecreatefromgif($logoLocal),
                        default      => null,
                    };

                    if ($logoImg) {
                        $logoOrigW = imagesx($logoImg);
                        $logoOrigH = imagesy($logoImg);

                        // Scale logo to ~24% of image width, max 100px tall
                        $targetW = (int)($imgW * 0.24);
                        $scale   = $targetW / $logoOrigW;
                        $targetH = (int)($logoOrigH * $scale);
                        if ($targetH > 100) {
                            $targetH = 100;
                            $targetW = (int)($logoOrigW * (100 / $logoOrigH));
                        }

                        $logoX = $padding;
                        $logoY = $bottomY - $targetH;

                        // Use imagecopyresampled for quality
                        imagecopyresampled(
                            $img, $logoImg,
                            $logoX, $logoY,
                            0, 0,
                            $targetW, $targetH,
                            $logoOrigW, $logoOrigH
                        );
                        imagedestroy($logoImg);
                    }
                }
            }
        }

        // ── Website text (bottom-right) ──
        if (!empty($website)) {
            $white = imagecolorallocate($img, 255, 255, 255);

            // Try to use a nice font if available, fallback to built-in
            $fontSize = max(14, (int)($imgW * 0.018));
            $fontFile = APP_ROOT . '/public/fonts/Inter-Variable.ttf';

            if (file_exists($fontFile)) {
                $bbox = imagettfbbox($fontSize, 0, $fontFile, $website);
                $textW = abs($bbox[2] - $bbox[0]);
                $textH = abs($bbox[7] - $bbox[1]);
                $textX = $imgW - $padding - $textW;
                $textY = $bottomY - (int)($textH * 0.3);
                imagettftext($img, $fontSize, 0, $textX, $textY, $white, $fontFile, $website);
            } else {
                // Built-in font fallback (font 5 is the largest built-in)
                $font = 5;
                $charW = imagefontwidth($font);
                $charH = imagefontheight($font);
                $textW = strlen($website) * $charW;
                $textX = $imgW - $padding - $textW;
                $textY = $bottomY - $charH;
                imagestring($img, $font, $textX, $textY, $website, $white);
            }
        }

        // Save the watermarked image (overwrite original)
        $saved = match ($mime) {
            'image/jpeg' => imagejpeg($img, $localFile, 92),
            'image/png'  => imagepng($img, $localFile, 2),
            'image/webp' => function_exists('imagewebp') ? imagewebp($img, $localFile, 90) : false,
            default      => false,
        };

        imagedestroy($img);

        return $saved ? $imageWebUrl : $imageWebUrl;
    }

    private function kieCreateTask(string $prompt): ?string
    {
        $payload = json_encode([
            'model' => KIE_MODEL,
            'input' => [
                'prompt'       => $prompt,
                'aspect_ratio' => '1:1',
                'resolution'   => '1K',
                'output_format' => 'jpg',
            ],
        ]);

        $ch = curl_init(KIE_CREATE_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->kieKey,
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            error_log("Kie.ai create error [{$httpCode}]: {$error} | " . substr($response, 0, 500));
            return null;
        }

        $data = json_decode($response, true);
        return $data['data']['taskId'] ?? null;
    }

    private function kiePollResult(string $taskId, int $maxWait = 60): ?string
    {
        $start = time();

        while (time() - $start < $maxWait) {
            $url = KIE_STATUS_URL . '?taskId=' . urlencode($taskId);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $this->kieKey,
                ],
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            $state = $data['data']['state'] ?? 'unknown';

            if ($state === 'success') {
                $resultJson = $data['data']['resultJson'] ?? '';
                if (is_string($resultJson)) {
                    $result = json_decode($resultJson, true);
                } else {
                    $result = $resultJson;
                }
                $urls = $result['resultUrls'] ?? [];
                return $urls[0] ?? null;
            }

            if ($state === 'fail') {
                $failMsg = $data['data']['failMsg'] ?? 'Unknown error';
                error_log("Kie.ai task {$taskId} failed: {$failMsg}");
                return null;
            }

            // Still processing — wait 3 seconds before checking again
            sleep(3);
        }

        error_log("Kie.ai task {$taskId} timed out after {$maxWait}s");
        return null;
    }

    private function downloadImage(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $imageData = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$imageData) {
            return null;
        }

        $ext = 'jpg';
        if (str_contains($contentType, 'png')) $ext = 'png';
        if (str_contains($contentType, 'webp')) $ext = 'webp';

        $filename = 'ai_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destination = UPLOAD_DIR . $filename;

        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        if (file_put_contents($destination, $imageData)) {
            return BASE_URL . '/uploads/' . $filename;
        }

        return null;
    }

    // ──────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────

    private function parseJson(string $raw): ?array
    {
        // Strip markdown code fences if present
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $cleaned = preg_replace('/\s*```$/', '', $cleaned);

        $decoded = json_decode($cleaned, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Try to find JSON in the response
        if (preg_match('/\[[\s\S]*\]/', $cleaned, $m)) {
            $decoded = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE) return $decoded;
        }
        if (preg_match('/\{[\s\S]*\}/', $cleaned, $m)) {
            $decoded = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE) return $decoded;
        }

        error_log("AIService JSON parse failed: " . substr($raw, 0, 300));
        return null;
    }

    private function fallbackWeekContent(string $company, array $days, array $types): array
    {
        $results = [];
        foreach ($days as $i => $day) {
            $results[] = [
                'day'          => $day,
                'title'        => "Sample {$types[$i]} post for {$day}",
                'content'      => "AI generation unavailable. Add your OpenRouter API key in Branding > API Settings to generate real content for {$company}.",
                'post_type'    => $types[$i],
                'topic'        => 'general',
                'keywords'     => strtolower($company) . ', technology',
                'angle'        => $types[$i] . ' angle',
                'image_prompt' => "Professional {$types[$i]} social media image for {$company}",
            ];
        }
        return $results;
    }

    /**
     * Check API connectivity — used by the settings page.
     */
    public function testOpenRouter(): array
    {
        if (empty($this->openRouterKey)) {
            return ['ok' => false, 'error' => 'No API key configured'];
        }
        $response = $this->callOpenRouter('Respond with exactly: OK', 'Test');
        return $response !== null
            ? ['ok' => true, 'message' => 'Connected']
            : ['ok' => false, 'error' => 'API call failed — check key'];
    }

    public function testKie(): array
    {
        if (empty($this->kieKey)) {
            return ['ok' => false, 'error' => 'No API key configured'];
        }
        // Light test — just try creating a task (won't wait for result)
        $taskId = $this->kieCreateTask('A simple blue square on a white background, minimal, test image');
        return $taskId
            ? ['ok' => true, 'message' => 'Connected (task: ' . $taskId . ')']
            : ['ok' => false, 'error' => 'API call failed — check key'];
    }
}
