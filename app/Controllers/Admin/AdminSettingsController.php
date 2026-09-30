<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Uploader;
use App\Core\View;
use App\Models\Admin;
use App\Models\Setting;

final class AdminSettingsController
{
    private const KEYS = [
        'site_name', 'site_description', 'site_author', 'favicon', 'og_image',
        'social_github', 'social_twitter', 'social_linkedin', 'contact_email',
        'about_summary', 'about_content',
    ];

    public function index(Request $request): void
    {
        Auth::requireLogin();
        View::admin('admin/settings/index', [
            'settings' => Setting::all(),
            'admin' => Auth::user(),
        ], 'settings', 'Ayarlar');
    }

    public function update(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::redirect('/yonetim/ayarlar');
        }

        foreach (self::KEYS as $key) {
            Setting::set($key, (string) $request->input($key, ''));
        }

        Admin::updateProfile((int) Auth::id(), (string) $request->input('admin_name', ''), (string) $request->input('admin_email', ''));

        Session::flash('admin_success', 'Ayarlar güncellendi.');
        Response::redirect('/yonetim/ayarlar');
    }

    public function uploadFavicon(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::json(['error' => 'Geçersiz istek.'], 419);
        }

        $file = $request->file('favicon');
        if ($file === null) {
            Response::json(['error' => 'Dosya bulunamadı.'], 422);
        }

        try {
            $url = Uploader::storeFavicon($file);
            Response::json(['url' => $url]);
        } catch (\RuntimeException $e) {
            Response::json(['error' => $e->getMessage()], 422);
        }
    }

    public function updatePassword(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::redirect('/yonetim/ayarlar');
        }

        $current = (string) $request->input('current_password', '');
        $new = (string) $request->input('new_password', '');
        $confirm = (string) $request->input('new_password_confirm', '');

        $admin = Auth::user();
        if ($admin === null || !password_verify($current, $admin['password_hash'])) {
            Session::flash('admin_error', 'Mevcut şifre hatalı.');
            Response::redirect('/yonetim/ayarlar');
        }

        if (mb_strlen($new) < 8 || $new !== $confirm) {
            Session::flash('admin_error', 'Yeni şifre en az 8 karakter olmalı ve tekrar ile eşleşmeli.');
            Response::redirect('/yonetim/ayarlar');
        }

        Admin::updatePassword((int) Auth::id(), $new);
        Session::flash('admin_success', 'Şifre güncellendi.');
        Response::redirect('/yonetim/ayarlar');
    }
}
