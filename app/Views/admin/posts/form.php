<?php
/** @var array|null $post */
/** @var string $tagsSelected */
/** @var array $categories */
/** @var array $allTags */
/** @var array $errors */

use App\Core\Csrf;
use App\Core\View;

$post = $post ?? [];
$isEdit = !empty($post['id']);
$publishedAtValue = '';
if (!empty($post['published_at'])) {
    $ts = strtotime((string) $post['published_at']);
    if ($ts) {
        $publishedAtValue = date('Y-m-d\TH:i', $ts);
    }
}
?>
<div class="admin-topbar">
    <h2><?= $isEdit ? 'Yazıyı Düzenle' : 'Yeni Yazı' ?></h2>
    <a href="/yonetim/yazilar" class="btn">&larr; Listeye dön</a>
</div>

<?php if ($errors !== []): ?>
    <div class="flash flash-error"><?= View::e(implode(' ', $errors)) ?></div>
<?php endif; ?>

<form class="admin-form" method="post" action="<?= $isEdit ? '/yonetim/yazilar/' . (int) $post['id'] . '/duzenle' : '/yonetim/yazilar/yeni' ?>" id="post-form">
    <?= Csrf::field() ?>

    <div class="card">
        <label for="title">Başlık</label>
        <input type="text" id="title" name="title" value="<?= View::e($post['title'] ?? '') ?>" required>

        <div class="row">
            <div>
                <label for="slug">Slug (boş bırakılırsa başlıktan oluşturulur)</label>
                <input type="text" id="slug" name="slug" value="<?= View::e($post['slug'] ?? '') ?>">
            </div>
            <div>
                <label for="category_id">Kategori</label>
                <select id="category_id" name="category_id">
                    <option value="">— Seçiniz —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= (int) ($post['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>><?= View::e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <label for="excerpt">Özet (boş bırakılırsa içerikten otomatik oluşturulur)</label>
        <textarea id="excerpt" name="excerpt" style="min-height:60px;"><?= View::e($post['excerpt'] ?? '') ?></textarea>

        <label for="content_md">İçerik (Markdown)</label>
        <div class="md-tabs">
            <button type="button" data-tab="write" class="active">Yaz</button>
            <button type="button" data-tab="preview">Önizle</button>
        </div>
        <div class="md-toolbar">
            <button type="button" id="btn-undo" title="Geri Al (Ctrl+Z)">↶ Geri Al</button>
            <button type="button" id="btn-redo" title="Yinele (Ctrl+Shift+Z)">↷ Yinele</button>
            <span class="md-sep" aria-hidden="true"></span>
            <button type="button" data-block="# " title="Başlık 1">H1</button>
            <button type="button" data-block="## " title="Başlık 2">H2</button>
            <button type="button" data-block="### " title="Başlık 3">H3</button>
            <span class="md-sep" aria-hidden="true"></span>
            <button type="button" data-wrap="**" title="Kalın (Ctrl+B)"><strong>K</strong></button>
            <button type="button" data-wrap="_" title="İtalik (Ctrl+I)"><em>İ</em></button>
            <button type="button" data-wrap="~~" title="Üstü Çizili"><s>Ü</s></button>
            <button type="button" data-wrap="`" title="Satır İçi Kod">&lt;/&gt;</button>
            <span class="md-sep" aria-hidden="true"></span>
            <button type="button" data-block="- " title="Madde Listesi">• Liste</button>
            <button type="button" data-block-ol title="Numaralı Liste">1. Liste</button>
            <button type="button" data-block="- [ ] " title="Görev Listesi">☑ Görev</button>
            <button type="button" data-block="&gt; " title="Alıntı">❝ Alıntı</button>
            <span class="md-sep" aria-hidden="true"></span>
            <button type="button" data-link title="Bağlantı (Ctrl+K)">🔗 Bağlantı</button>
            <button type="button" id="btn-upload-image" title="Görsel yükle (sürükle-bırak veya yapıştır da olur)">🖼️ Görsel</button>
            <button type="button" data-codeblock title="Kod Bloğu">{ } Kod Bloğu</button>
            <button type="button" data-table title="Tablo">▦ Tablo</button>
            <button type="button" data-hr title="Yatay Çizgi">— Çizgi</button>
            <input type="file" id="image-input" accept="image/*" hidden>
        </div>
        <textarea id="content-editor" name="content_md" required><?= View::e($post['content_md'] ?? '') ?></textarea>
        <div class="md-preview" id="md-preview"></div>
        <p class="hint">Görseli editöre sürükleyip bırakabilir veya doğrudan panodan yapıştırabilirsiniz.</p>
        <p class="hint" id="upload-status"></p>
        <p class="md-stats" id="md-stats"></p>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Yayın</h3>
        <div class="row">
            <div>
                <label for="status">Durum</label>
                <select id="status" name="status">
                    <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Taslak</option>
                    <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Yayında</option>
                </select>
            </div>
            <div>
                <label for="published_at">Yayın Tarihi (boş = şimdi)</label>
                <input type="datetime-local" id="published_at" name="published_at" value="<?= View::e($publishedAtValue) ?>">
            </div>
        </div>

        <label for="tags">Etiketler (virgül ile ayırın)</label>
        <input type="text" id="tags" name="tags" value="<?= View::e($tagsSelected) ?>" placeholder="Linux, duyuru">

        <label for="cover_image">Kapak Görseli</label>
        <div class="cover-upload">
            <input type="text" id="cover_image" name="cover_image" value="<?= View::e($post['cover_image'] ?? '') ?>" placeholder="/uploads/posts/... veya yükleyin">
            <button type="button" id="btn-upload-cover" class="btn">Görsel Yükle</button>
            <input type="file" id="cover-image-input" accept="image/*" hidden>
        </div>
        <div class="cover-preview" id="cover-preview" style="<?= empty($post['cover_image']) ? 'display:none;' : '' ?>">
            <img id="cover-preview-img" src="<?= View::e($post['cover_image'] ?? '') ?>" alt="Kapak görseli önizleme">
            <button type="button" id="btn-cover-clear" title="Kaldır">✕</button>
        </div>
        <p class="hint">Dosya seçebilir, sürükleyip bırakabilir veya panodan yapıştırabilirsiniz.</p>
        <p class="hint" id="cover-upload-status"></p>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">SEO</h3>
        <label for="meta_title">Meta Başlık (boş = yazı başlığı)</label>
        <input type="text" id="meta_title" name="meta_title" value="<?= View::e($post['meta_title'] ?? '') ?>">
        <label for="meta_description">Meta Açıklama (boş = otomatik özet)</label>
        <textarea id="meta_description" name="meta_description" style="min-height:60px;"><?= View::e($post['meta_description'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Güncelle' : 'Oluştur' ?></button>
</form>

<script>
(function () {
    var csrf = document.querySelector('#post-form input[name="_csrf"]').value;
    var editor = document.getElementById('content-editor');
    var preview = document.getElementById('md-preview');
    var tabs = document.querySelectorAll('.md-tabs button');
    var statsEl = document.getElementById('md-stats');
    var uploadStatus = document.getElementById('upload-status');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) { t.classList.remove('active'); });
            tab.classList.add('active');
            if (tab.dataset.tab === 'preview') {
                fetch('/yonetim/onizleme', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: '_csrf=' + encodeURIComponent(csrf) + '&content_md=' + encodeURIComponent(editor.value)
                }).then(function (r) { return r.json(); }).then(function (data) {
                    preview.innerHTML = '<div class="article">' + (data.html || '') + '</div>';
                    editor.classList.add('hidden-editor');
                    preview.classList.add('active');
                });
            } else {
                editor.classList.remove('hidden-editor');
                preview.classList.remove('active');
            }
        });
    });

    function updateStats() {
        var text = editor.value.trim();
        var words = text.length ? text.split(/\s+/).length : 0;
        var minutes = Math.max(1, Math.round(words / 180));
        statsEl.textContent = words + ' kelime · ' + editor.value.length + ' karakter · ~' + minutes + ' dk okuma';
    }

    /* ---------- Geri Al / Yinele geçmişi ---------- */
    var undoBtn = document.getElementById('btn-undo');
    var redoBtn = document.getElementById('btn-redo');
    var MAX_HISTORY = 100;
    var history = [editor.value];
    var historyIndex = 0;
    var typingTimer = null;

    function refreshUndoButtons() {
        undoBtn.disabled = historyIndex <= 0 && editor.value === history[historyIndex];
        redoBtn.disabled = historyIndex >= history.length - 1;
    }

    function pushHistory() {
        var value = editor.value;
        if (history[historyIndex] === value) return;
        history = history.slice(0, historyIndex + 1);
        history.push(value);
        if (history.length > MAX_HISTORY) history.shift();
        historyIndex = history.length - 1;
        refreshUndoButtons();
    }

    function commit() {
        updateStats();
        pushHistory();
    }

    function setEditorValue(value) {
        editor.value = value;
        editor.focus();
        var pos = value.length;
        editor.selectionStart = pos;
        editor.selectionEnd = pos;
        updateStats();
    }

    function undo() {
        clearTimeout(typingTimer);
        if (editor.value !== history[historyIndex]) {
            setEditorValue(history[historyIndex]);
        } else if (historyIndex > 0) {
            historyIndex--;
            setEditorValue(history[historyIndex]);
        }
        refreshUndoButtons();
    }

    function redo() {
        clearTimeout(typingTimer);
        if (historyIndex < history.length - 1) {
            historyIndex++;
            setEditorValue(history[historyIndex]);
        }
        refreshUndoButtons();
    }

    undoBtn.addEventListener('click', undo);
    redoBtn.addEventListener('click', redo);

    function currentLineRange(value, start, end) {
        var lineStart = value.lastIndexOf('\n', start - 1) + 1;
        var lineEnd = value.indexOf('\n', end);
        if (lineEnd === -1) lineEnd = value.length;
        return { lineStart: lineStart, lineEnd: lineEnd };
    }

    function wrapSelection(open, close) {
        close = close || open;
        var start = editor.selectionStart, end = editor.selectionEnd;
        var value = editor.value;
        var selected = value.substring(start, end);
        editor.value = value.substring(0, start) + open + selected + close + value.substring(end);
        editor.focus();
        editor.selectionStart = start + open.length;
        editor.selectionEnd = start + open.length + selected.length;
        commit();
    }

    function prefixLines(prefix, numbered) {
        var start = editor.selectionStart, end = editor.selectionEnd;
        var value = editor.value;
        var range = currentLineRange(value, start, end);
        var block = value.substring(range.lineStart, range.lineEnd);
        var n = 1;
        var newBlock = block.split('\n').map(function (line) {
            if (!line.length) return line;
            return (numbered ? (n++) + '. ' : prefix) + line;
        }).join('\n');
        editor.value = value.substring(0, range.lineStart) + newBlock + value.substring(range.lineEnd);
        editor.focus();
        editor.selectionStart = range.lineStart;
        editor.selectionEnd = range.lineStart + newBlock.length;
        commit();
    }

    function insertAtCursor(text) {
        var start = editor.selectionStart, end = editor.selectionEnd;
        var value = editor.value;
        editor.value = value.substring(0, start) + text + value.substring(end);
        editor.focus();
        var pos = start + text.length;
        editor.selectionStart = pos;
        editor.selectionEnd = pos;
        commit();
    }

    document.querySelectorAll('.md-toolbar button[data-wrap]').forEach(function (btn) {
        btn.addEventListener('click', function () { wrapSelection(btn.dataset.wrap); });
    });
    document.querySelectorAll('.md-toolbar button[data-block]').forEach(function (btn) {
        btn.addEventListener('click', function () { prefixLines(btn.dataset.block, false); });
    });
    var olBtn = document.querySelector('.md-toolbar button[data-block-ol]');
    if (olBtn) olBtn.addEventListener('click', function () { prefixLines('', true); });

    var linkBtn = document.querySelector('.md-toolbar button[data-link]');
    function insertLink() {
        var start = editor.selectionStart, end = editor.selectionEnd;
        var value = editor.value;
        var selected = value.substring(start, end) || 'bağlantı metni';
        var text = '[' + selected + '](https://)';
        editor.value = value.substring(0, start) + text + value.substring(end);
        editor.focus();
        var urlPos = start + selected.length + 3;
        editor.selectionStart = urlPos;
        editor.selectionEnd = urlPos + 8;
        commit();
    }
    if (linkBtn) linkBtn.addEventListener('click', insertLink);

    var codeBlockBtn = document.querySelector('.md-toolbar button[data-codeblock]');
    if (codeBlockBtn) codeBlockBtn.addEventListener('click', function () {
        var start = editor.selectionStart, end = editor.selectionEnd;
        var value = editor.value;
        var selected = value.substring(start, end) || 'kod';
        var text = '```\n' + selected + '\n```\n';
        editor.value = value.substring(0, start) + text + value.substring(end);
        editor.focus();
        editor.selectionStart = start + 4;
        editor.selectionEnd = start + 4 + selected.length;
        commit();
    });

    var tableBtn = document.querySelector('.md-toolbar button[data-table]');
    if (tableBtn) tableBtn.addEventListener('click', function () {
        insertAtCursor('\n| Başlık 1 | Başlık 2 | Başlık 3 |\n| --- | --- | --- |\n| Hücre | Hücre | Hücre |\n');
    });

    var hrBtn = document.querySelector('.md-toolbar button[data-hr]');
    if (hrBtn) hrBtn.addEventListener('click', function () { insertAtCursor('\n---\n'); });

    editor.addEventListener('keydown', function (e) {
        var mod = e.metaKey || e.ctrlKey;
        if (!mod) return;
        if (e.key === 'b') { e.preventDefault(); wrapSelection('**'); }
        else if (e.key === 'i') { e.preventDefault(); wrapSelection('_'); }
        else if (e.key === 'k') { e.preventDefault(); insertLink(); }
        else if (e.key === 'z' && !e.shiftKey) { e.preventDefault(); undo(); }
        else if ((e.key === 'z' && e.shiftKey) || e.key === 'y') { e.preventDefault(); redo(); }
    });
    editor.addEventListener('input', function () {
        updateStats();
        clearTimeout(typingTimer);
        typingTimer = setTimeout(pushHistory, 500);
    });
    updateStats();
    refreshUndoButtons();

    function uploadFile(file) {
        if (!file || file.type.indexOf('image/') !== 0) return;
        uploadStatus.textContent = 'Yükleniyor...';
        var fd = new FormData();
        fd.append('image', file);
        fd.append('_csrf', csrf);
        fetch('/yonetim/medya/yukle', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.url) {
                    var pos = editor.selectionStart;
                    var text = '![](' + data.url + ')';
                    editor.value = editor.value.substring(0, pos) + text + editor.value.substring(pos);
                    uploadStatus.textContent = 'Görsel eklendi: ' + data.url;
                    commit();
                } else {
                    uploadStatus.textContent = 'Hata: ' + (data.error || 'bilinmeyen hata');
                }
            })
            .catch(function () { uploadStatus.textContent = 'Yükleme sırasında hata oluştu.'; });
    }

    var uploadBtn = document.getElementById('btn-upload-image');
    var imageInput = document.getElementById('image-input');
    uploadBtn.addEventListener('click', function () { imageInput.click(); });
    imageInput.addEventListener('change', function () {
        if (imageInput.files.length) uploadFile(imageInput.files[0]);
        imageInput.value = '';
    });

    editor.addEventListener('paste', function (e) {
        var items = (e.clipboardData || window.clipboardData).items;
        for (var i = 0; i < items.length; i++) {
            if (items[i].type.indexOf('image/') === 0) {
                e.preventDefault();
                uploadFile(items[i].getAsFile());
                return;
            }
        }
    });

    ['dragenter', 'dragover'].forEach(function (evt) {
        editor.addEventListener(evt, function (e) {
            e.preventDefault();
            editor.classList.add('is-drop-active');
        });
    });
    ['dragleave', 'drop'].forEach(function (evt) {
        editor.addEventListener(evt, function () { editor.classList.remove('is-drop-active'); });
    });
    editor.addEventListener('drop', function (e) {
        e.preventDefault();
        if (e.dataTransfer.files.length) uploadFile(e.dataTransfer.files[0]);
    });

    /* ---------- Kapak görseli yükleme ---------- */
    var coverInput = document.getElementById('cover_image');
    var coverFileInput = document.getElementById('cover-image-input');
    var coverUploadBtn = document.getElementById('btn-upload-cover');
    var coverPreview = document.getElementById('cover-preview');
    var coverPreviewImg = document.getElementById('cover-preview-img');
    var coverClearBtn = document.getElementById('btn-cover-clear');
    var coverStatus = document.getElementById('cover-upload-status');

    function setCoverImage(url) {
        coverInput.value = url;
        if (url) {
            coverPreviewImg.src = url;
            coverPreview.style.display = 'inline-block';
        } else {
            coverPreviewImg.src = '';
            coverPreview.style.display = 'none';
        }
    }

    function uploadCoverFile(file) {
        if (!file || file.type.indexOf('image/') !== 0) return;
        coverStatus.textContent = 'Yükleniyor...';
        var fd = new FormData();
        fd.append('image', file);
        fd.append('_csrf', csrf);
        fetch('/yonetim/medya/yukle', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.url) {
                    setCoverImage(data.url);
                    coverStatus.textContent = 'Kapak görseli yüklendi.';
                } else {
                    coverStatus.textContent = 'Hata: ' + (data.error || 'bilinmeyen hata');
                }
            })
            .catch(function () { coverStatus.textContent = 'Yükleme sırasında hata oluştu.'; });
    }

    coverUploadBtn.addEventListener('click', function () { coverFileInput.click(); });
    coverFileInput.addEventListener('change', function () {
        if (coverFileInput.files.length) uploadCoverFile(coverFileInput.files[0]);
        coverFileInput.value = '';
    });
    coverClearBtn.addEventListener('click', function () { setCoverImage(''); coverStatus.textContent = ''; });
    coverInput.addEventListener('input', function () {
        if (coverInput.value) {
            coverPreviewImg.src = coverInput.value;
            coverPreview.style.display = 'inline-block';
        } else {
            coverPreview.style.display = 'none';
        }
    });
    coverPreviewImg.addEventListener('error', function () {
        if (coverInput.value) coverPreview.style.display = 'none';
    });
    coverPreviewImg.addEventListener('load', function () {
        if (coverInput.value) coverPreview.style.display = 'inline-block';
    });

    ['dragenter', 'dragover'].forEach(function (evt) {
        coverInput.addEventListener(evt, function (e) { e.preventDefault(); coverInput.classList.add('is-drop-active'); });
    });
    ['dragleave', 'drop'].forEach(function (evt) {
        coverInput.addEventListener(evt, function () { coverInput.classList.remove('is-drop-active'); });
    });
    coverInput.addEventListener('drop', function (e) {
        e.preventDefault();
        if (e.dataTransfer.files.length) uploadCoverFile(e.dataTransfer.files[0]);
    });
    coverInput.addEventListener('paste', function (e) {
        var items = (e.clipboardData || window.clipboardData).items;
        for (var i = 0; i < items.length; i++) {
            if (items[i].type.indexOf('image/') === 0) {
                e.preventDefault();
                uploadCoverFile(items[i].getAsFile());
                return;
            }
        }
    });
})();
</script>
