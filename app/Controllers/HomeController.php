<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Request;
use App\Core\Seo;
use App\Core\View;
use App\Models\Post;

final class HomeController
{
    public function index(Request $request): void
    {
        $result = Post::paginatePublished(1);
        $siteName = App::setting('site_name', App::config('app')['name']);
        $siteDescription = App::setting('site_description', App::config('seo')['default_description']);

        View::render('pages/home', [
            'posts' => $result['items'],
        ], 'layouts/desktop', [
            'id' => 'home',
            'title' => $siteName,
            'kind' => 'finder',
        ], [
            'seo' => [
                'title' => Seo::title($siteName, true),
                'description' => $siteDescription,
                'canonical' => Seo::siteUrl('/'),
            ],
            'jsonLd' => [[
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => Seo::siteUrl('/'),
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => Seo::siteUrl('/arama') . '?q={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ]],
        ]);
    }
}
