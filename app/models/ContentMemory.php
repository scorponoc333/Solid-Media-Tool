<?php

class ContentMemory extends Model
{
    protected string $table = 'content_memory';

    public function getByClient(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT cm.*, p.title as post_title FROM {$this->table} cm LEFT JOIN posts p ON cm.post_id = p.id WHERE cm.client_id = :cid ORDER BY cm.created_at DESC",
            ['cid' => $clientId]
        );
    }

    public function hashExists(string $hash, int $clientId): bool
    {
        $result = Database::fetch(
            "SELECT id FROM {$this->table} WHERE content_hash = :hash AND client_id = :cid",
            ['hash' => $hash, 'cid' => $clientId]
        );
        return $result !== null;
    }

    public function getTopics(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT topic, COUNT(*) as count FROM {$this->table} WHERE client_id = :cid GROUP BY topic ORDER BY count DESC",
            ['cid' => $clientId]
        );
    }

    public function getRecentAngles(int $clientId, int $limit = 20): array
    {
        return Database::fetchAll(
            "SELECT angle FROM {$this->table} WHERE client_id = :cid ORDER BY created_at DESC LIMIT {$limit}",
            ['cid' => $clientId]
        );
    }
}
