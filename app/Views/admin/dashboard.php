<?php
/** @var array $stats */
/** @var array $recentPosts */

use App\Core\Str;
use App\Core\View;
?>
<div class="admin-topbar"><h2>Panel</h2></div>

<div class="stat-grid">
    <div class="stat-card"><div class="num"><?= $stats['total'] ?></div><div class="label">Toplam Yazı</div></div>
    <div class="stat-card"><div class="num"><?= $stats['published'] ?></div><div class="label">Yayınlanan</div></div>
    <div class="stat-card"><div class="num"><?= $stats['categories'] ?></div><div class="label">Kategori</div></div>
    <div class="stat-card"><div class="num"><?= $stats['messages'] ?></div><div class="label">Mesaj</div></div>
</div>

<div class="card">
    <h3 style="margin-top:0;">Son Yazılar</h3>
    <table>
        <thead><tr><th>Başlık</th><th>Durum</th><th>Tarih</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recentPosts as $post): ?>
            <tr>
                <td><?= View::e($post['title']) ?></td>
                <td><span class="badge badge-<?= $post['status'] ?>"><?= $post['status'] === 'published' ? 'Yayında' : 'Taslak' ?></span></td>
                <td><?= View::e(Str::trDate($post['created_at'])) ?></td>
                <td><a href="/yonetim/yazilar/<?= (int) $post['id'] ?>/duzenle">Düzenle</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($recentPosts === []): ?>
            <tr><td colspan="4">Henüz yazı yok. <a href="/yonetim/yazilar/yeni">İlk yazıyı oluştur</a>.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
