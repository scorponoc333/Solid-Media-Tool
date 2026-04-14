<?php

class ContentTheme extends Model
{
    protected string $table = 'content_themes';

    public function getByClient(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE client_id = :cid ORDER BY sort_order ASC, id ASC",
            ['cid' => $clientId]
        );
    }

    public function getActiveByClient(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE client_id = :cid AND is_active = 1 ORDER BY sort_order ASC, id ASC",
            ['cid' => $clientId]
        );
    }

    public function getByIdAndClient(int $id, int $clientId): ?array
    {
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE id = :id AND client_id = :cid",
            ['id' => $id, 'cid' => $clientId]
        );
    }

    public function deleteByClient(int $id, int $clientId): bool
    {
        $theme = $this->getByIdAndClient($id, $clientId);
        if (!$theme) return false;
        $this->delete($id);
        return true;
    }
}
