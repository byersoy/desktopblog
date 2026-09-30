<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Admin
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM admins WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updatePassword(int $id, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = Database::connection()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
    }

    public static function updateProfile(int $id, string $name, string $email): void
    {
        $stmt = Database::connection()->prepare('UPDATE admins SET name = ?, email = ? WHERE id = ?');
        $stmt->execute([$name, $email, $id]);
    }
}
