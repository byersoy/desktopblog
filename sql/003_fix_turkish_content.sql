-- byersoyblog - Türkçe karakter düzeltmesi
-- Panelden Türkçe karaktersiz (ASCII) girilmiş "site açıklaması" ayarını
-- düzeltir. Sadece tam eşleşen eski değeri hedefler, bu yüzden güncel/doğru
-- veri üzerinde çalıştırmak zararsızdır (eşleşme olmazsa hiçbir satır
-- değişmez). Yeni bir kurulumda gerekmez (002_seed.sql zaten düzeltilmiş
-- metinle gelir).

SET NAMES utf8mb4;

UPDATE settings SET `value` = 'Yazılım, tasarım ve teknoloji üzerine notlar.'
WHERE `key` = 'site_description' AND `value` = 'Yazilim, tasarim ve teknoloji uzerine notlar.';
