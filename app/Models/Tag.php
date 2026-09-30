<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Tag
{
    public static function all(): array
    {
        return Database::connection()->query('SELECT * FROM tags ORDER BY name')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM tags WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM tags WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public static function findOrCreateByNames(array $names): array
    {
        $db = Database::connection();
        $ids = [];
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $slug = \App\Core\Str::slug($name);
            $stmt = $db->prepare('SELECT id FROM tags WHERE slug = ? LIMIT 1');
            $stmt->execute([$slug]);
            $id = $stmt->fetchColumn();
            if ($id === false) {
                $insert = $db->prepare('INSERT INTO tags (name, slug, created_at) VALUES (?, ?, NOW())');
                $insert->execute([$name, $slug]);
                $id = $db->lastInsertId();
            }
            $ids[] = (int) $id;
        }
        return $ids;
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM tags WHERE id = ?')->execute([$id]);
    }
}
