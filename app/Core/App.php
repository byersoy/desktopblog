<?php

declare(strict_types=1);

namespace App\Core;

final class App
{
    private static array $config = [];
    private static ?array $settingsCache = null;

    public static function boot(array $config): void
    {
        self::$config = $config;
    }

    public static function config(?string $key = null): mixed
    {
        if ($key === null) {
            return self::$config;
        }
        return self::$config[$key] ?? null;
    }

    /**
     * Site ayarlarini `settings` tablosundan okur (key/value), cache'ler.
     */
    public static function setting(string $key, mixed $default = null): mixed
    {
        if (self::$settingsCache === null) {
            self::$settingsCache = [];
            try {
                $stmt = Database::connection()->query('SELECT `key`, `value` FROM settings');
                foreach ($stmt->fetchAll() as $row) {
                    self::$settingsCache[$row['key']] = $row['value'];
                }
            } catch (\Throwable) {
                // Tablo henüz oluşturulmamış olabilir (ilk kurulum).
            }
        }

        return self::$settingsCache[$key] ?? $default;
    }

    /**
     * Bir public/ dosyasi icin tarayici onbelleginin otomatik kirilmasini
     * saglayan surum sorgu dizesi ("?v=..."). Dosya bulunamazsa bos doner.
     */
    public static function assetVersion(string $publicRelativePath): string
    {
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $path = rtrim($docRoot, '/') . '/' . ltrim($publicRelativePath, '/');
        $mtime = is_file($path) ? filemtime($path) : false;
        return $mtime !== false ? '?v=' . $mtime : '';
    }

    /**
     * Ayarlardan favicon varsa onu, yoksa varsayılan SVG'yi kullanan
     * hazır `<link rel="icon">` etiketi.
     */
    public static function faviconTag(): string
    {
        $favicon = (string) self::setting('favicon', '');
        if ($favicon === '') {
            return '<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">';
        }

        $ext = strtolower(pathinfo(parse_url($favicon, PHP_URL_PATH) ?: $favicon, PATHINFO_EXTENSION));
        $type = $ext === 'svg' ? 'image/svg+xml' : 'image/png';

        return '<link rel="icon" href="' . htmlspecialchars($favicon, ENT_QUOTES) . '" type="' . $type . '">';
    }
}
