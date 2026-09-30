<?php
/** @var array $post */
/** @var array $tags */
/** @var array $related */

use App\Core\Str;
use App\Core\View;
?>
<article class="article">
    <?php if (!empty($post['category_name'])): ?>
        <span class="kicker"><?= View::e($post['category_name']) ?></span>
    <?php endif; ?>
    <h1><?= View::e($post['title']) ?></h1>
    <div class="post-meta">
        <span><?= View::e(Str::trDate($post['published_at'])) ?></span>
        <span><?= (int) $post['reading_time'] ?> dk okuma</span>
        <?php if (!empty($post['category_name'])): ?>
            <a href="/kategori/<?= View::e($post['category_slug']) ?>"><?= View::e($post['category_name']) ?></a>
        <?php endif; ?>
    </div>
    <?php if (!empty($post['cover_image'])): ?>
        <img class="post-cover" src="<?= View::e($post['cover_image']) ?>" alt="<?= View::e($post['title']) ?>">
    <?php endif; ?>

    <div class="article-body"><?= $post['content_html'] ?></div>

    <?php if ($tags !== []): ?>
        <div class="post-tags">
            <?php foreach ($tags as $tag): ?>
                <a href="/etiket/<?= View::e($tag['slug']) ?>">#<?= View::e($tag['name']) ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($related !== []): ?>
        <p class="related-heading">Benzer Yazılar</p>
        <?= View::capture('partials/post-grid', ['posts' => $related]) ?>
    <?php endif; ?>
</article>
