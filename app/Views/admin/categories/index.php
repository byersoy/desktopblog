<?php
/** @var array $categories */

use App\Core\Csrf;
use App\Core\View;
?>
<div class="admin-topbar"><h2>Kategoriler</h2></div>

<div class="card">
    <h3 style="margin-top:0;">Yeni Kategori</h3>
    <form class="admin-form" method="post" action="/yonetim/kategoriler">
        <?= Csrf::field() ?>
        <div class="row">
            <div>
                <label for="name">Ad</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div>
                <label for="description">Açıklama</label>
                <input type="text" id="description" name="description">
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:14px;">Ekle</button>
    </form>
</div>

<div class="card">
    <table>
        <thead><tr><th>Ad</th><th>Slug</th><th>Yazı Sayısı</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td>
                    <form class="admin-form" method="post" action="/yonetim/kategoriler/<?= (int) $cat['id'] ?>/guncelle" style="display:flex;gap:8px;align-items:center;">
                        <?= Csrf::field() ?>
                        <input type="text" name="name" value="<?= View::e($cat['name']) ?>" style="width:160px;">
                        <input type="text" name="description" value="<?= View::e($cat['description']) ?>" placeholder="açıklama" style="width:220px;">
                        <button type="submit" class="btn btn-sm">Kaydet</button>
                    </form>
                </td>
                <td><?= View::e($cat['slug']) ?></td>
                <td><?= (int) $cat['post_count'] ?></td>
                <td>
                    <form method="post" action="/yonetim/kategoriler/<?= (int) $cat['id'] ?>/sil" onsubmit="return confirm('Silinsin mi?');">
                        <?= Csrf::field() ?>
                        <button type="submit" class="btn btn-sm btn-danger">Sil</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($categories === []): ?>
            <tr><td colspan="4">Henüz kategori yok.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
