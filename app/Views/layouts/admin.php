<?php
/** @var string $content */
/** @var string $activeNav */
/** @var string $pageTitle */

use App\Core\App;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;

$activeNav = $activeNav ?? '';
$admin = Auth::user();
$flashSuccess = Session::flash('admin_success');
$flashError = Session::flash('admin_error');

$navLink = static function (string $href, string $label, string $key, string $active): string {
    $cls = $key === $active ? ' class="active"' : '';
    return '<a href="' . htmlspecialchars($href, ENT_QUOTES) . '"' . $cls . '>' . $label . '</a>';
};
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= View::e($pageTitle ?? 'Yönetim') ?> · Yönetim Paneli</title>
<meta name="robots" content="noindex, nofollow">
<?= App::faviconTag() ?>
<link rel="stylesheet" href="/assets/css/admin.css<?= App::assetVersion('assets/css/admin.css') ?>">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <h1><a href="/yonetim">⚙️ Yönetim</a></h1>
        <nav class="admin-nav">
            <?= $navLink('/yonetim', 'Panel', 'dashboard', $activeNav) ?>
            <?= $navLink('/yonetim/yazilar', 'Yazılar', 'posts', $activeNav) ?>
            <?= $navLink('/yonetim/kategoriler', 'Kategoriler', 'categories', $activeNav) ?>
            <?= $navLink('/yonetim/etiketler', 'Etiketler', 'tags', $activeNav) ?>
            <?= $navLink('/yonetim/mesajlar', 'Mesajlar', 'messages', $activeNav) ?>
            <?= $navLink('/yonetim/ayarlar', 'Ayarlar', 'settings', $activeNav) ?>
            <?= $navLink('/', 'Siteyi Görüntüle ↗', '', '') ?>
            <form method="post" action="/yonetim/cikis">
                <?= Csrf::field() ?>
                <button type="submit">Çıkış Yap (<?= View::e($admin['name'] ?? '') ?>)</button>
            </form>
        </nav>
    </aside>
    <main class="admin-main">
        <?php if ($flashSuccess): ?><div class="flash flash-success"><?= View::e($flashSuccess) ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="flash flash-error"><?= View::e($flashError) ?></div><?php endif; ?>
        <?= $content ?>
    </main>
</div>
</body>
</html>
