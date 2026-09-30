<?php
/** @var array $errors */
/** @var string|null $success */
/** @var array $old */

use App\Core\Csrf;
use App\Core\View;
?>
<div class="article">
    <h1>İletişim</h1>
    <p>Aşağıdaki formu doldurarak bana ulaşabilirsiniz.</p>

    <?php if (!empty($success)): ?>
        <div class="form-success"><?= View::e($success) ?></div>
    <?php endif; ?>

    <form class="term-form" method="post" action="/iletisim">
        <?= Csrf::field() ?>
        <div aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
            <label for="website">Web sitesi</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>
        <label for="name">Ad Soyad</label>
        <input type="text" id="name" name="name" value="<?= View::e($old['name'] ?? '') ?>" required>

        <label for="email">E-posta</label>
        <input type="email" id="email" name="email" value="<?= View::e($old['email'] ?? '') ?>" required>

        <label for="body">Mesaj</label>
        <textarea id="body" name="body" required><?= View::e($old['body'] ?? '') ?></textarea>

        <?php if ($errors !== []): ?>
            <div class="form-error"><?= View::e(implode(' ', $errors)) ?></div>
        <?php endif; ?>

        <button type="submit">Gönder</button>
    </form>
</div>
