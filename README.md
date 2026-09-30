# byersoy blog — Kurulum Rehberi

Basit bir PHP blog sitesi. Kurulum için sunucuda SSH veya Composer'a
gerek yok — her şey zaten `vendor/` klasörünün içinde hazır geliyor.

---

## 1. Yerelde çalıştırma (FlyEnv, bilgisayarında)

### Adım 1 — Veritabanı oluştur

1. phpMyAdmin'i aç.
2. `byersoyblog` adında yeni bir veritabanı oluştur.
   - Charset: `utf8mb4`
   - Collation: `utf8mb4_unicode_ci`
3. `sql/` klasöründeki dosyaları **sırayla** çalıştır (Import):
   1. `001_schema.sql`
   2. `002_seed.sql`
   3. `003_fix_turkish_content.sql`
   4. `004_login_attempts.sql`
   5. Varsa sonraki numaralı dosyalar

### Adım 2 — `.env` dosyasını doldur

Proje kökünde `.env` dosyası hazır. Sadece şu satırı kendi MySQL
şifrenle güncelle:

```
DB_PASSWORD=...
```

### Adım 3 — Siteyi aç

Tarayıcıda **https://byersoyblog.test** adresine git.

Yönetim paneline girmek için: `/yonetim/giris`
- E-posta: `byektaersoy@gmail.com`
- Şifre: `ChangeMe123!`

**Giriş yaptıktan hemen sonra şifreni değiştir** (Ayarlar → Şifre Değiştir).

---

## 2. İnternete yükleme (cPanel, blog.byersoy.com)

### Adım 1 — Subdomain oluştur

cPanel → Domains/Subdomains:

- Subdomain: `blog` → `blog.byersoy.com`
- **Document Root**: cPanel'in otomatik önerdiği klasörü olduğu gibi
  bırak (örn. `/home/kullanici/blog.byersoy.com`) — `public` alt
  klasörüne ayarlamana **gerek yok**. Proje kökündeki `.htaccess`
  dosyası tüm istekleri otomatik olarak `public/` klasörüne yönlendirir;
  `app/`, `config/`, `vendor/`, `storage/`, `sql/`, `.env` gibi dosyalar
  bu sayede dışarıdan hiçbir zaman doğrudan erişilebilir olmaz.

### Adım 2 — Dosyaları yükle

1. Proje klasörünü zip'le. **`.env` dosyasını zip'e ekleme.**
   `vendor/` klasörünü mutlaka dahil et. `public/uploads/` klasörünü de
   dahil et — yazı kapak görselleri ve favicon buradaki gerçek dosyalara
   işaret ediyor, bu klasör olmadan görseller kırık gelir.
2. cPanel → Dosya Yöneticisi ile, subdomain oluştururken otomatik
   açılan klasöre yükle (örn. `~/blog.byersoy.com` — `public_html`
   içine değil).
3. Zip'i aç. `app/`, `config/`, `public/`, `vendor/` vb. klasörler bu
   klasörün doğrudan içinde olmalı (bir alt klasörün içinde değil).

### Adım 3 — PHP sürümünü seç

cPanel → MultiPHP Manager → bu subdomain için **PHP 8.5** (yoksa en
yeni 8.x) seç.

### Adım 4 — Veritabanı oluştur

cPanel → MySQL Databases:

1. Yeni veritabanı oluştur.
2. Yeni bir MySQL kullanıcısı oluştur, güçlü bir şifre ver.
3. Kullanıcıyı veritabanına ekle, **All Privileges** yetkisi ver.
4. phpMyAdmin'den bu veritabanına gir, `sql/` klasöründeki tüm
   dosyaları **sırayla** Import et (yukarıdaki Adım 1 ile aynı liste).

### Adım 5 — `.env` dosyasını sunucuda oluştur

Dosya Yöneticisi'nden, proje kökünde (public'in bir üstünde) `.env`
adında yeni bir dosya oluştur:

```
APP_NAME="byersoy blog"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://blog.byersoy.com
APP_LOCALE=tr
APP_TIMEZONE=Europe/Istanbul

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kullanici_byersoyblog
DB_USERNAME=kullanici_dbuser
DB_PASSWORD=...

MAIL_FROM_ADDRESS=no-reply@byersoy.com
MAIL_FROM_NAME="byersoy blog"
MAIL_TO_ADDRESS=byektaersoy@gmail.com

SEO_DEFAULT_TITLE="byersoy blog"
SEO_DEFAULT_DESCRIPTION="Yazılım, teknoloji ve daha fazlası üzerine bir blog."
SEO_TWITTER=
SEO_OG_IMAGE=
```

`APP_DEBUG=false` önemli — hatalar ziyaretçiye gösterilmez.

### Adım 6 — Yazma izinlerini kontrol et

`public/uploads/` ve `storage/logs/` klasörleri yazılabilir olmalı
(çoğu hostta zip'ten çıkınca zaten `755` ile gelir, ekstra ayar gerekmez;
görsel yükleme veya hata günlüğü çalışmazsa buradan kontrol et).

### Adım 7 — SSL'i aç

cPanel → SSL/TLS Status → AutoSSL ile `blog.byersoy.com` için sertifika
aktif et. Sonra `public/.htaccess` dosyasında şu 2 satırın başındaki
`#` işaretini kaldır:

```
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Adım 8 — Son kontrol

- [ ] `https://blog.byersoy.com` açılıyor mu?
- [ ] `/yonetim/giris` ile giriş yapıp şifreyi değiştirdin mi?
- [ ] `/sitemap.xml` ve `/robots.txt` doğru adresle açılıyor mu?

---

## 3. İçerik yönetimi (`/yonetim`)

| Ne yapmak istiyorsun? | Nereye git |
|---|---|
| Yazı ekle/düzenle | `/yonetim/yazilar` |
| Kategori/etiket ekle | `/yonetim/kategoriler`, `/yonetim/etiketler` |
| Site adı, açıklama, Hakkımda, sosyal linkler | `/yonetim/ayarlar` |
| Favicon değiştir | `/yonetim/ayarlar` → "Favicon" alanı (png/svg, max 1MB) |
| Gelen iletişim mesajları | `/yonetim/mesajlar` |

**Not:** Yönetim paneli sitenin görünümünden (Windows XP teması) ayrıdır
ve hiçbir sayfadan linklenmez — sadece adresi elle yazarak girilir.

---

## 4. Bilinmesi faydalı olanlar

- **Şifreyi yanlış girme limiti**: `/yonetim/giris` sayfasında aynı
  IP'den 15 dakikada 5 başarısız denemeden sonra giriş geçici olarak
  kilitlenir.
- **Hata olursa**: `APP_DEBUG=false` iken hata mesajları ziyaretçiye
  gösterilmez, bunun yerine `storage/logs/php-error.log` dosyasına
  yazılır. Bir şey çalışmıyorsa önce oraya bak.
- **Eski bir yazı linkini değiştirdiysen**, eski adresi yeni adrese
  yönlendirmek için `sql/` altına kendi SQL dosyanı ekleyebilirsin:
  ```sql
  INSERT INTO redirects (from_path, to_path, status_code, created_at)
  VALUES ('/eski-slug', '/yazi/yeni-slug', 301, NOW());
  ```
- **`sql/` klasörünü silmene gerek yok.** Bu klasör zaten tarayıcıdan
  hiçbir şekilde erişilemez (proje kökündeki `.htaccess` dosyası bunu
  engelliyor). Dosyaları sunucuda bırakmak, siteyi ileride başka bir
  sunucuya taşırsan veya veritabanını sıfırdan kurman gerekirse işine
  yarar.
