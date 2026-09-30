<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Tag;

final class AdminTagController
{
    public function index(Request $request): void
    {
        Auth::requireLogin();
        View::admin('admin/tags/index', [
            'tags' => Tag::all(),
        ], 'tags', 'Etiketler');
    }

    public function store(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::redirect('/yonetim/etiketler');
        }
        $name = trim((string) $request->input('name', ''));
        if ($name !== '') {
            Tag::findOrCreateByNames([$name]);
            Session::flash('admin_success', 'Etiket eklendi.');
        }
        Response::redirect('/yonetim/etiketler');
    }

    public function destroy(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::redirect('/yonetim/etiketler');
        }
        Tag::delete((int) $request->param('id'));
        Session::flash('admin_success', 'Etiket silindi.');
        Response::redirect('/yonetim/etiketler');
    }
}
