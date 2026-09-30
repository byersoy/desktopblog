<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    /**
     * Beklenmeyen bir PHP uyarisi/notice'i (orn. php.ini upload limiti asimi)
     * cikti tamponuna JSON/XML'den once bir sey yazmis olabilir. Bunu temizleyip
     * yanitin gecerli kalmasini saglar.
     */
    private static function discardStrayOutput(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    public static function json(array $data, int $status = 200): never
    {
        self::discardStrayOutput();
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }

    public static function notFound(): never
    {
        http_response_code(404);
        View::render('errors/404', [], layout: 'layouts/desktop', windowMeta: [
            'id' => 'not-found',
            'title' => 'Bulunamadı',
            'kind' => 'finder',
        ], options: [
            'seo' => [
                'title' => 'Sayfa Bulunamadı (404)',
                'description' => 'Aradığınız sayfa bulunamadı.',
                'canonical' => \App\Core\Seo::siteUrl($_SERVER['REQUEST_URI'] ?? '/'),
                'noindex' => true,
            ],
        ]);
        exit;
    }

    public static function xml(string $content, int $status = 200): never
    {
        self::discardStrayOutput();
        http_response_code($status);
        header('Content-Type: application/xml; charset=utf-8');
        echo $content;
        exit;
    }

    public static function text(string $content, int $status = 200, string $type = 'text/plain'): never
    {
        self::discardStrayOutput();
        http_response_code($status);
        header('Content-Type: ' . $type . '; charset=utf-8');
        echo $content;
        exit;
    }
}
