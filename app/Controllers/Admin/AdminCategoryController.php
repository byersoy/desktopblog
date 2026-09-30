<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Str;
use App\Core\View;
use App\Models\Category;

final class AdminCategoryController
{
    public function index(Request $request): void
    {
        Auth::requireLogin();
        View::admin('admin/categories/index', [
            'categories' => Category::allWithCounts(),
        ], 'categories', 'Kategoriler');
    }

    public function store(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::redirect('/yonetim/kategoriler');
        }

        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            Session::flash('admin_error', 'Kategori adı zorunludur.');
            Response::redirect('/yonetim/kategoriler');
        }

        $slug = Str::slug($name);
        if (Category::slugExists($slug)) {
            $slug .= '-' . Str::random(4);
        }

        Category::create($name, $slug, trim((string) $request->input('description', '')));
        Session::flash('admin_success', 'Kategori eklendi.');
        Response::redirect('/yonetim/kategoriler');
    }

    public function update(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::redirect('/yonetim/kategoriler');
        }

        $id = (int) $request->param('id');
        $name = trim((string) $request->input('name', ''));
        $slug = Str::slug($name);
        if (Category::slugExists($slug, $id)) {
            $slug .= '-' . Str::random(4);
        }

        Category::update($id, $name, $slug, trim((string) $request->input('description', '')));
        Session::flash('admin_success', 'Kategori güncellendi.');
        Response::redirect('/yonetim/kategoriler');
    }

    public function destroy(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::redirect('/yonetim/kategoriler');
        }
        Category::delete((int) $request->param('id'));
        Session::flash('admin_success', 'Kategori silindi.');
        Response::redirect('/yonetim/kategoriler');
    }
}
