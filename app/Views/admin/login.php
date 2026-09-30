<?php
/** @var string|null $error */

use App\Core\App;
use App\Core\Csrf;
use App\Core\View;
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Giriş · Yönetim Paneli</title>
<meta name="robots" content="noindex, nofollow">
<?= App::faviconTag() ?>
<link rel="stylesheet" href="/assets/css/admin.css<?= App::assetVersion('assets/css/admin.css') ?>">
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <h1>⚙️ Yönetim Paneli</h1>
        <?php if (!empty($error)): ?>
            <div class="flash flash-error"><?= View::e($error) ?></div>
        <?php endif; ?>
        <form class="admin-form" method="post" action="/yonetim/giris">
            <?= Csrf::field() ?>
            <label for="email">E-posta</label>
            <input type="email" id="email" name="email" required autofocus>
            <label for="password">Şifre</label>
            <input type="password" id="password" name="password" required>
            <button type="submit" class="btn btn-primary" style="margin-top:18px;width:100%;">Giriş Yap</button>
        </form>
    </div>
</div>
</body>
</html>
