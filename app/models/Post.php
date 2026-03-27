<?php

class Post extends Model
{
    protected string $table = 'posts';

    public function getByClient(int $clientId, string $orderBy = 'created_at DESC'): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE client_id = :cid ORDER BY {$orderBy}",
            ['cid' => $clientId]
        );
    }

    public function getScheduled(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE client_id = :cid AND status = 'scheduled' ORDER BY scheduled_at ASC",
            ['cid' => $clientId]
        );
    }

    public function getByDateRange(int $clientId, string $start, string $end): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE client_id = :cid AND scheduled_at BETWEEN :start AND :end ORDER BY scheduled_at ASC",
            ['cid' => $clientId, 'start' => $start, 'end' => $end]
        );
    }

    public function getByStatus(int $clientId, string $status): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE client_id = :cid AND status = :status ORDER BY created_at DESC",
            ['cid' => $clientId, 'status' => $status]
        );
    }

    public function getStats(int $clientId): array
    {
        $total = $this->count('client_id = :cid', ['cid' => $clientId]);
        $scheduled = $this->count("client_id = :cid AND status = 'scheduled'", ['cid' => $clientId]);
        $published = $this->count("client_id = :cid AND status = 'published'", ['cid' => $clientId]);
        $draft = $this->count("client_id = :cid AND status = 'draft'", ['cid' => $clientId]);

        return compact('total', 'scheduled', 'published', 'draft');
    }

    public function getRecent(int $clientId, int $limit = 5): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE client_id = :cid ORDER BY created_at DESC LIMIT {$limit}",
            ['cid' => $clientId]
        );
    }

    public function getTopicDistribution(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT topic, COUNT(*) as count FROM {$this->table} WHERE client_id = :cid AND topic IS NOT NULL GROUP BY topic ORDER BY count DESC",
            ['cid' => $clientId]
        );
    }

    public function getPlatformDistribution(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT platform, COUNT(*) as count FROM {$this->table} WHERE client_id = :cid GROUP BY platform ORDER BY count DESC",
            ['cid' => $clientId]
        );
    }
}
