-- =====================================================================
-- Tabel untuk menyimpan session PHP secara terpusat di database.
-- WAJIB dijalankan kalau kamu deploy ke Vercel (atau hosting serverless
-- lain), karena session file biasa tidak bisa dipakai bersama antar
-- serverless function. Tidak diperlukan untuk hosting biasa (XAMPP,
-- InfinityFree, cPanel).
-- =====================================================================
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(128) NOT NULL,
  `data` text NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
