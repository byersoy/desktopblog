-- byersoyblog - Giriş denemesi kısıtlaması (kaba kuvvet koruması)
-- 001-003 çalıştırıldıktan SONRA bu dosyayı çalıştırın.

SET NAMES utf8mb4;

-- --------------------------------------------------------
-- login_attempts: /yonetim/giris için başarısız deneme kaydı (IP başına)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_login_attempts_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
