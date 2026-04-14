<?php

class WizardService
{
    public function isSetupComplete(int $clientId): bool
    {
        $branding = (new BrandingService())->get($clientId);
        $name = trim($branding['company_name'] ?? '');
        // Setup is complete if company name is set and not the default
        return $name !== '' && $name !== 'SolidTech' && $name !== APP_NAME;
    }

    public function scanWebsite(string $url): array
    {
        $url = trim($url);
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        // Fetch HTML
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; SolidSocialBot/1.0)',
            CURLOPT_MAXREDIRS => 3,
        ]);
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 400 || empty($html)) {
            return ['error' => 'Could not access website. You can enter your information manually.'];
        }

        // Strip scripts/styles, truncate
        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        $html = strip_tags($html, '<title><meta><h1><h2><h3><p><li><a>');
        $html = preg_replace('/\s+/', ' ', $html);
        $html = mb_substr($html, 0, 5000);

        // Send to AI for extraction
        $systemPrompt = "You are a business analyst. Extract key information from this website content. "
            . "Return valid JSON only — no markdown fences.";

        $userPrompt = "Analyze this website content and extract:\n\n"
            . $html . "\n\n"
            . "Return a JSON object with these keys:\n"
            . "- \"company_name\": the company name\n"
            . "- \"services\": array of 3-8 key services offered\n"
            . "- \"about\": 2-3 sentence summary of what the company does\n"
            . "- \"phone\": phone number if found (or empty string)\n"
            . "- \"email\": email address if found (or empty string)\n"
            . "- \"industry\": the primary industry (e.g. IT, Healthcare, Finance)\n"
            . "- \"keywords\": array of 5-10 industry keywords for social media\n"
            . "- \"tagline\": a suggested tagline based on what you read\n"
            . "Return valid JSON only.";

        $payload = json_encode([
            'model' => OPENROUTER_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => 800,
            'temperature' => 0.3,
        ]);

        $ch = curl_init(OPENROUTER_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . OPENROUTER_API_KEY,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['error' => 'AI analysis failed. You can enter your information manually.'];
        }

        $data = json_decode($response, true);
        $text = $data['choices'][0]['message']['content'] ?? '';
        $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/', '', $text);
        $result = json_decode($text, true);

        return $result ?: ['error' => 'Could not parse website data. Enter your information manually.'];
    }

    public function suggestThemes(array $businessInfo): array
    {
        $systemPrompt = "You are a social media strategist. Suggest content themes for this business. "
            . "Return valid JSON only — no markdown fences.";

        $context = "Business: " . ($businessInfo['company_name'] ?? 'Unknown') . "\n"
            . "Industry: " . ($businessInfo['industry'] ?? 'General') . "\n"
            . "Services: " . implode(', ', $businessInfo['services'] ?? []) . "\n"
            . "About: " . ($businessInfo['about'] ?? '') . "\n"
            . "Keywords: " . implode(', ', $businessInfo['keywords'] ?? []) . "\n";

        $userPrompt = $context . "\n"
            . "Suggest 5-7 social media content themes for this business.\n"
            . "Return a JSON array of objects, each with:\n"
            . "- \"name\": short theme name (2-4 words)\n"
            . "- \"description\": 1-2 sentence description of what content falls under this theme\n"
            . "- \"copy_instructions\": specific guidance for writing posts in this theme\n"
            . "- \"suggested_hashtags\": 4-5 relevant hashtags as a string\n"
            . "Return valid JSON only.";

        $payload = json_encode([
            'model' => OPENROUTER_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => 1500,
            'temperature' => 0.7,
        ]);

        $ch = curl_init(OPENROUTER_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . OPENROUTER_API_KEY,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return [];
        }

        $data = json_decode($response, true);
        $text = $data['choices'][0]['message']['content'] ?? '';
        $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/', '', $text);
        $result = json_decode($text, true);

        return is_array($result) ? $result : [];
    }

    public function saveWizardData(int $clientId, array $data): void
    {
        // 1. Save branding
        $brandingService = new BrandingService();
        $brandingData = [];
        if (!empty($data['company_name'])) $brandingData['company_name'] = trim($data['company_name']);
        if (!empty($data['website'])) $brandingData['website'] = trim($data['website']);
        if (!empty($data['phone'])) $brandingData['phone'] = trim($data['phone']);
        if (!empty($data['tagline'])) $brandingData['tagline'] = trim($data['tagline']);
        if (!empty($data['primary_color'])) $brandingData['primary_color'] = trim($data['primary_color']);
        if (!empty($data['secondary_color'])) $brandingData['secondary_color'] = trim($data['secondary_color']);
        if (!empty($data['favicon_url'])) $brandingData['favicon_url'] = trim($data['favicon_url']);
        if (!empty($brandingData)) {
            $brandingService->save($clientId, $brandingData);
        }

        // 2. Create selected themes
        if (!empty($data['themes']) && is_array($data['themes'])) {
            $strategyService = new ContentStrategyService();
            $order = 0;
            foreach ($data['themes'] as $theme) {
                if (empty($theme['name'])) continue;
                $strategyService->createTheme($clientId, [
                    'name' => $theme['name'],
                    'description' => $theme['description'] ?? '',
                    'copy_instructions' => $theme['copy_instructions'] ?? '',
                    'default_hashtags' => $theme['suggested_hashtags'] ?? $theme['default_hashtags'] ?? '',
                    'required_elements' => ['website' => true, 'cta' => true, 'hashtags' => true],
                    'sort_order' => $order++,
                    'samples' => [],
                ]);
            }
        }

        // 3. Set default art direction
        $artService = new ArtDirectionService();
        $existing = $artService->get($clientId);
        if (empty($existing['id'])) {
            $artService->save($clientId, [
                'image_style' => 'photorealistic',
                'realism_level' => 8,
                'color_temperature' => 'cold',
                'contrast_level' => 'punchy',
                'mood' => 'professional',
                'brand_color_bleed' => 25,
                'illustration_limit' => 'max_1_per_week',
            ]);
        }
    }
}
