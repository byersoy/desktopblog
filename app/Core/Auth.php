<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Admin;

final class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $admin = Admin::findByEmail($email);
        if ($admin === null || !password_verify($password, $admin['password_hash'])) {
            return false;
        }

        Session::regenerate();
        Session::set('admin_id', (int) $admin['id']);
        return true;
    }

    public static function id(): ?int
    {
        $id = Session::get('admin_id');
        return $id === null ? null : (int) $id;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function user(): ?array
    {
        $id = self::id();
        if ($id === null) {
            return null;
        }
        return Admin::find($id);
    }

    public static function logout(): void
    {
        Session::remove('admin_id');
        Session::regenerate();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Response::redirect('/yonetim/giris');
        }
    }
}
