<?php

class BrandingService
{
    private BrandingSetting $model;

    public function __construct()
    {
        $this->model = new BrandingSetting();
    }

    public function get(int $clientId): array
    {
        $settings = $this->model->getByClient($clientId);
        if (!$settings) {
            return [
                'logo_url' => '',
                'primary_color' => '#6366f1',
                'secondary_color' => '#8b5cf6',
                'login_bg_url' => '',
                'particles_enabled' => 1,
                'company_name' => APP_NAME,
                'tagline' => 'AI-Powered Social Media Management',
                'first_comment' => '',
                'favicon_url' => '',
            ];
        }
        return $settings;
    }

    public function save(int $clientId, array $data): void
    {
        $this->model->updateByClient($clientId, $data);
    }

    public function getContext(int $clientId): array
    {
        $branding = $this->get($clientId);
        return [
            'company_name' => $branding['company_name'] ?? APP_NAME,
            'primary_color' => $branding['primary_color'] ?? '#6366f1',
            'tagline' => $branding['tagline'] ?? '',
            'phone' => $branding['phone'] ?? '',
            'website' => $branding['website'] ?? '',
        ];
    }

    public function isProfileComplete(int $clientId): array
    {
        $b = $this->get($clientId);
        $missing = [];
        if (empty(trim($b['company_name'] ?? '')) || ($b['company_name'] ?? '') === APP_NAME) $missing[] = 'Company Name';
        if (empty(trim($b['website'] ?? ''))) $missing[] = 'Website';
        if (empty(trim($b['phone'] ?? ''))) $missing[] = 'Phone Number';
        return $missing;
    }
}
