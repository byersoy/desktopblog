<?php
/** @var string $siteName */
/** @var array $window */

use App\Core\App;
use App\Core\Emblem;
use App\Core\View;

$initialBadge = Emblem::badgeFor($window['id'] ?? 'home');

$navItems = [
    ['href' => '/', 'icon' => 'folder', 'color' => 'c-blue', 'label' => 'Ana Sayfa'],
    ['href' => '/yazilar', 'icon' => 'archive', 'color' => 'c-orange', 'label' => 'Yazılarım'],
    ['href' => '/kategoriler', 'icon' => 'tag', 'color' => 'c-green', 'label' => 'Kategoriler'],
    ['href' => '/arsiv', 'icon' => 'rss', 'color' => 'c-purple', 'label' => 'Arşiv'],
    ['href' => '/hakkimda', 'icon' => 'person', 'color' => 'c-pink', 'label' => 'Hakkımda'],
    ['href' => '/iletisim', 'icon' => 'mail', 'color' => 'c-teal', 'label' => 'İletişim'],
    ['href' => '/arama', 'icon' => 'search', 'color' => 'c-blue', 'label' => 'Ara'],
    ['href' => '/rss.xml', 'icon' => 'rss', 'color' => 'c-teal', 'label' => 'RSS Beslemesi'],
];
?>
<div class="start-menu" id="startMenu">
    <div class="start-menu-header">
        <span class="start-menu-avatar" aria-hidden="true"><span class="icon icon-person"></span></span>
        <span class="start-menu-name"><?= View::e($siteName) ?></span>
    </div>
    <ul class="start-menu-list">
        <?php foreach ($navItems as $item): ?>
            <li>
                <a href="<?= View::e($item['href']) ?>">
                    <span class="badge <?= View::e($item['color']) ?>" aria-hidden="true"><span class="icon icon-<?= View::e($item['icon']) ?>"></span></span>
                    <?= View::e($item['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="taskbar">
    <button class="start-btn" id="startBtn" type="button">
        <span class="start-flag" aria-hidden="true"></span>başlat
    </button>
    <div class="task-buttons" id="taskButtons">
        <button class="task-btn is-active" type="button" data-win-id="win-1">
            <span class="badge <?= View::e($initialBadge['color']) ?>" aria-hidden="true"><span class="icon icon-<?= View::e($initialBadge['icon']) ?>"></span></span>
            <span class="task-btn-label"><?= View::e($window['title'] ?? $siteName) ?></span>
        </button>
    </div>
    <div class="systray">
        <span class="tray-icons" aria-hidden="true"><span class="icon icon-gear"></span></span>
        <span class="tray-clock" id="trayClock">--:--</span>
    </div>
</div>

<script src="/assets/js/xp.js<?= App::assetVersion('assets/js/xp.js') ?>" defer></script>
