<?php

declare(strict_types=1);

namespace App\Core;

final class Uploader
{
    private const ALLOWED = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const FAVICON_MAX_BYTES = 1024 * 1024;

    /**
     * @param array $file $_FILES[...] girdisi
     * @return string Web'den erişilebilir yol (örn: /uploads/posts/2026/08/xxxx.jpg)
     * @throws \RuntimeException
     */
    public static function storePostImage(array $file): string
    {
        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Dosya yüklenemedi. (' . self::uploadErrorMessage((int) $file['error']) . ')');
        }
        if ((int) $file['size'] > self::MAX_BYTES) {
            throw new \RuntimeException('Dosya 5MB\'dan büyük olamaz.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset(self::ALLOWED[$ext])) {
            throw new \RuntimeException('Sadece jpg, png, webp, gif dosyalarına izin verilir.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if ($mime !== self::ALLOWED[$ext]) {
            throw new \RuntimeException('Dosya türü doğrulanamadı.');
        }

        $year = date('Y');
        $month = date('m');
        $dir = dirname(__DIR__, 2) . "/public/uploads/posts/{$year}/{$month}";
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = Str::random(20) . '.' . $ext;
        $destination = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Dosya kaydedilemedi.');
        }

        return "/uploads/posts/{$year}/{$month}/{$filename}";
    }

    /**
     * @param array $file $_FILES[...] girdisi
     * @return string Web'den erişilebilir yol (örn: /uploads/favicons/xxxx.svg)
     * @throws \RuntimeException
     */
    public static function storeFavicon(array $file): string
    {
        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Dosya yüklenemedi. (' . self::uploadErrorMessage((int) $file['error']) . ')');
        }
        if ((int) $file['size'] > self::FAVICON_MAX_BYTES) {
            throw new \RuntimeException('Favicon 1MB\'dan büyük olamaz.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'svg'], true)) {
            throw new \RuntimeException('Sadece png veya svg dosyalarına izin verilir.');
        }

        if ($ext === 'png') {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if ($mime !== 'image/png') {
                throw new \RuntimeException('Dosya türü doğrulanamadı.');
            }
        } else {
            $contents = file_get_contents($file['tmp_name']);
            if ($contents === false || !preg_match('/<svg[\s>]/i', $contents)) {
                throw new \RuntimeException('Geçerli bir SVG dosyası değil.');
            }
            if (preg_match('/<script[\s>]|javascript:|\son\w+\s*=/i', $contents)) {
                throw new \RuntimeException('SVG içinde script/olay işleyici bulunamamalı.');
            }
        }

        $dir = dirname(__DIR__, 2) . '/public/uploads/favicons';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = Str::random(20) . '.' . $ext;
        $destination = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Dosya kaydedilemedi.');
        }

        return "/uploads/favicons/{$filename}";
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'dosya izin verilen boyuttan büyük',
            UPLOAD_ERR_PARTIAL => 'dosya eksik yüklendi, tekrar deneyin',
            UPLOAD_ERR_NO_TMP_DIR => 'sunucuda geçici klasör yok',
            UPLOAD_ERR_CANT_WRITE => 'sunucuya yazılamadı',
            UPLOAD_ERR_EXTENSION => 'bir PHP eklentisi yüklemeyi durdurdu',
            default => 'bilinmeyen hata, kod ' . $code,
        };
    }
}
