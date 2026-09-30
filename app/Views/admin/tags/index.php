<?php
/** @var array $tags */

use App\Core\Csrf;
use App\Core\View;
?>
<div class="admin-topbar"><h2>Etiketler</h2></div>

<div class="card">
    <form class="admin-form" method="post" action="/yonetim/etiketler" style="display:flex;gap:10px;align-items:flex-end;">
        <?= Csrf::field() ?>
        <div style="flex:1;">
            <label for="name">Yeni etiket</label>
            <input type="text" id="name" name="name" required>
        </div>
        <button type="submit" class="btn btn-primary">Ekle</button>
    </form>
</div>

<div class="card">
    <table>
        <thead><tr><th>Ad</th><th>Slug</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($tags as $tag): ?>
            <tr>
                <td><?= View::e($tag['name']) ?></td>
                <td><?= View::e($tag['slug']) ?></td>
                <td>
                    <form method="post" action="/yonetim/etiketler/<?= (int) $tag['id'] ?>/sil" onsubmit="return confirm('Silinsin mi?');">
                        <?= Csrf::field() ?>
                        <button type="submit" class="btn btn-sm btn-danger">Sil</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($tags === []): ?>
            <tr><td colspan="3">Henüz etiket yok.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
