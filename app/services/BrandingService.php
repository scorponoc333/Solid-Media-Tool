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
        ];
    }
}
