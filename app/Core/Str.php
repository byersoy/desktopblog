<?php

declare(strict_types=1);

namespace App\Core;

final class Str
{
    public static function slug(string $text): string
    {
        $map = [
            'ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'I' => 'i',
            'İ' => 'i', 'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u',
        ];
        $text = strtr($text, $map);
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^a-z0-9]+/u', '-', $text) ?? '';
        return trim($text, '-');
    }

    public static function excerpt(string $plainText, int $length = 160): string
    {
        $plainText = trim(preg_replace('/\s+/u', ' ', $plainText) ?? '');
        if (mb_strlen($plainText, 'UTF-8') <= $length) {
            return $plainText;
        }
        return rtrim(mb_substr($plainText, 0, $length, 'UTF-8')) . '…';
    }

    public static function readingTimeMinutes(string $plainText): int
    {
        $words = str_word_count(strip_tags($plainText));
        return max(1, (int) ceil($words / 200));
    }

    public static function random(int $length = 16): string
    {
        return bin2hex(random_bytes((int) ceil($length / 2)));
    }

    private const TR_MONTHS = [
        1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs', 6 => 'Haziran',
        7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
    ];

    public static function trDate(?string $datetime, bool $withTime = false): string
    {
        if (!$datetime) {
            return '';
        }
        $ts = strtotime($datetime);
        if ($ts === false) {
            return '';
        }
        $out = (int) date('j', $ts) . ' ' . self::TR_MONTHS[(int) date('n', $ts)] . ' ' . date('Y', $ts);
        if ($withTime) {
            $out .= ' · ' . date('H:i', $ts);
        }
        return $out;
    }
}
