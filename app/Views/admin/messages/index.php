<?php
/** @var array $messages */

use App\Core\Csrf;
use App\Core\Str;
use App\Core\View;
?>
<div class="admin-topbar"><h2>Mesajlar</h2></div>

<div class="card">
    <table>
        <thead><tr><th>Ad</th><th>E-posta</th><th>Mesaj</th><th>Tarih</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($messages as $msg): ?>
            <tr>
                <td><?= View::e($msg['name']) ?></td>
                <td><a href="mailto:<?= View::e($msg['email']) ?>"><?= View::e($msg['email']) ?></a></td>
                <td style="max-width:340px;"><?= nl2br(View::e($msg['body'])) ?></td>
                <td><?= View::e(Str::trDate($msg['created_at'], true)) ?></td>
                <td>
                    <form method="post" action="/yonetim/mesajlar/<?= (int) $msg['id'] ?>/sil" onsubmit="return confirm('Silinsin mi?');">
                        <?= Csrf::field() ?>
                        <button type="submit" class="btn btn-sm btn-danger">Sil</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($messages === []): ?>
            <tr><td colspan="5">Henüz mesaj yok.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
