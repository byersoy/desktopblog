<?php
/** @var string $content */
/** @var array $window */
/** @var array $seo */
/** @var array $jsonLd */

use App\Core\App;
use App\Core\Seo;
use App\Core\View;

$siteName = App::setting('site_name', App::config('app')['name']);
$siteTagline = App::setting('site_description', '');
$pageTitle = $seo['title'] ?? $siteName;

$windowId = $window['id'] ?? 'home';
$activeSection = match (true) {
    $windowId === 'home' => 'home',
    str_starts_with($windowId, 'yazi-') || in_array($windowId, ['yazilar'], true) => 'yazilar',
    str_starts_with($windowId, 'kategori-') || in_array($windowId, ['kategoriler'], true) => 'kategoriler',
    str_starts_with($windowId, 'etiket-') => 'yazilar',
    $windowId === 'arsiv' => 'arsiv',
    $windowId === 'hakkimda' => 'hakkimda',
    $windowId === 'iletisim' => 'iletisim',
    default => '',
};

$statusText = $seo['description'] ?? '';
if (mb_strlen($statusText) > 90) {
    $statusText = mb_substr($statusText, 0, 87) . '…';
}
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= View::e($pageTitle) ?></title>
<?= Seo::renderMeta($seo + ['title' => $pageTitle]) ?>
<?php foreach ($jsonLd as $graph): ?>
<?= Seo::jsonLd($graph) ?>
<?php endforeach; ?>
<?= App::faviconTag() ?>
<link rel="alternate" type="application/rss+xml" title="<?= View::e($siteName) ?> RSS" href="/rss.xml">
<link rel="stylesheet" href="/assets/css/xp.css<?= App::assetVersion('assets/css/xp.css') ?>">
</head>
<body>
<?= View::capture('partials/site-header', ['siteName' => $siteName, 'activeSection' => $activeSection]) ?>

<section class="os-window" id="mainWindow" data-win-id="win-1" data-href="<?= View::e($currentPath) ?>">
  <header class="os-window-title">
    <span class="win-icon" aria-hidden="true"><span class="icon icon-folder"></span></span>
    <span class="win-title-text"><?= View::e($window['title'] ?? $siteName) ?></span>
    <span class="win-controls">
      <button class="win-btn win-min" type="button" title="Simge Durumuna Küçült" aria-label="Simge Durumuna Küçült"></button>
      <button class="win-btn win-max" type="button" title="Ekranı Kapla" aria-label="Ekranı Kapla"></button>
      <button class="win-btn win-close" type="button" title="Kapat" aria-label="Kapat"></button>
    </span>
  </header>

  <nav class="win-menubar">
    <div class="menu-drop">
      <button class="menu-label" type="button">Dosya</button>
      <div class="menu-panel">
        <a href="/"><span class="icon icon-folder"></span>Yeni Pencere</a>
        <button type="button" data-action="print"><span class="icon icon-archive"></span>Sayfayı Yazdır</button>
        <div class="menu-sep"></div>
        <button type="button" data-action="close"><span class="icon icon-tag"></span>Kapat</button>
      </div>
    </div>
    <div class="menu-drop">
      <button class="menu-label" type="button">Düzen</button>
      <div class="menu-panel">
        <a href="/arama"><span class="icon icon-search"></span>Sayfada Bul…</a>
        <button type="button" data-action="copy-link"><span class="icon icon-tag"></span>Bağlantıyı Kopyala</button>
      </div>
    </div>
    <div class="menu-drop">
      <button class="menu-label" type="button">Görünüm</button>
      <div class="menu-panel">
        <button type="button" data-action="reload"><span class="icon icon-gear"></span>Yenile</button>
        <button type="button" data-action="minimize"><span class="icon icon-archive"></span>Simge Durumuna Küçült</button>
        <button type="button" data-action="maximize"><span class="icon icon-folder"></span>Tam Ekran</button>
      </div>
    </div>
    <div class="menu-drop">
      <button class="menu-label" type="button">Favoriler</button>
      <div class="menu-panel">
        <a href="/"><span class="icon icon-folder"></span>Ana Sayfa</a>
        <a href="/yazilar"><span class="icon icon-archive"></span>Yazılarım</a>
        <a href="/kategoriler"><span class="icon icon-tag"></span>Kategoriler</a>
        <a href="/arsiv"><span class="icon icon-rss"></span>Arşiv</a>
        <a href="/hakkimda"><span class="icon icon-person"></span>Hakkımda</a>
        <a href="/iletisim"><span class="icon icon-mail"></span>İletişim</a>
      </div>
    </div>
    <div class="menu-drop">
      <button class="menu-label" type="button">Araçlar</button>
      <div class="menu-panel">
        <a href="/rss.xml"><span class="icon icon-rss"></span>RSS Beslemesi</a>
      </div>
    </div>
    <div class="menu-drop">
      <button class="menu-label" type="button">Yardım</button>
      <div class="menu-panel">
        <a href="/hakkimda"><span class="icon icon-person"></span>Bu Site Hakkında</a>
      </div>
    </div>
    <span class="menubar-logo" aria-hidden="true">
      <svg viewBox="0 0 20 16" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="9" height="7" fill="#ff8a2a"/>
        <rect x="10" y="0" width="10" height="7" fill="#7bd83a"/>
        <rect x="0" y="8" width="9" height="8" fill="#3a9dff"/>
        <rect x="10" y="8" width="10" height="8" fill="#ffe23a"/>
      </svg>
    </span>
  </nav>

  <div class="win-toolbar">
    <button class="tb-btn" type="button" data-action="back">&larr; <span class="tb-text">Geri</span></button>
    <button class="tb-btn" type="button" data-action="forward"><span class="tb-text">İleri</span> &rarr;</button>
    <span class="tb-sep" aria-hidden="true"></span>
    <button class="tb-btn" type="button" data-action="reload"><span class="icon icon-gear"></span><span class="tb-text">Yenile</span></button>
    <a class="tb-btn" href="/arama"><span class="icon icon-search"></span><span class="tb-text">Ara</span></a>
  </div>

  <div class="win-address">
    <span class="addr-label">Adres</span>
    <input class="addr-input" type="text" value="" spellcheck="false">
    <button class="addr-go" type="button"><span class="icon icon-folder"></span>Git</button>
  </div>

  <div class="os-window-body">
    <?= $content ?>
  </div>

  <div class="os-window-status">
    <span><?= View::e($statusText) ?></span>
    <span>İnternet</span>
  </div>

  <div class="win-resize" aria-hidden="true"></div>
</section>

<?= View::capture('partials/site-footer', ['siteName' => $siteName, 'window' => $window]) ?>
</body>
</html>
