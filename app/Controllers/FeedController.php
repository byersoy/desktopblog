<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Request;
use App\Core\Response;
use App\Core\Seo;
use App\Models\Post;

final class FeedController
{
    public function rss(Request $request): void
    {
        $siteName = htmlspecialchars(App::setting('site_name', App::config('app')['name']), ENT_QUOTES | ENT_XML1);
        $siteDescription = htmlspecialchars(App::setting('site_description', ''), ENT_QUOTES | ENT_XML1);
        $siteUrl = Seo::siteUrl('/');

        $items = '';
        foreach (Post::latestForFeed(30) as $post) {
            $link = Seo::siteUrl('/yazi/' . $post['slug']);
            $title = htmlspecialchars($post['title'], ENT_QUOTES | ENT_XML1);
            $desc = htmlspecialchars($post['excerpt'] ?: '', ENT_QUOTES | ENT_XML1);
            $pubDate = date(DATE_RSS, strtotime((string) $post['published_at']));
            $items .= <<<ITEM
    <item>
      <title>{$title}</title>
      <link>{$link}</link>
      <guid>{$link}</guid>
      <pubDate>{$pubDate}</pubDate>
      <description>{$desc}</description>
    </item>

ITEM;
        }

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>{$siteName}</title>
    <link>{$siteUrl}</link>
    <description>{$siteDescription}</description>
    <language>tr-TR</language>
{$items}  </channel>
</rss>
XML;

        Response::xml($xml);
    }
}
