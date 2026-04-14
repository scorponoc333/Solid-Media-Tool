<?php

class ThemeSample extends Model
{
    protected string $table = 'theme_samples';

    public function getByTheme(int $themeId): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE theme_id = :tid ORDER BY sort_order ASC",
            ['tid' => $themeId]
        );
    }

    public function deleteByTheme(int $themeId): void
    {
        Database::query(
            "DELETE FROM {$this->table} WHERE theme_id = :tid",
            ['tid' => $themeId]
        );
    }
}
