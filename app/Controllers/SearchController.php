<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Seo;
use App\Core\View;
use App\Models\Post;

final class SearchController
{
    public function page(Request $request): void
    {
        $q = trim((string) ($request->query['q'] ?? ''));
        $results = $q !== '' ? Post::search($q, 20) : [];

        View::render('pages/search', [
            'q' => $q,
            'results' => $results,
        ], 'layouts/desktop', [
            'id' => 'arama',
            'title' => $q !== '' ? '“' . $q . '” için arama' : 'Ara',
            'kind' => 'finder',
        ], [
            'seo' => [
                'title' => Seo::title($q !== '' ? '“' . $q . '” için arama sonuçları' : 'Ara'),
                'description' => 'Blog içinde arama yapın.',
                'canonical' => Seo::siteUrl('/arama') . ($q !== '' ? '?q=' . urlencode($q) : ''),
                'noindex' => true,
            ],
        ]);
    }

    public function json(Request $request): void
    {
        $q = trim((string) ($request->query['q'] ?? ''));
        if (mb_strlen($q) < 2) {
            Response::json(['results' => []]);
        }

        $posts = Post::search($q, 8);
        $results = array_map(static function (array $p): array {
            return ['title' => $p['title'], 'url' => '/yazi/' . $p['slug']];
        }, $posts);

        Response::json(['results' => $results]);
    }
}
