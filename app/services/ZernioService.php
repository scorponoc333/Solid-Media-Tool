<?php

class ZernioService
{
    private string $apiKey;
    private string $baseUrl;
    private string $firstComment;

    public function __construct()
    {
        $this->apiKey = ZERNIO_API_KEY;
        $this->baseUrl = rtrim(ZERNIO_API_URL, '/');

        // Load default first comment from branding settings
        $branding = (new BrandingService())->get($GLOBALS['client_id'] ?? 1);
        $this->firstComment = $branding['first_comment'] ?? '';
    }

    /**
     * Get the Zernio account ID for a given platform
     */
    public function getAccountId(string $platform): ?string
    {
        $platform = strtolower(trim($platform));
        return match ($platform) {
            'facebook' => defined('ZERNIO_FACEBOOK_ACCOUNT_ID') && ZERNIO_FACEBOOK_ACCOUNT_ID ? ZERNIO_FACEBOOK_ACCOUNT_ID : null,
            'linkedin' => defined('ZERNIO_LINKEDIN_ACCOUNT_ID') && ZERNIO_LINKEDIN_ACCOUNT_ID ? ZERNIO_LINKEDIN_ACCOUNT_ID : null,
            default => null,
        };
    }

    /**
     * Post immediately to a single platform
     */
    public function postNow(string $platform, string $content, ?string $imageUrl = null, ?string $firstComment = null): array
    {
        $accountId = $this->getAccountId($platform);
        if (!$accountId) {
            return ['success' => false, 'error' => "No Zernio account configured for {$platform}"];
        }

        // Use post-level override, fall back to branding default
        $comment = $firstComment ?? $this->firstComment;

        $platformData = [];
        if (!empty($comment)) {
            $platformData['firstComment'] = $comment;
        }

        $payload = [
            'content' => $content,
            'platforms' => [
                [
                    'platform' => strtolower($platform),
                    'accountId' => $accountId,
                    'platformSpecificData' => $platformData,
                ]
            ],
            'publishNow' => true,
        ];

        $publicImgUrl = $this->resolveImageUrl($imageUrl);
        if ($publicImgUrl) {
            $payload['mediaItems'] = [['url' => $publicImgUrl]];
        }

        return $this->makeRequest('POST', '/posts', $payload);
    }

    /**
     * Schedule a post for a future time on a single platform
     */
    public function schedulePost(string $platform, string $content, string $scheduledAt, ?string $imageUrl = null, ?string $timezone = null, ?string $firstComment = null): array
    {
        $accountId = $this->getAccountId($platform);
        if (!$accountId) {
            return ['success' => false, 'error' => "No Zernio account configured for {$platform}"];
        }

        // Use post-level override, fall back to branding default
        $comment = $firstComment ?? $this->firstComment;

        $platformData = [];
        if (!empty($comment)) {
            $platformData['firstComment'] = $comment;
        }

        // Convert to ISO 8601
        $dt = new DateTime($scheduledAt);
        $isoDate = $dt->format('c');

        $payload = [
            'content' => $content,
            'platforms' => [
                [
                    'platform' => strtolower($platform),
                    'accountId' => $accountId,
                    'platformSpecificData' => $platformData,
                ]
            ],
            'scheduledFor' => $isoDate,
        ];

        if ($timezone) {
            $payload['timezone'] = $timezone;
        }

        $publicImgUrl = $this->resolveImageUrl($imageUrl);
        if ($publicImgUrl) {
            $payload['mediaItems'] = [['url' => $publicImgUrl]];
        }

        return $this->makeRequest('POST', '/posts', $payload);
    }

    /**
     * Post to multiple platforms at once (immediate)
     */
    public function postNowMulti(array $platforms, string $content, ?string $imageUrl = null): array
    {
        $results = [];
        foreach ($platforms as $platform) {
            $results[$platform] = $this->postNow($platform, $content, $imageUrl);
        }
        return $results;
    }

    /**
     * Schedule to multiple platforms at once
     */
    public function scheduleMulti(array $platforms, string $content, string $scheduledAt, ?string $imageUrl = null): array
    {
        $results = [];
        foreach ($platforms as $platform) {
            $results[$platform] = $this->schedulePost($platform, $content, $scheduledAt, $imageUrl);
        }
        return $results;
    }

    /**
     * Get status of a Zernio post
     */
    public function getPostStatus(string $zernioPostId): array
    {
        return $this->makeRequest('GET', '/posts/' . $zernioPostId);
    }

    /**
     * Cancel/delete a scheduled post
     */
    public function cancelPost(string $zernioPostId): array
    {
        return $this->makeRequest('DELETE', '/posts/' . $zernioPostId);
    }

    /**
     * Retry a failed post
     */
    public function retryPost(string $zernioPostId): array
    {
        return $this->makeRequest('POST', '/posts/' . $zernioPostId . '/retry');
    }

    /**
     * List connected accounts
     */
    public function listAccounts(): array
    {
        return $this->makeRequest('GET', '/accounts');
    }

    /**
     * Log a posting attempt to the database
     */
    public function logPostAttempt(int $postId, string $platform, string $status, ?string $accountId = null, ?string $zernioPostId = null, ?string $response = null, ?string $error = null): int
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO social_post_logs (post_id, platform, account_id, zernio_post_id, status, response, error_message) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$postId, $platform, $accountId, $zernioPostId, $status, $response, $error]);
        return (int) $db->lastInsertId();
    }

    /**
     * Get post logs for a specific post
     */
    public function getPostLogs(int $postId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM social_post_logs WHERE post_id = ? ORDER BY created_at DESC");
        $stmt->execute([$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Resolve an image URL to a publicly accessible one.
     * If the URL is localhost, upload the file to a public host so Zernio can fetch it.
     * In production (SiteGround), URLs are already public.
     */
    public function resolveImageUrl(?string $imageUrl): ?string
    {
        if (!$imageUrl) {
            error_log("ZernioService::resolveImageUrl — no image URL provided");
            return null;
        }

        // Already public
        if (!str_contains($imageUrl, 'localhost') && !str_contains($imageUrl, '127.0.0.1')) {
            return $imageUrl;
        }

        // Convert localhost URL to local file path
        $localFile = $this->resolveLocalFilePath($imageUrl);

        if (!$localFile || !file_exists($localFile)) {
            error_log("ZernioService::resolveImageUrl — local file not found: " . ($localFile ?: 'null') . " (from URL: {$imageUrl})");
            return null;
        }

        error_log("ZernioService::resolveImageUrl — uploading local file: {$localFile} (" . filesize($localFile) . " bytes)");

        // Try multiple public hosts in order of reliability
        $publicUrl = $this->uploadToCatbox($localFile)
                  ?? $this->uploadToLitterbox($localFile)
                  ?? $this->uploadToFileIO($localFile);

        if ($publicUrl) {
            error_log("ZernioService::resolveImageUrl — public URL obtained: {$publicUrl}");
        } else {
            error_log("ZernioService::resolveImageUrl — all upload methods failed for: {$localFile}");
        }

        return $publicUrl;
    }

    /**
     * Convert a localhost URL to a local file path.
     */
    private function resolveLocalFilePath(string $imageUrl): ?string
    {
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
        $urlPath = parse_url($imageUrl, PHP_URL_PATH) ?: '';
        if ($basePath && str_starts_with($urlPath, $basePath)) {
            $relativePath = substr($urlPath, strlen($basePath));
        } else {
            $relativePath = $urlPath;
        }
        return APP_ROOT . '/public' . $relativePath;
    }

    /**
     * Upload to file.io (temporary file host — reliable, files auto-expire)
     */
    private function uploadToFileIO(string $localFile): ?string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://file.io',
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => ['file' => new CURLFile($localFile)],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['User-Agent: SolidTech-Social/1.0'],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("ZernioService::uploadToFileIO — cURL error: {$curlError}");
            return null;
        }

        $data = json_decode($response, true);
        if ($httpCode === 200 && !empty($data['success']) && !empty($data['link'])) {
            error_log("ZernioService::uploadToFileIO — success: {$data['link']}");
            return $data['link'];
        }

        error_log("ZernioService::uploadToFileIO — failed [{$httpCode}]: " . substr($response, 0, 500));
        return null;
    }

    /**
     * Upload to catbox.moe (reliable free file host, no expiry)
     */
    private function uploadToCatbox(string $localFile): ?string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://catbox.moe/user/api.php',
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'reqtype'  => 'fileupload',
                'fileToUpload' => new CURLFile($localFile),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['User-Agent: SolidTech-Social/1.0'],
        ]);
        $publicUrl = trim(curl_exec($ch));
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("ZernioService::uploadToCatbox — cURL error: {$curlError}");
            return null;
        }

        if ($httpCode === 200 && str_starts_with($publicUrl, 'https://files.catbox.moe/')) {
            error_log("ZernioService::uploadToCatbox — success: {$publicUrl}");
            return $publicUrl;
        }

        error_log("ZernioService::uploadToCatbox — failed [{$httpCode}]: " . substr($publicUrl, 0, 500));
        return null;
    }

    /**
     * Upload to litterbox.catbox.moe (temporary file host, 24h expiry — fallback)
     */
    private function uploadToLitterbox(string $localFile): ?string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://litterbox.catbox.moe/resources/internals/api.php',
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'reqtype'  => 'fileupload',
                'time'     => '24h',
                'fileToUpload' => new CURLFile($localFile),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['User-Agent: SolidTech-Social/1.0'],
        ]);
        $publicUrl = trim(curl_exec($ch));
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("ZernioService::uploadToLitterbox — cURL error: {$curlError}");
            return null;
        }

        if ($httpCode === 200 && str_starts_with($publicUrl, 'https://litter.catbox.moe/')) {
            error_log("ZernioService::uploadToLitterbox — success: {$publicUrl}");
            return $publicUrl;
        }

        error_log("ZernioService::uploadToLitterbox — failed [{$httpCode}]: " . substr($publicUrl, 0, 500));
        return null;
    }

    /**
     * Check if a URL is publicly accessible (not localhost)
     */
    private function isPublicUrl(?string $url): bool
    {
        if (!$url) return false;
        return !str_contains($url, 'localhost') && !str_contains($url, '127.0.0.1');
    }

    /**
     * Make an HTTP request to Zernio API
     */
    private function makeRequest(string $method, string $endpoint, ?array $payload = null): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'Zernio API key not configured'];
        }

        $url = $this->baseUrl . $endpoint;

        $ch = curl_init();
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($payload) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            }
        } elseif ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($payload) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'cURL error: ' . $curlError, 'http_code' => 0];
        }

        $data = json_decode($response, true) ?? [];

        if ($httpCode >= 200 && $httpCode < 300) {
            $data['http_code'] = $httpCode;
            // Extract zernio_post_id from response
            if (isset($data['post']['_id'])) {
                $data['zernio_post_id'] = $data['post']['_id'];
            } elseif (isset($data['id'])) {
                $data['zernio_post_id'] = $data['id'];
            }

            // HTTP 207 means the API accepted the request but platform publishing may have failed.
            // Check the actual post status and platform results to determine real success.
            if ($httpCode === 207) {
                $postStatus = $data['post']['status'] ?? null;
                $platformResults = $data['platformResults'] ?? [];
                $anyPlatformFailed = false;
                $errors = [];

                foreach ($platformResults as $pr) {
                    if (($pr['status'] ?? '') === 'failed') {
                        $anyPlatformFailed = true;
                        $errors[] = ($pr['platform'] ?? 'unknown') . ': ' . ($pr['error'] ?? 'Unknown error');
                    }
                }

                if ($postStatus === 'failed' || $anyPlatformFailed) {
                    $data['success'] = false;
                    $data['error'] = implode('; ', $errors) ?: ($data['error'] ?? 'Publishing failed on platform');
                    return $data;
                }
            }

            $data['success'] = true;
            return $data;
        }

        return [
            'success' => false,
            'error' => $data['error'] ?? $data['message'] ?? "HTTP {$httpCode}",
            'http_code' => $httpCode,
            'raw_response' => $response,
        ];
    }
}
