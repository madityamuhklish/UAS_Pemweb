-- Tabel ini dibuat OTOMATIS oleh config/DbSessionHandler.php saat aplikasi
-- pertama kali jalan. File ini hanya dokumentasi/cadangan kalau ingin
-- membuatnya manual lewat phpMyAdmin.

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    data TEXT,
    last_activity INT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
