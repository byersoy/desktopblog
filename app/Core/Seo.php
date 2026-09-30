<?php

declare(strict_types=1);

namespace App\Core;

final class Seo
{
    public static function siteUrl(string $path = ''): string
    {
        $base = App::config('app')['url'];
        if ($path === '') {
            return $base;
        }
        return $base . '/' . ltrim($path, '/');
    }

    public static function title(string $pageTitle, bool $isHome = false): string
    {
        $siteName = App::setting('site_name', App::config('app')['name']);
        if ($isHome) {
            return $pageTitle;
        }
        return $pageTitle . ' · ' . $siteName;
    }

    /**
     * @param array{title:string,description:string,canonical:string,image?:string,type?:string,publishedTime?:string,modifiedTime?:string,noindex?:bool} $meta
     */
    public static function renderMeta(array $meta): string
    {
        $description = htmlspecialchars($meta['description'] ?? App::config('seo')['default_description'], ENT_QUOTES);
        $canonical = htmlspecialchars($meta['canonical'], ENT_QUOTES);
        $imageRaw = $meta['image'] ?? App::setting('og_image', App::config('seo')['og_image']);
        $image = $imageRaw ? htmlspecialchars(str_starts_with($imageRaw, 'http') ? $imageRaw : self::siteUrl($imageRaw), ENT_QUOTES) : '';
        $type = $meta['type'] ?? 'website';
        $title = htmlspecialchars($meta['title'], ENT_QUOTES);
        $robots = !empty($meta['noindex']) ? 'noindex, nofollow' : 'index, follow';

        $html = '';
        $html .= '<meta name="description" content="' . $description . '">' . "\n";
        $html .= '    <meta name="robots" content="' . $robots . '">' . "\n";
        $html .= '    <link rel="canonical" href="' . $canonical . '">' . "\n";
        $html .= '    <meta property="og:type" content="' . htmlspecialchars($type, ENT_QUOTES) . '">' . "\n";
        $html .= '    <meta property="og:title" content="' . $title . '">' . "\n";
        $html .= '    <meta property="og:description" content="' . $description . '">' . "\n";
        $html .= '    <meta property="og:url" content="' . $canonical . '">' . "\n";
        if ($image !== '') {
            $html .= '    <meta property="og:image" content="' . $image . '">' . "\n";
        }
        $html .= '    <meta property="og:site_name" content="' . htmlspecialchars(App::setting('site_name', App::config('app')['name']), ENT_QUOTES) . '">' . "\n";
        $html .= '    <meta property="og:locale" content="tr_TR">' . "\n";
        $html .= '    <meta name="twitter:card" content="' . ($image !== '' ? 'summary_large_image' : 'summary') . '">' . "\n";
        $html .= '    <meta name="twitter:title" content="' . $title . '">' . "\n";
        $html .= '    <meta name="twitter:description" content="' . $description . '">' . "\n";
        if ($image !== '') {
            $html .= '    <meta name="twitter:image" content="' . $image . '">' . "\n";
        }

        if (!empty($meta['publishedTime'])) {
            $html .= '    <meta property="article:published_time" content="' . htmlspecialchars($meta['publishedTime'], ENT_QUOTES) . '">' . "\n";
        }
        if (!empty($meta['modifiedTime'])) {
            $html .= '    <meta property="article:modified_time" content="' . htmlspecialchars($meta['modifiedTime'], ENT_QUOTES) . '">' . "\n";
        }

        return $html;
    }

    public static function jsonLd(array $graph): string
    {
        // JSON_HEX_TAG/JSON_HEX_AMP: veride "</script>" gibi bir dizi olsa bile
        // inline <script> etiketinden erken cikip HTML/JS enjekte edilemez.
        return '<script type="application/ld+json">' .
            json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) .
            '</script>';
    }

    public static function breadcrumbJsonLd(array $items): array
    {
        $list = [];
        foreach ($items as $i => $item) {
            $list[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }
}
