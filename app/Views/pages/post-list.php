<?php
/** @var array $posts */
/** @var int $page */
/** @var int $lastPage */
/** @var string $baseUrl */
/** @var string $heading */

use App\Core\View;
?>
<div class="article">
    <div class="section-header">
        <h1><?= View::e($heading) ?></h1>
    </div>
    <?= View::capture('partials/post-grid', ['posts' => $posts]) ?>
    <?= View::capture('partials/pagination', ['page' => $page, 'lastPage' => $lastPage, 'baseUrl' => $baseUrl]) ?>
</div>
