<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

ob_start();

require dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/config.php';
App::boot($config);

date_default_timezone_set($config['app']['timezone']);

if ($config['app']['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED);
    ini_set('log_errors', '1');
    $logDir = dirname(__DIR__) . '/storage/logs';
    if (is_dir($logDir) && is_writable($logDir)) {
        ini_set('error_log', $logDir . '/php-error.log');
    }

    set_exception_handler(function (Throwable $e): void {
        error_log(sprintf('[%s] Yakalanmayan hata: %s in %s:%d', date('c'), $e->getMessage(), $e->getFile(), $e->getLine()));
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code(500);
        echo 'Bir şeyler ters gitti. Lütfen daha sonra tekrar deneyin.';
    });
}

Session::start();

$request = new Request();

$router = require dirname(__DIR__) . '/config/routes.php';
/** @var Router $router */
$router->dispatch($request);
