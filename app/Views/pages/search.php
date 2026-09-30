<?php
/** @var string $q */
/** @var array $results */

use App\Core\View;
?>
<div class="article">
    <div class="section-header">
        <h1>Ara</h1>
    </div>
    <form class="term-form" method="get" action="/arama" role="search">
        <label for="q">Anahtar kelime</label>
        <input type="search" id="q" name="q" value="<?= View::e($q) ?>" placeholder="Ne arıyorsunuz?">
        <button type="submit">Ara</button>
    </form>

    <?php if ($q !== ''): ?>
        <h2>Sonuçlar</h2>
        <?= View::capture('partials/post-grid', ['posts' => $results]) ?>
    <?php endif; ?>
</div>
