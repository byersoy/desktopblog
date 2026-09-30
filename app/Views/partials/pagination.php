<?php
/** @var int $page */
/** @var int $lastPage */
/** @var string $baseUrl */

use App\Core\View;

if ($lastPage <= 1) {
    return;
}
$sep = str_contains($baseUrl, '?') ? '&' : '?';
?>
<nav class="pagination" aria-label="Sayfalama">
    <?php if ($page > 1): ?>
        <a href="<?= View::e($baseUrl . $sep . 'sayfa=' . ($page - 1)) ?>">&larr; Önceki</a>
    <?php endif; ?>
    <span class="current"><?= $page ?> / <?= $lastPage ?></span>
    <?php if ($page < $lastPage): ?>
        <a href="<?= View::e($baseUrl . $sep . 'sayfa=' . ($page + 1)) ?>">Sonraki &rarr;</a>
    <?php endif; ?>
</nav>
