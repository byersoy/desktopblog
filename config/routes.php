<?php

declare(strict_types=1);

use App\Controllers\Admin\AdminAuthController;
use App\Controllers\Admin\AdminCategoryController;
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AdminMessageController;
use App\Controllers\Admin\AdminPostController;
use App\Controllers\Admin\AdminSettingsController;
use App\Controllers\Admin\AdminTagController;
use App\Controllers\ArchiveController;
use App\Controllers\CategoryController;
use App\Controllers\FeedController;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\PostController;
use App\Controllers\RobotsController;
use App\Controllers\SearchController;
use App\Controllers\SitemapController;
use App\Controllers\TagController;
use App\Core\Router;

$router = new Router();

// ---- Genel site ----
$router->get('/', [HomeController::class, 'index']);
$router->get('/yazilar', [PostController::class, 'index']);
$router->get('/rastgele', [PostController::class, 'random']);
$router->get('/yazi/{slug}', [PostController::class, 'show']);
$router->get('/kategoriler', [CategoryController::class, 'index']);
$router->get('/kategori/{slug}', [CategoryController::class, 'show']);
$router->get('/etiket/{slug}', [TagController::class, 'show']);
$router->get('/arsiv', [ArchiveController::class, 'index']);
$router->get('/hakkimda', [PageController::class, 'about']);
$router->get('/iletisim', [PageController::class, 'contact']);
$router->post('/iletisim', [PageController::class, 'contactSubmit']);
$router->get('/arama', [SearchController::class, 'page']);
$router->get('/arama.json', [SearchController::class, 'json']);
$router->get('/rss.xml', [FeedController::class, 'rss']);
$router->get('/sitemap.xml', [SitemapController::class, 'index']);
$router->get('/robots.txt', [RobotsController::class, 'index']);

// ---- Yonetim paneli ----
$router->get('/yonetim/giris', [AdminAuthController::class, 'loginForm']);
$router->post('/yonetim/giris', [AdminAuthController::class, 'login']);
$router->post('/yonetim/cikis', [AdminAuthController::class, 'logout']);

$router->get('/yonetim', [AdminDashboardController::class, 'index']);

$router->get('/yonetim/yazilar', [AdminPostController::class, 'index']);
$router->get('/yonetim/yazilar/yeni', [AdminPostController::class, 'create']);
$router->post('/yonetim/yazilar/yeni', [AdminPostController::class, 'store']);
$router->get('/yonetim/yazilar/{id}/duzenle', [AdminPostController::class, 'edit']);
$router->post('/yonetim/yazilar/{id}/duzenle', [AdminPostController::class, 'update']);
$router->post('/yonetim/yazilar/{id}/sil', [AdminPostController::class, 'destroy']);
$router->post('/yonetim/medya/yukle', [AdminPostController::class, 'uploadImage']);
$router->post('/yonetim/onizleme', [AdminPostController::class, 'preview']);

$router->get('/yonetim/kategoriler', [AdminCategoryController::class, 'index']);
$router->post('/yonetim/kategoriler', [AdminCategoryController::class, 'store']);
$router->post('/yonetim/kategoriler/{id}/guncelle', [AdminCategoryController::class, 'update']);
$router->post('/yonetim/kategoriler/{id}/sil', [AdminCategoryController::class, 'destroy']);

$router->get('/yonetim/etiketler', [AdminTagController::class, 'index']);
$router->post('/yonetim/etiketler', [AdminTagController::class, 'store']);
$router->post('/yonetim/etiketler/{id}/sil', [AdminTagController::class, 'destroy']);

$router->get('/yonetim/ayarlar', [AdminSettingsController::class, 'index']);
$router->post('/yonetim/ayarlar', [AdminSettingsController::class, 'update']);
$router->post('/yonetim/ayarlar/sifre', [AdminSettingsController::class, 'updatePassword']);
$router->post('/yonetim/medya/favicon-yukle', [AdminSettingsController::class, 'uploadFavicon']);

$router->get('/yonetim/mesajlar', [AdminMessageController::class, 'index']);
$router->post('/yonetim/mesajlar/{id}/sil', [AdminMessageController::class, 'destroy']);

return $router;
