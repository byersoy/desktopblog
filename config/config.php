<?php

declare(strict_types=1);

use App\Core\Env;

Env::load(dirname(__DIR__) . '/.env');

return [
    'app' => [
        'name' => Env::get('APP_NAME', 'byersoy blog'),
        'env' => Env::get('APP_ENV', 'production'),
        'debug' => filter_var(Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL),
        'url' => rtrim((string) Env::get('APP_URL', 'https://byersoyblog.test'), '/'),
        'locale' => Env::get('APP_LOCALE', 'tr'),
        'timezone' => Env::get('APP_TIMEZONE', 'Europe/Istanbul'),
    ],
    'db' => [
        'host' => Env::get('DB_HOST', '127.0.0.1'),
        'port' => Env::get('DB_PORT', '3306'),
        'database' => Env::get('DB_DATABASE', 'byersoyblog'),
        'username' => Env::get('DB_USERNAME', 'root'),
        'password' => Env::get('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'from_address' => Env::get('MAIL_FROM_ADDRESS', 'no-reply@byersoy.com'),
        'from_name' => Env::get('MAIL_FROM_NAME', 'byersoy blog'),
        'to_address' => Env::get('MAIL_TO_ADDRESS', 'byektaersoy@gmail.com'),
    ],
    'seo' => [
        'default_title' => Env::get('SEO_DEFAULT_TITLE', 'byersoy blog'),
        'default_description' => Env::get('SEO_DEFAULT_DESCRIPTION', 'Yazılım, teknoloji ve daha fazlası üzerine bir blog.'),
        'twitter' => Env::get('SEO_TWITTER', ''),
        'og_image' => Env::get('SEO_OG_IMAGE', ''),
    ],
];
