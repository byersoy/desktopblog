<?php
/** @var array $posts */

use App\Core\View;
?>
<div class="article">
    <div class="section-header">
        <h1>Son Yazılar</h1>
        <a class="more-link" href="/yazilar">Tümünü Gör →</a>
    </div>
    <?= View::capture('partials/post-grid', ['posts' => $posts]) ?>
</div>
