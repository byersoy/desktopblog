<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Kategori/etiket adlarini tutarli bir renkli simgeye esler.
 * Ayni ad her zaman ayni rozeti verir (crc32 tabanli).
 */
final class Emblem
{
    private const BADGES = [
        ['icon' => 'folder', 'color' => 'c-blue'],
        ['icon' => 'tag', 'color' => 'c-orange'],
        ['icon' => 'archive', 'color' => 'c-purple'],
        ['icon' => 'mail', 'color' => 'c-green'],
        ['icon' => 'person', 'color' => 'c-pink'],
        ['icon' => 'search', 'color' => 'c-teal'],
    ];

    public static function badgeFor(string $key): array
    {
        return self::BADGES[crc32($key) % count(self::BADGES)];
    }
}
