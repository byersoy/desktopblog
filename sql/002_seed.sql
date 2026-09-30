-- byersoyblog - Başlangıç verileri
-- 001_schema.sql çalıştırıldıktan SONRA bu dosyayı çalıştırın.

SET NAMES utf8mb4;

-- Varsayılan yönetici hesabı
-- E-posta: byektaersoy@gmail.com
-- Şifre  : ChangeMe123!   <-- İLK GİRİŞTEN SONRA MUTLAKA DEĞİŞTİRİN (Ayarlar > Şifre Değiştir)
INSERT INTO admins (name, email, password_hash, created_at)
VALUES ('Byektaersoy', 'byektaersoy@gmail.com', '$2y$12$PoOdY8/T0OlogQdEoxdpLuacBvMnkOOFtN4XsqyXlcdKIgJSPTSVC', NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Site ayarları
INSERT INTO settings (`key`, `value`) VALUES
    ('site_name', 'Bayraktar Yekta ERSOY - Blog'),
    ('site_description', 'Yazılım, tasarım ve teknoloji üzerine notlar.'),
    ('site_author', 'Bayraktar Yekta ERSOY'),
    ('wallpaper', ''),
    ('favicon', '/uploads/favicons/e7f58d56865021d34d9a.png'),
    ('og_image', ''),
    ('social_github', ''),
    ('social_twitter', ''),
    ('social_linkedin', ''),
    ('contact_email', 'byektaersoy@gmail.com'),
    ('about_summary', 'Bayraktar Yekta ERSOY'),
    ('about_content', '# Hakkımda\n\nHakkımda bilgi almak için https://byersoy.com sitesini inceleyebilirsiniz.')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- Kategoriler
INSERT INTO categories (name, slug, description, created_at) VALUES
    ('Genel', 'genel', 'Genel konular', NOW()),
    ('Yazılım', 'yazilim', 'Yazılım', NOW()),
    ('Teknoloji', 'teknoloji', 'Teknoloji', NOW()),
    ('Yapay Zeka', 'yapay-zeka', 'Yapay Zeka', NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Etiketler
INSERT INTO tags (name, slug, created_at) VALUES
    ('Duyuru', 'duyuru', NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Örnek/hoşgeldin yazısı
-- cover_image, public/uploads/posts/2026/08/ altındaki gerçek bir dosyaya
-- işaret eder. Sunucuya yüklerken proje klasörünüzle birlikte
-- public/uploads/ klasörünü de taşıdığınızdan emin olun (bkz. SETUP.md).
INSERT INTO posts
    (title, slug, excerpt, content_md, content_html, cover_image, status, category_id, author_id,
     meta_title, meta_description, reading_time, published_at, created_at, updated_at)
VALUES (
    'Merhaba Dünya',
    'merhaba-dunya',
    'Bu blogun ilk yazısı. Boş durmasın diye de diyebiliriz.',
    '# Merhaba Dünya\n\nBuralar hep dutluktu. Hala öyle.',
    '<h1>Merhaba Dünya<a id="content-merhaba-dunya" href="#content-merhaba-dunya" class="heading-permalink" aria-hidden="true" tabindex="-1" title="Permalink">¶</a></h1>\n<p>Buralar hep dutluktu. Hala öyle.</p>\n',
    '/uploads/posts/2026/08/41401534e7e40fc7a791.png',
    'published',
    (SELECT id FROM categories WHERE slug = 'genel' LIMIT 1),
    (SELECT id FROM admins WHERE email = 'byektaersoy@gmail.com' LIMIT 1),
    'Merhaba Dünya',
    NULL,
    1,
    NOW(),
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO post_tag (post_id, tag_id)
SELECT p.id, t.id FROM posts p, tags t
WHERE p.slug = 'merhaba-dunya' AND t.slug IN ('duyuru')
ON DUPLICATE KEY UPDATE post_id = post_id;
