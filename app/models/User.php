<?php

class User extends Model
{
    protected string $table = 'users';

    public function findByUsername(string $username): ?array
    {
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE username = :username",
            ['username' => $username]
        );
    }

    public function findByEmail(string $email): ?array
    {
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE email = :email",
            ['email' => $email]
        );
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
