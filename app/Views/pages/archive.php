<?php
/** @var array $groups */

use App\Core\Str;
use App\Core\View;

$trMonths = [1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs', 6 => 'Haziran',
    7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'];
?>
<div class="article">
    <div class="section-header">
        <h1>Arşiv</h1>
    </div>
    <?php if ($groups === []): ?>
        <p>Henüz yazı yok.</p>
    <?php endif; ?>
    <?php foreach ($groups as $year => $months): ?>
        <h2><?= View::e((string) $year) ?></h2>
        <?php krsort($months); ?>
        <?php foreach ($months as $month => $posts): ?>
            <h3><?= View::e($trMonths[(int) $month]) ?></h3>
            <ul>
                <?php foreach ($posts as $post): ?>
                    <li>
                        <a href="/yazi/<?= View::e($post['slug']) ?>"><?= View::e($post['title']) ?></a>
                        <span><?= View::e(Str::trDate($post['published_at'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
