<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Seo;
use App\Core\View;
use App\Models\Post;
use App\Models\Tag;

final class TagController
{
    public function show(Request $request): void
    {
        $slug = (string) $request->param('slug');
        $tag = Tag::findBySlug($slug);
        if ($tag === null) {
            Response::notFound();
        }

        $page = max(1, (int) ($request->query['sayfa'] ?? 1));
        $result = Post::paginatePublished($page, null, (int) $tag['id']);

        if ($page > 1 && $result['items'] === []) {
            Response::notFound();
        }

        View::render('pages/post-list', [
            'posts' => $result['items'],
            'page' => $result['page'],
            'lastPage' => $result['lastPage'],
            'baseUrl' => '/etiket/' . $tag['slug'],
            'heading' => '#' . $tag['name'],
        ], 'layouts/desktop', [
            'id' => 'etiket-' . $tag['slug'],
            'title' => '#' . $tag['name'],
            'kind' => 'finder',
        ], [
            'seo' => [
                'title' => Seo::title('#' . $tag['name']),
                'description' => '“' . $tag['name'] . '” etiketli yazılar.',
                'canonical' => Seo::siteUrl('/etiket/' . $tag['slug']) . ($page > 1 ? '?sayfa=' . $page : ''),
                'noindex' => $page > 1,
            ],
        ]);
    }
}
