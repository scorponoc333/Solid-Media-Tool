<?php

class BrandingSetting extends Model
{
    protected string $table = 'branding_settings';

    public function getByClient(int $clientId): ?array
    {
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE client_id = :cid",
            ['cid' => $clientId]
        );
    }

    public function updateByClient(int $clientId, array $data): void
    {
        $existing = $this->getByClient($clientId);
        if ($existing) {
            $this->update($existing['id'], $data);
        } else {
            $data['client_id'] = $clientId;
            $this->create($data);
        }
    }
}
