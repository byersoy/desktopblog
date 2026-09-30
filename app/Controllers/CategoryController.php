<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Seo;
use App\Core\View;
use App\Models\Category;
use App\Models\Post;

final class CategoryController
{
    public function index(Request $request): void
    {
        $categories = Category::allWithCounts();

        View::render('pages/category-list', [
            'categories' => $categories,
        ], 'layouts/desktop', [
            'id' => 'kategoriler',
            'title' => 'Kategoriler',
            'kind' => 'finder',
        ], [
            'seo' => [
                'title' => Seo::title('Kategoriler'),
                'description' => 'Blog yazılarının kategorilere göre listesi.',
                'canonical' => Seo::siteUrl('/kategoriler'),
            ],
        ]);
    }

    public function show(Request $request): void
    {
        $slug = (string) $request->param('slug');
        $category = Category::findBySlug($slug);
        if ($category === null) {
            Response::notFound();
        }

        $page = max(1, (int) ($request->query['sayfa'] ?? 1));
        $result = Post::paginatePublished($page, (int) $category['id']);

        if ($page > 1 && $result['items'] === []) {
            Response::notFound();
        }

        View::render('pages/post-list', [
            'posts' => $result['items'],
            'page' => $result['page'],
            'lastPage' => $result['lastPage'],
            'baseUrl' => '/kategori/' . $category['slug'],
            'heading' => $category['name'],
        ], 'layouts/desktop', [
            'id' => 'kategori-' . $category['slug'],
            'title' => $category['name'],
            'kind' => 'finder',
        ], [
            'seo' => [
                'title' => Seo::title($category['name']),
                'description' => $category['description'] ?: ('“' . $category['name'] . '” kategorisindeki yazılar.'),
                'canonical' => Seo::siteUrl('/kategori/' . $category['slug']) . ($page > 1 ? '?sayfa=' . $page : ''),
                'noindex' => $page > 1,
            ],
            'jsonLd' => [
                Seo::breadcrumbJsonLd([
                    ['name' => 'Ana Sayfa', 'url' => Seo::siteUrl('/')],
                    ['name' => 'Kategoriler', 'url' => Seo::siteUrl('/kategoriler')],
                    ['name' => $category['name'], 'url' => Seo::siteUrl('/kategori/' . $category['slug'])],
                ]),
            ],
        ]);
    }
}
