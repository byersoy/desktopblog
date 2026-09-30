<?php
/** @var string $siteName */
/** @var string $activeSection */

use App\Core\View;

$icons = [
    ['href' => '/', 'icon' => 'folder', 'color' => 'c-blue', 'label' => 'Ana Sayfa', 'section' => 'home'],
    ['href' => '/yazilar', 'icon' => 'archive', 'color' => 'c-orange', 'label' => 'Yazılarım', 'section' => 'yazilar'],
    ['href' => '/kategoriler', 'icon' => 'tag', 'color' => 'c-green', 'label' => 'Kategoriler', 'section' => 'kategoriler'],
    ['href' => '/arsiv', 'icon' => 'rss', 'color' => 'c-purple', 'label' => 'Arşiv', 'section' => 'arsiv'],
    ['href' => '/hakkimda', 'icon' => 'person', 'color' => 'c-pink', 'label' => 'Hakkımda', 'section' => 'hakkimda'],
    ['href' => '/iletisim', 'icon' => 'mail', 'color' => 'c-teal', 'label' => 'İletişim', 'section' => 'iletisim'],
    ['href' => '/arama', 'icon' => 'search', 'color' => 'c-blue', 'label' => 'Ara'],
];
?>
<div class="desktop-icons" id="desktopIcons">
    <?php foreach ($icons as $item): ?>
        <a class="desktop-icon<?= ($activeSection ?? '') === ($item['section'] ?? '') ? ' is-selected' : '' ?>" href="<?= View::e($item['href']) ?>">
            <span class="badge <?= View::e($item['color']) ?>" aria-hidden="true"><span class="icon icon-<?= View::e($item['icon']) ?>"></span></span>
            <span class="di-label"><?= View::e($item['label']) ?></span>
        </a>
    <?php endforeach; ?>
</div>
