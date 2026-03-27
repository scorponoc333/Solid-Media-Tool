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

        // Build first comment from branding
        $branding = (new BrandingService())->get($GLOBALS['client_id'] ?? 1);
        $phone = $branding['phone'] ?? '587-557-1234';
        $website = $branding['website'] ?? 'solidtech.ca';
        $this->firstComment = "\xF0\x9F\x93\x9E {$phone}\n\xF0\x9F\x8C\x90 https://{$website}";
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
    public function postNow(string $platform, string $content, ?string $imageUrl = null): array
    {
        $accountId = $this->getAccountId($platform);
        if (!$accountId) {
            return ['success' => false, 'error' => "No Zernio account configured for {$platform}"];
        }

        $payload = [
            'content' => $content,
            'platforms' => [
                [
                    'platform' => strtolower($platform),
                    'accountId' => $accountId,
                    'platformSpecificData' => [
                        'firstComment' => $this->firstComment,
                    ],
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
    public function schedulePost(string $platform, string $content, string $scheduledAt, ?string $imageUrl = null, ?string $timezone = null): array
    {
        $accountId = $this->getAccountId($platform);
        if (!$accountId) {
            return ['success' => false, 'error' => "No Zernio account configured for {$platform}"];
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
                    'platformSpecificData' => [
                        'firstComment' => $this->firstComment,
                    ],
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
     * If the URL is localhost, upload the file to imgBB for a public link.
     * In production (SiteGround), URLs are already public.
     */
    public function resolveImageUrl(?string $imageUrl): ?string
    {
        if (!$imageUrl) return null;

        // Already public
        if (!str_contains($imageUrl, 'localhost') && !str_contains($imageUrl, '127.0.0.1')) {
            return $imageUrl;
        }

        // Convert localhost URL to local file path
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
        $urlPath = parse_url($imageUrl, PHP_URL_PATH) ?: '';
        if ($basePath && str_starts_with($urlPath, $basePath)) {
            $relativePath = substr($urlPath, strlen($basePath));
        } else {
            $relativePath = $urlPath;
        }
        $localFile = APP_ROOT . '/public' . $relativePath;

        if (!file_exists($localFile)) {
            return null;
        }

        // Upload to 0x0.st (free file host, works for dev)
        // In production on SiteGround, this code won't run since URLs are already public
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://0x0.st',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['file' => new CURLFile($localFile)],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $publicUrl = trim(curl_exec($ch));
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && str_starts_with($publicUrl, 'http')) {
            return $publicUrl;
        }

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
            $data['success'] = true;
            $data['http_code'] = $httpCode;
            // Extract zernio_post_id from response
            if (isset($data['post']['_id'])) {
                $data['zernio_post_id'] = $data['post']['_id'];
            } elseif (isset($data['id'])) {
                $data['zernio_post_id'] = $data['id'];
            }
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
