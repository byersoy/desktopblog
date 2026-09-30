<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Seo;

final class RobotsController
{
    public function index(Request $request): void
    {
        $lines = [
            'User-agent: *',
            'Disallow: /yonetim',
            'Disallow: /arama.json',
            'Allow: /',
            '',
            'Sitemap: ' . Seo::siteUrl('/sitemap.xml'),
        ];

        Response::text(implode("\n", $lines));
    }
}
