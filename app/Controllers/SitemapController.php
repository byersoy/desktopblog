<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Seo;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;

final class SitemapController
{
    public function index(Request $request): void
    {
        $urls = [
            ['loc' => Seo::siteUrl('/'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => Seo::siteUrl('/yazilar'), 'changefreq' => 'daily', 'priority' => '0.8'],
            ['loc' => Seo::siteUrl('/kategoriler'), 'changefreq' => 'weekly', 'priority' => '0.6'],
            ['loc' => Seo::siteUrl('/arsiv'), 'changefreq' => 'weekly', 'priority' => '0.5'],
            ['loc' => Seo::siteUrl('/hakkimda'), 'changefreq' => 'monthly', 'priority' => '0.4'],
            ['loc' => Seo::siteUrl('/iletisim'), 'changefreq' => 'monthly', 'priority' => '0.4'],
        ];

        foreach (Post::allPublishedForSitemap() as $post) {
            $urls[] = [
                'loc' => Seo::siteUrl('/yazi/' . $post['slug']),
                'lastmod' => date('c', strtotime((string) $post['updated_at'])),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }
        foreach (Category::all() as $category) {
            $urls[] = ['loc' => Seo::siteUrl('/kategori/' . $category['slug']), 'changefreq' => 'weekly', 'priority' => '0.5'];
        }
        foreach (Tag::all() as $tag) {
            $urls[] = ['loc' => Seo::siteUrl('/etiket/' . $tag['slug']), 'changefreq' => 'weekly', 'priority' => '0.3'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_QUOTES | ENT_XML1) . '</loc>' . "\n";
            if (!empty($u['lastmod'])) {
                $xml .= '    <lastmod>' . $u['lastmod'] . '</lastmod>' . "\n";
            }
            $xml .= '    <changefreq>' . $u['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $u['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        $xml .= '</urlset>';

        Response::xml($xml);
    }
}
