<?php
/** @var array $posts */

use App\Core\Csrf;
use App\Core\Str;
use App\Core\View;
?>
<div class="admin-topbar">
    <h2>Yazılar</h2>
    <a href="/yonetim/yazilar/yeni" class="btn btn-primary">+ Yeni Yazı</a>
</div>

<div class="card">
    <table>
        <thead><tr><th>Başlık</th><th>Kategori</th><th>Durum</th><th>Tarih</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($posts as $post): ?>
            <tr>
                <td><?= View::e($post['title']) ?></td>
                <td><?= View::e($post['category_name'] ?? '—') ?></td>
                <td><span class="badge badge-<?= $post['status'] ?>"><?= $post['status'] === 'published' ? 'Yayında' : 'Taslak' ?></span></td>
                <td><?= View::e(Str::trDate($post['created_at'])) ?></td>
                <td class="actions">
                    <a href="/yonetim/yazilar/<?= (int) $post['id'] ?>/duzenle" class="btn btn-sm">Düzenle</a>
                    <?php if ($post['status'] === 'published'): ?>
                        <a href="/yazi/<?= View::e($post['slug']) ?>" class="btn btn-sm" target="_blank" rel="noopener">Görüntüle</a>
                    <?php endif; ?>
                    <form method="post" action="/yonetim/yazilar/<?= (int) $post['id'] ?>/sil" onsubmit="return confirm('Bu yazı silinsin mi?');">
                        <?= Csrf::field() ?>
                        <button type="submit" class="btn btn-sm btn-danger">Sil</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($posts === []): ?>
            <tr><td colspan="5">Henüz yazı yok.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
