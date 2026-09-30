<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Seo;
use App\Core\View;
use App\Models\Archive;

final class ArchiveController
{
    public function index(Request $request): void
    {
        View::render('pages/archive', [
            'groups' => Archive::grouped(),
        ], 'layouts/desktop', [
            'id' => 'arsiv',
            'title' => 'Arşiv',
            'kind' => 'finder',
        ], [
            'seo' => [
                'title' => Seo::title('Arşiv'),
                'description' => 'Tüm yazıların tarihe göre arşivi.',
                'canonical' => Seo::siteUrl('/arsiv'),
            ],
        ]);
    }
}
