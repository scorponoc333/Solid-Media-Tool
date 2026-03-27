<?php

class Model
{
    protected string $table;

    public function find(int $id): ?array
    {
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        );
    }

    public function all(string $orderBy = 'id DESC'): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy}"
        );
    }

    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        Database::query(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})",
            $data
        );

        return (int) Database::lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sets = [];
        foreach (array_keys($data) as $key) {
            $sets[] = "{$key} = :{$key}";
        }
        $setStr = implode(', ', $sets);
        $data['id'] = $id;

        Database::query(
            "UPDATE {$this->table} SET {$setStr} WHERE id = :id",
            $data
        );
    }

    public function delete(int $id): void
    {
        Database::query(
            "DELETE FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        );
    }

    public function where(string $column, mixed $value): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE {$column} = :val",
            ['val' => $value]
        );
    }

    public function count(string $where = '1=1', array $params = []): int
    {
        $result = Database::fetch(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}",
            $params
        );
        return (int) ($result['total'] ?? 0);
    }
}
