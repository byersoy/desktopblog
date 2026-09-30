<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\LoginAttempt;

final class AdminAuthController
{
    public function loginForm(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect('/yonetim');
        }

        $error = Session::flash('admin_login_error');
        echo View::capture('admin/login', ['error' => $error]);
    }

    public function login(Request $request): void
    {
        if (!Csrf::verify($request->input('_csrf'))) {
            Session::flash('admin_login_error', 'Oturum süresi doldu, tekrar deneyin.');
            Response::redirect('/yonetim/giris');
        }

        $ip = $request->ip();
        if (LoginAttempt::isBlocked($ip)) {
            Session::flash('admin_login_error', 'Çok fazla başarısız deneme. Lütfen 15 dakika sonra tekrar deneyin.');
            Response::redirect('/yonetim/giris');
        }

        $email = (string) $request->input('email', '');
        $password = (string) $request->input('password', '');

        if (!Auth::attempt($email, $password)) {
            LoginAttempt::record($ip);
            Session::flash('admin_login_error', 'E-posta veya şifre hatalı.');
            Response::redirect('/yonetim/giris');
        }

        LoginAttempt::clear($ip);
        Response::redirect('/yonetim');
    }

    public function logout(Request $request): void
    {
        if (Csrf::verify($request->input('_csrf'))) {
            Auth::logout();
        }
        Response::redirect('/yonetim/giris');
    }
}
