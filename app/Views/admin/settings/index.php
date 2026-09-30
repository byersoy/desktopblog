<?php
/** @var array $settings */
/** @var array $admin */

use App\Core\Csrf;
use App\Core\View;

$s = static fn(string $key) => View::e($settings[$key] ?? '');
?>
<div class="admin-topbar"><h2>Ayarlar</h2></div>

<form class="admin-form" method="post" action="/yonetim/ayarlar" id="settings-form">
    <?= Csrf::field() ?>

    <div class="card">
        <h3 style="margin-top:0;">Site</h3>
        <div class="row">
            <div>
                <label for="site_name">Site Adı</label>
                <input type="text" id="site_name" name="site_name" value="<?= $s('site_name') ?>">
            </div>
            <div>
                <label for="site_author">Yazar Adı</label>
                <input type="text" id="site_author" name="site_author" value="<?= $s('site_author') ?>">
            </div>
        </div>
        <label for="site_description">Site Açıklaması</label>
        <textarea id="site_description" name="site_description" style="min-height:60px;"><?= $s('site_description') ?></textarea>

        <label for="favicon">Favicon</label>
        <div class="cover-upload">
            <input type="text" id="favicon" name="favicon" value="<?= $s('favicon') ?>" placeholder="/assets/img/favicon.svg (varsayılan)">
            <button type="button" id="btn-upload-favicon" class="btn">Favicon Yükle</button>
            <input type="file" id="favicon-input" accept=".png,.svg,image/png,image/svg+xml" hidden>
        </div>
        <div class="cover-preview" id="favicon-preview" style="<?= empty($settings['favicon']) ? 'display:none;' : '' ?>">
            <img id="favicon-preview-img" src="<?= $s('favicon') ?>" alt="Favicon önizleme" style="max-width:64px;max-height:64px;object-fit:contain;background:#fff;">
            <button type="button" id="btn-favicon-clear" title="Kaldır">✕</button>
        </div>
        <p class="hint">Sadece png veya svg, en fazla 1MB. Boş bırakılırsa varsayılan simge kullanılır.</p>
        <p class="hint" id="favicon-upload-status"></p>

        <label for="og_image">Varsayılan Paylaşım Görseli (og:image) URL</label>
        <input type="text" id="og_image" name="og_image" value="<?= $s('og_image') ?>">

        <label for="contact_email">İletişim E-postası</label>
        <input type="email" id="contact_email" name="contact_email" value="<?= $s('contact_email') ?>">
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Hakkımda Sayfası</h3>
        <label for="about_summary">Kısa Özet (SEO açıklaması)</label>
        <input type="text" id="about_summary" name="about_summary" value="<?= $s('about_summary') ?>">
        <label for="about_content">İçerik (Markdown)</label>
        <textarea id="about_content" name="about_content" style="min-height:220px;"><?= $s('about_content') ?></textarea>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Sosyal Bağlantılar</h3>
        <div class="row">
            <div><label for="social_github">GitHub</label><input type="url" id="social_github" name="social_github" value="<?= $s('social_github') ?>"></div>
            <div><label for="social_twitter">Twitter / X</label><input type="url" id="social_twitter" name="social_twitter" value="<?= $s('social_twitter') ?>"></div>
            <div><label for="social_linkedin">LinkedIn</label><input type="url" id="social_linkedin" name="social_linkedin" value="<?= $s('social_linkedin') ?>"></div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Yönetici Profili</h3>
        <div class="row">
            <div><label for="admin_name">Ad</label><input type="text" id="admin_name" name="admin_name" value="<?= View::e($admin['name'] ?? '') ?>"></div>
            <div><label for="admin_email">E-posta</label><input type="email" id="admin_email" name="admin_email" value="<?= View::e($admin['email'] ?? '') ?>"></div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Kaydet</button>
</form>

<div class="card" style="margin-top:20px;">
    <h3 style="margin-top:0;">Şifre Değiştir</h3>
    <form class="admin-form" method="post" action="/yonetim/ayarlar/sifre">
        <?= Csrf::field() ?>
        <div class="row">
            <div><label for="current_password">Mevcut Şifre</label><input type="password" id="current_password" name="current_password" required></div>
            <div><label for="new_password">Yeni Şifre</label><input type="password" id="new_password" name="new_password" required minlength="8"></div>
            <div><label for="new_password_confirm">Yeni Şifre (Tekrar)</label><input type="password" id="new_password_confirm" name="new_password_confirm" required minlength="8"></div>
        </div>
        <button type="submit" class="btn" style="margin-top:14px;">Şifreyi Güncelle</button>
    </form>
</div>

<script>
(function () {
    var csrf = document.querySelector('#settings-form input[name="_csrf"]').value;
    var faviconInput = document.getElementById('favicon');
    var faviconFileInput = document.getElementById('favicon-input');
    var faviconUploadBtn = document.getElementById('btn-upload-favicon');
    var faviconPreview = document.getElementById('favicon-preview');
    var faviconPreviewImg = document.getElementById('favicon-preview-img');
    var faviconClearBtn = document.getElementById('btn-favicon-clear');
    var faviconStatus = document.getElementById('favicon-upload-status');

    function setFavicon(url) {
        faviconInput.value = url;
        if (url) {
            faviconPreviewImg.src = url;
            faviconPreview.style.display = 'inline-block';
        } else {
            faviconPreviewImg.src = '';
            faviconPreview.style.display = 'none';
        }
    }

    function uploadFavicon(file) {
        if (!file) return;
        faviconStatus.textContent = 'Yükleniyor...';
        var fd = new FormData();
        fd.append('favicon', file);
        fd.append('_csrf', csrf);
        fetch('/yonetim/medya/favicon-yukle', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.url) {
                    setFavicon(data.url);
                    faviconStatus.textContent = 'Favicon yüklendi. Kaydetmeyi unutmayın.';
                } else {
                    faviconStatus.textContent = 'Hata: ' + (data.error || 'bilinmeyen hata');
                }
            })
            .catch(function () { faviconStatus.textContent = 'Yükleme sırasında hata oluştu.'; });
    }

    faviconUploadBtn.addEventListener('click', function () { faviconFileInput.click(); });
    faviconFileInput.addEventListener('change', function () {
        if (faviconFileInput.files.length) uploadFavicon(faviconFileInput.files[0]);
        faviconFileInput.value = '';
    });
    faviconClearBtn.addEventListener('click', function () { setFavicon(''); faviconStatus.textContent = ''; });
    faviconInput.addEventListener('input', function () {
        if (faviconInput.value) {
            faviconPreviewImg.src = faviconInput.value;
            faviconPreview.style.display = 'inline-block';
        } else {
            faviconPreview.style.display = 'none';
        }
    });

    ['dragenter', 'dragover'].forEach(function (evt) {
        faviconInput.addEventListener(evt, function (e) { e.preventDefault(); faviconInput.classList.add('is-drop-active'); });
    });
    ['dragleave', 'drop'].forEach(function (evt) {
        faviconInput.addEventListener(evt, function () { faviconInput.classList.remove('is-drop-active'); });
    });
    faviconInput.addEventListener('drop', function (e) {
        e.preventDefault();
        if (e.dataTransfer.files.length) uploadFavicon(e.dataTransfer.files[0]);
    });
})();
</script>
