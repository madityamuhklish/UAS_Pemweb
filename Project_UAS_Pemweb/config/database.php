<?php

/**
 * =====================================================================
 * PENGATURAN KONEKSI DATABASE
 * =====================================================================
 * Mendukung 2 mode:
 *
 * 1) LOKAL (XAMPP) - tidak perlu apa-apa, otomatis pakai nilai default
 *    di bawah (localhost / root / tanpa password).
 *
 * 2) VERCEL / HOSTING LAIN - WAJIB diisi lewat Environment Variables,
 *    JANGAN edit nilai di bawah ini langsung. Set di dashboard hosting:
 *
 *      DB_HOST      -> contoh: containers-us-west-1.railway.app
 *      DB_NAME      -> contoh: subspilot
 *      DB_USER      -> contoh: root
 *      DB_PASS      -> password database kamu
 *      DB_PORT      -> contoh: 3306 (opsional, default 3306)
 *
 *    Karena Vercel serverless TIDAK punya MySQL sendiri (tidak seperti
 *    cPanel/InfinityFree), database harus pakai MySQL cloud eksternal,
 *    misalnya: Railway, Aiven, Clever Cloud, atau PlanetScale.
 * =====================================================================
 */
class Database
{
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $port;

    public $conn;

    public function __construct()
    {
        $this->host     = getenv('DB_HOST') ?: "localhost";
        $this->dbname   = getenv('DB_NAME') ?: "subspilot";
        $this->username = getenv('DB_USER') ?: "root";
        $this->password = getenv('DB_PASS') ?: "";
        $this->port     = getenv('DB_PORT') ?: "3306";
    }

    public function connect()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Jangan tampilkan detail error mentah di production (bisa membocorkan
            // info sensitif). Log ke file, tampilkan pesan yang jelas ke pengguna.
            error_log("Koneksi database gagal: " . $e->getMessage());
            http_response_code(500);
            die("Koneksi Database Gagal. Jika ini di Vercel, periksa Environment Variables DB_HOST/DB_NAME/DB_USER/DB_PASS/DB_PORT di dashboard Vercel.");
        }

        return $this->conn;
    }
}
