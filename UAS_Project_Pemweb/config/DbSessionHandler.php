<?php

/**
 * Menyimpan session PHP di tabel database, bukan di file server.
 *
 * Kenapa perlu: di hosting serverless seperti Vercel, tiap request bisa
 * dijalankan di "instance" server yang berbeda dan tidak berbagi filesystem
 * yang sama. Session bawaan PHP (disimpan sebagai file di server) jadi tidak
 * bisa dibaca lagi oleh instance lain -> user yang baru saja login akan
 * dianggap belum login di request berikutnya.
 *
 * Dengan menyimpan session di database (yang sama-sama diakses oleh semua
 * instance), masalah ini hilang.
 */
class DbSessionHandler implements SessionHandlerInterface
{
    private $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
        $this->ensureTableExists();
    }

    private function ensureTableExists()
    {
        // Aman dipanggil berkali-kali (CREATE TABLE IF NOT EXISTS).
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS sessions (
                id VARCHAR(128) PRIMARY KEY,
                data TEXT,
                last_activity INTEGER NOT NULL
            )
        ");
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
        $stmt = $this->conn->prepare("SELECT data FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $row['data'] : "";
    }

    public function write($id, $data): bool
    {
        $stmt = $this->conn->prepare("
            INSERT INTO sessions (id, data, last_activity)
            VALUES (?, ?, ?)
            ON CONFLICT (id) DO UPDATE SET data = EXCLUDED.data, last_activity = EXCLUDED.last_activity
        ");
        return $stmt->execute([$id, $data, time()]);
    }

    public function destroy($id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function gc($max_lifetime): int|false
    {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE last_activity < ?");
        $stmt->execute([time() - $max_lifetime]);
        return $stmt->rowCount();
    }
}
