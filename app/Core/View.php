<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    /**
     * @param string $view Views dizinine gore nokta veya slash ile yol, orn: "posts/show"
     * @param array $data View icine aktarilacak degiskenler
     * @param array{
     *   layout?: string|null,
     *   window?: array{id:string,title:string,icon?:string,kind?:string,width?:int,height?:int},
     *   seo?: array,
     *   jsonLd?: array<int, array>
     * } $options
     */
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/desktop', array $windowMeta = [], array $options = []): void
    {
        $content = self::capture($view, $data);
        $seo = $options['seo'] ?? [];
        $jsonLd = $options['jsonLd'] ?? [];

        if ($layout === null) {
            echo $content;
            return;
        }

        if (Request::wantsWindow()) {
            $badge = Emblem::badgeFor($windowMeta['id'] ?? $view);
            $status = http_response_code();
            Response::json([
                'html' => $content,
                'docTitle' => $seo['title'] ?? ($windowMeta['title'] ?? ''),
                'windowTitle' => $windowMeta['title'] ?? ($seo['title'] ?? ''),
                'status' => $seo['description'] ?? '',
                'icon' => $badge['icon'],
                'color' => $badge['color'],
            ], $status !== false ? $status : 200);
        }

        $layoutData = [
            'content' => $content,
            'window' => $windowMeta,
            'seo' => $seo,
            'jsonLd' => $jsonLd,
        ];
        echo self::capture($layout, $layoutData);
    }

    public static function admin(string $view, array $data, string $activeNav, string $pageTitle): void
    {
        $content = self::capture($view, $data);
        echo self::capture('layouts/admin', [
            'content' => $content,
            'activeNav' => $activeNav,
            'pageTitle' => $pageTitle,
        ]);
    }

    public static function capture(string $view, array $data = []): string
    {
        $path = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException("View bulunamadı: {$view}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        return (string) ob_get_clean();
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
