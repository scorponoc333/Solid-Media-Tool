<?php

class ThemeSchedule extends Model
{
    protected string $table = 'theme_schedule';

    public function getByClient(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT ts.*, ct.name AS theme_name
             FROM {$this->table} ts
             LEFT JOIN content_themes ct ON ct.id = ts.theme_id
             WHERE ts.client_id = :cid
             ORDER BY ts.day_of_week ASC",
            ['cid' => $clientId]
        );
    }

    public function setDay(int $clientId, int $dayOfWeek, ?int $themeId): void
    {
        if ($themeId === null) {
            Database::query(
                "DELETE FROM {$this->table} WHERE client_id = :cid AND day_of_week = :dow",
                ['cid' => $clientId, 'dow' => $dayOfWeek]
            );
        } else {
            Database::query(
                "INSERT INTO {$this->table} (client_id, day_of_week, theme_id)
                 VALUES (:cid, :dow, :tid)
                 ON DUPLICATE KEY UPDATE theme_id = :tid2",
                ['cid' => $clientId, 'dow' => $dayOfWeek, 'tid' => $themeId, 'tid2' => $themeId]
            );
        }
    }
}
