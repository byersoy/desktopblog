<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class LoginAttempt
{
    private const WINDOW_MINUTES = 15;
    private const MAX_ATTEMPTS = 5;

    /**
     * `login_attempts` tablosu henüz oluşturulmamışsa (sql/004 çalıştırılmadıysa)
     * oran sınırlama sessizce devre dışı kalır — giriş akışını bozmaz.
     */
    public static function isBlocked(string $ip): bool
    {
        try {
            return self::recentCount($ip) >= self::MAX_ATTEMPTS;
        } catch (\PDOException) {
            return false;
        }
    }

    public static function recentCount(string $ip): int
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempted_at >= (NOW() - INTERVAL ' . self::WINDOW_MINUTES . ' MINUTE)'
        );
        $stmt->execute([$ip]);
        return (int) $stmt->fetchColumn();
    }

    public static function record(string $ip): void
    {
        try {
            Database::connection()
                ->prepare('INSERT INTO login_attempts (ip_address, attempted_at) VALUES (?, NOW())')
                ->execute([$ip]);
        } catch (\PDOException) {
            // login_attempts tablosu yoksa sessizce yut
        }
    }

    public static function clear(string $ip): void
    {
        try {
            Database::connection()
                ->prepare('DELETE FROM login_attempts WHERE ip_address = ?')
                ->execute([$ip]);
        } catch (\PDOException) {
            // login_attempts tablosu yoksa sessizce yut
        }
    }
}
