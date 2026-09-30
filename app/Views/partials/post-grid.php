<?php
/** @var array $posts */

use App\Core\Emblem;
use App\Core\Str;
use App\Core\View;
?>
<?php if ($posts === []): ?>
    <p>Henüz yayınlanmış bir yazı yok.</p>
<?php else: ?>
    <div class="headline-grid">
        <?php foreach ($posts as $post): ?>
            <?php $isNew = !empty($post['published_at']) && strtotime((string) $post['published_at']) >= strtotime('-7 days'); ?>
            <?php $badge = Emblem::badgeFor($post['category_name'] ?? $post['title']); ?>
            <a class="headline-card" href="/yazi/<?= View::e($post['slug']) ?>">
                <?php if ($isNew): ?><span class="ribbon-new">YENİ</span><?php endif; ?>
                <div class="card-head">
                    <span class="file-badge <?= View::e($badge['color']) ?>" aria-hidden="true"><span class="icon icon-<?= View::e($badge['icon']) ?>"></span></span>
                    <h3><?= View::e($post['title']) ?></h3>
                </div>
                <p><?= View::e($post['excerpt']) ?></p>
                <div class="headline-meta">
                    <span><?= View::e(Str::trDate($post['published_at'])) ?></span>
                    <?php if (!empty($post['category_name'])): ?>
                        <span>· <?= View::e($post['category_name']) ?></span>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
