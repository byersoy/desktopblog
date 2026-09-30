<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Post
{
    private const PER_PAGE = 8;

    public static function paginatePublished(int $page = 1, ?int $categoryId = null, ?int $tagId = null): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * self::PER_PAGE;
        $db = Database::connection();

        $where = ['p.status = "published"', 'p.published_at <= NOW()'];
        $params = [];

        if ($categoryId !== null) {
            $where[] = 'p.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        $join = '';
        if ($tagId !== null) {
            $join = 'INNER JOIN post_tag pt ON pt.post_id = p.id';
            $where[] = 'pt.tag_id = :tag_id';
            $params['tag_id'] = $tagId;
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $db->prepare("SELECT COUNT(DISTINCT p.id) FROM posts p {$join} WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT DISTINCT p.*, c.name AS category_name, c.slug AS category_slug
                FROM posts p
                {$join}
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE {$whereSql}
                ORDER BY p.published_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', self::PER_PAGE, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'perPage' => self::PER_PAGE,
            'lastPage' => max(1, (int) ceil($total / self::PER_PAGE)),
        ];
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM posts p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.slug = ? LIMIT 1'
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM posts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function tagsFor(int $postId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT t.* FROM tags t
             INNER JOIN post_tag pt ON pt.tag_id = t.id
             WHERE pt.post_id = ? ORDER BY t.name'
        );
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    public static function related(int $postId, ?int $categoryId, int $limit = 3): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, title, slug, excerpt, cover_image, published_at
             FROM posts
             WHERE status = "published" AND published_at <= NOW() AND id != ? AND (category_id = ? OR ? IS NULL)
             ORDER BY published_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $postId, PDO::PARAM_INT);
        $stmt->bindValue(2, $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(3, $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(4, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function search(string $query, int $limit = 10): array
    {
        $like = '%' . $query . '%';
        $stmt = Database::connection()->prepare(
            'SELECT id, title, slug, excerpt, published_at FROM posts
             WHERE status = "published" AND published_at <= NOW()
             AND (title LIKE ? OR excerpt LIKE ? OR content_md LIKE ?)
             ORDER BY published_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $like);
        $stmt->bindValue(2, $like);
        $stmt->bindValue(3, $like);
        $stmt->bindValue(4, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function incrementViews(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE posts SET views = views + 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM posts WHERE slug = ?';
        $params = [$slug];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    public static function allForAdmin(): array
    {
        $stmt = Database::connection()->query(
            'SELECT p.*, c.name AS category_name FROM posts p
             LEFT JOIN categories c ON c.id = p.category_id
             ORDER BY p.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO posts
             (title, slug, excerpt, content_md, content_html, cover_image, status, category_id,
              meta_title, meta_description, published_at, reading_time, author_id, created_at, updated_at)
             VALUES
             (:title, :slug, :excerpt, :content_md, :content_html, :cover_image, :status, :category_id,
              :meta_title, :meta_description, :published_at, :reading_time, :author_id, NOW(), NOW())'
        );
        $stmt->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE posts SET
                title = :title, slug = :slug, excerpt = :excerpt, content_md = :content_md,
                content_html = :content_html, cover_image = :cover_image, status = :status,
                category_id = :category_id, meta_title = :meta_title, meta_description = :meta_description,
                published_at = :published_at, reading_time = :reading_time, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute($data + ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function syncTags(int $postId, array $tagIds): void
    {
        $db = Database::connection();
        $db->prepare('DELETE FROM post_tag WHERE post_id = ?')->execute([$postId]);
        if ($tagIds === []) {
            return;
        }
        $stmt = $db->prepare('INSERT INTO post_tag (post_id, tag_id) VALUES (?, ?)');
        foreach ($tagIds as $tagId) {
            $stmt->execute([$postId, (int) $tagId]);
        }
    }

    public static function countAll(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM posts')->fetchColumn();
    }

    public static function countPublished(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM posts WHERE status = "published"')->fetchColumn();
    }

    public static function randomPublishedSlug(): ?string
    {
        $stmt = Database::connection()->query(
            'SELECT slug FROM posts WHERE status = "published" AND published_at <= NOW() ORDER BY RAND() LIMIT 1'
        );
        $slug = $stmt->fetchColumn();
        return $slug === false ? null : $slug;
    }

    public static function allPublishedForSitemap(): array
    {
        $stmt = Database::connection()->query(
            'SELECT slug, updated_at FROM posts WHERE status = "published" AND published_at <= NOW() ORDER BY published_at DESC'
        );
        return $stmt->fetchAll();
    }

    public static function latestForFeed(int $limit = 20): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT title, slug, excerpt, content_html, published_at FROM posts
             WHERE status = "published" AND published_at <= NOW()
             ORDER BY published_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
