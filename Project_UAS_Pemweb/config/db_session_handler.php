<?php

/**
 * =====================================================================
 * SESSION HANDLER BERBASIS DATABASE (khusus untuk Vercel)
 * =====================================================================
 * Di Vercel, setiap file .php berjalan sebagai serverless function yang
 * TERPISAH. Session PHP default (disimpan sebagai file di server) tidak
 * bisa dipakai bersama antar function berbeda, sehingga user akan selalu
 * "ter-logout" saat pindah halaman.
 *
 * Solusinya: simpan data session di tabel MySQL supaya semua function
 * bisa membaca/menulis session yang sama.
 *
 * Handler ini HANYA aktif kalau environment variable DB_HOST di-set
 * (artinya berjalan di Vercel/hosting eksternal). Kalau di lokal
 * (XAMPP), session tetap pakai mekanisme file default PHP seperti biasa.
 * =====================================================================
 */
class DbSessionHandler implements SessionHandlerInterface
{
    private $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string|false
    {
        $stmt = $this->conn->prepare("SELECT data FROM sessions WHERE id = ? AND expires_at > NOW()");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $row['data'] : "";
    }

    public function write($id, $data): bool
    {
        $expires = date('Y-m-d H:i:s', time() + 1440); // 24 menit, sama seperti default PHP
        $stmt = $this->conn->prepare(
            "INSERT INTO sessions (id, data, expires_at) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE data = VALUES(data), expires_at = VALUES(expires_at)"
        );
        return $stmt->execute([$id, $data, $expires]);
    }

    public function destroy($id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function gc($max_lifetime): int|false
    {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE expires_at < NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
