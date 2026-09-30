<?php
/** @var array $categories */

use App\Core\Emblem;
use App\Core\View;
?>
<div class="article">
    <div class="section-header">
        <h1>Kategoriler</h1>
    </div>
    <?php if ($categories === []): ?>
        <p>Henüz kategori eklenmemiş.</p>
    <?php else: ?>
        <div class="headline-grid">
            <?php foreach ($categories as $category): ?>
                <?php $badge = Emblem::badgeFor($category['name']); ?>
                <a class="headline-card" href="/kategori/<?= View::e($category['slug']) ?>">
                    <div class="card-head">
                        <span class="file-badge <?= View::e($badge['color']) ?>" aria-hidden="true"><span class="icon icon-<?= View::e($badge['icon']) ?>"></span></span>
                        <h3><?= View::e($category['name']) ?></h3>
                    </div>
                    <p><?= View::e($category['description']) ?></p>
                    <div class="headline-meta"><span><?= (int) $category['post_count'] ?> yazı</span></div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
