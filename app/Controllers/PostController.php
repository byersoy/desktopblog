<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Markdown;
use App\Core\Request;
use App\Core\Response;
use App\Core\Seo;
use App\Core\Str;
use App\Core\View;
use App\Models\Post;

final class PostController
{
    public function random(Request $request): void
    {
        $slug = Post::randomPublishedSlug();
        if ($slug === null) {
            Response::redirect('/');
        }
        Response::redirect('/yazi/' . $slug);
    }

    public function index(Request $request): void
    {
        $page = max(1, (int) ($request->query['sayfa'] ?? 1));
        $result = Post::paginatePublished($page);

        if ($page > 1 && $result['items'] === []) {
            Response::notFound();
        }

        View::render('pages/post-list', [
            'posts' => $result['items'],
            'page' => $result['page'],
            'lastPage' => $result['lastPage'],
            'baseUrl' => '/yazilar',
            'heading' => 'Tüm Yazılar',
        ], 'layouts/desktop', [
            'id' => 'yazilar',
            'title' => 'Yazılar',
            'kind' => 'finder',
        ], [
            'seo' => [
                'title' => Seo::title('Tüm Yazılar'),
                'description' => 'Bütün blog yazılarının listesi.',
                'canonical' => Seo::siteUrl('/yazilar') . ($page > 1 ? '?sayfa=' . $page : ''),
                'noindex' => $page > 1,
            ],
        ]);
    }

    public function show(Request $request): void
    {
        $slug = (string) $request->param('slug');
        $post = Post::findBySlug($slug);

        if ($post === null || ($post['status'] !== 'published')) {
            Response::notFound();
        }

        Post::incrementViews((int) $post['id']);
        $tags = Post::tagsFor((int) $post['id']);
        $related = Post::related((int) $post['id'], $post['category_id'] ? (int) $post['category_id'] : null);

        $description = $post['meta_description'] ?: $post['excerpt'] ?: Str::excerpt(Markdown::toPlainText($post['content_md']));

        View::render('pages/post-show', [
            'post' => $post,
            'tags' => $tags,
            'related' => $related,
        ], 'layouts/desktop', [
            'id' => 'yazi-' . $post['slug'],
            'title' => $post['title'],
            'kind' => 'textedit',
        ], [
            'seo' => [
                'title' => Seo::title($post['meta_title'] ?: $post['title']),
                'description' => $description,
                'canonical' => Seo::siteUrl('/yazi/' . $post['slug']),
                'type' => 'article',
                'image' => $post['cover_image'] ?: null,
                'publishedTime' => date('c', strtotime((string) $post['published_at'])),
                'modifiedTime' => date('c', strtotime((string) $post['updated_at'])),
            ],
            'jsonLd' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'BlogPosting',
                    'headline' => $post['title'],
                    'description' => $description,
                    'datePublished' => date('c', strtotime((string) $post['published_at'])),
                    'dateModified' => date('c', strtotime((string) $post['updated_at'])),
                    'mainEntityOfPage' => Seo::siteUrl('/yazi/' . $post['slug']),
                    'author' => ['@type' => 'Person', 'name' => \App\Core\App::setting('site_author', \App\Core\App::config('app')['name'])],
                    'publisher' => ['@type' => 'Organization', 'name' => \App\Core\App::setting('site_name', \App\Core\App::config('app')['name'])],
                ],
                Seo::breadcrumbJsonLd([
                    ['name' => 'Ana Sayfa', 'url' => Seo::siteUrl('/')],
                    ['name' => 'Yazılar', 'url' => Seo::siteUrl('/yazilar')],
                    ['name' => $post['title'], 'url' => Seo::siteUrl('/yazi/' . $post['slug'])],
                ]),
            ],
        ]);
    }
}
