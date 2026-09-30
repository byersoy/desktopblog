<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class RedirectRule
{
    public static function findByFrom(string $path): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM redirects WHERE from_path = ? LIMIT 1');
        $stmt->execute([$path]);
        return $stmt->fetch() ?: null;
    }

    public static function all(): array
    {
        return Database::connection()->query('SELECT * FROM redirects ORDER BY created_at DESC')->fetchAll();
    }

    public static function create(string $from, string $to, int $status = 301): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO redirects (from_path, to_path, status_code, created_at) VALUES (?, ?, ?, NOW())'
        );
        $stmt->execute([$from, $to, $status]);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM redirects WHERE id = ?')->execute([$id]);
    }
}
