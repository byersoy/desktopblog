<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Message
{
    public static function create(string $name, string $email, string $body, string $ip): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO messages (name, email, body, ip, created_at) VALUES (?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$name, $email, $body, $ip]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function all(): array
    {
        return Database::connection()->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
    }
}
