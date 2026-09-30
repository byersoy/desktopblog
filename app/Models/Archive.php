<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Archive
{
    /**
     * Yayınlanan yazıları yıl > ay olarak gruplar.
     * @return array<string, array<string, array>>
     */
    public static function grouped(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, title, slug, excerpt, published_at FROM posts
             WHERE status = "published" AND published_at <= NOW()
             ORDER BY published_at DESC'
        );

        $groups = [];
        foreach ($stmt->fetchAll() as $post) {
            $year = date('Y', strtotime((string) $post['published_at']));
            $month = date('n', strtotime((string) $post['published_at']));
            $groups[$year][$month][] = $post;
        }
        return $groups;
    }
}
