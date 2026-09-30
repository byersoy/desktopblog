<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\View;
use App\Models\Post;

final class AdminDashboardController
{
    public function index(Request $request): void
    {
        Auth::requireLogin();

        $stats = [
            'total' => Post::countAll(),
            'published' => Post::countPublished(),
            'messages' => (int) Database::connection()->query('SELECT COUNT(*) FROM messages')->fetchColumn(),
            'categories' => (int) Database::connection()->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
        ];

        View::admin('admin/dashboard', [
            'stats' => $stats,
            'recentPosts' => array_slice(Post::allForAdmin(), 0, 6),
        ], 'dashboard', 'Panel');
    }
}
