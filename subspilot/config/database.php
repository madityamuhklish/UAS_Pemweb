<?php

/**
 * =====================================================================
 * PENGATURAN KONEKSI DATABASE
 * =====================================================================
 * Nilai di bawah ini cocok untuk XAMPP/localhost. SAAT DIHOSTING (cPanel,
 * shared hosting, dsb), nilai-nilai ini WAJIB diganti sesuai kredensial
 * database yang diberikan oleh penyedia hosting, contoh:
 *
 *   private $host     = "localhost";          // biasanya tetap "localhost"
 *   private $dbname   = "cpaneluser_subspilot"; // nama DB sering diberi prefix
 *   private $username = "cpaneluser_dbuser";    // username DB sering diberi prefix
 *   private $password = "isi_password_database"; // TIDAK BOLEH kosong di hosting
 *
 * Kredensial ini biasanya bisa dilihat/dibuat di menu "MySQL Databases"
 * pada cPanel hosting Anda.
 * =====================================================================
 */
class Database
{
    private $host = "localhost";
    private $dbname = "subspilot";
    private $username = "root";
    private $password = "";

    public $conn;

    public function connect()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
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
            die("Koneksi Database Gagal. Periksa kredensial database di config/database.php sesuai data dari hosting Anda.");
        }

        return $this->conn;
    }
}
