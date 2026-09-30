<?php

use App\Core\App;
use App\Core\View;

$about = App::setting('about_content', "# Hakkımda\n\nBu bölüm yönetim panelinden (Ayarlar) düzenlenebilir.");
?>
<div class="article">
    <div class="article-body"><?= \App\Core\Markdown::toHtml($about) ?></div>
</div>
