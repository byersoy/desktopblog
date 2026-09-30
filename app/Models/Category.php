<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Category
{
    public static function all(): array
    {
        return Database::connection()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
    }

    public static function allWithCounts(): array
    {
        return Database::connection()->query(
            'SELECT c.*, COUNT(p.id) AS post_count
             FROM categories c
             LEFT JOIN posts p ON p.category_id = c.id AND p.status = "published" AND p.published_at <= NOW()
             GROUP BY c.id ORDER BY c.name'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM categories WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $name, string $slug, string $description): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO categories (name, slug, description, created_at) VALUES (?, ?, ?, NOW())'
        );
        $stmt->execute([$name, $slug, $description]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, string $name, string $slug, string $description): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?'
        );
        $stmt->execute([$name, $slug, $description, $id]);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('UPDATE posts SET category_id = NULL WHERE category_id = ?')->execute([$id]);
        Database::connection()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categories WHERE slug = ?';
        $params = [$slug];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return ((int) $stmt->fetchColumn()) > 0;
    }
}
