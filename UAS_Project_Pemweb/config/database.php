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
    private $host = "";
    private $port = 5432;
    private $dbname = "";
    private $username = "";
    private $password = "";

    public function __construct()
    {
        // Vercel Storage Marketplace (Neon Postgres) memberi SATU environment
        // variable berbentuk connection string, contoh:
        //   postgres://user:pass@host/dbname?sslmode=require
        // Kalau itu ada, kita parse otomatis. Kalau tidak ada, fallback ke
        // variabel terpisah (DB_HOST, DB_NAME, dst).
        $connString = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL') ?: getenv('NEON_DATABASE_URL');

        if ($connString) {
            $parts = parse_url($connString);
            $this->host     = $parts['host'] ?? 'localhost';
            $this->port     = $parts['port'] ?? 5432;
            $this->dbname   = isset($parts['path']) ? ltrim($parts['path'], '/') : 'subspilot';
            $this->username = $parts['user'] ?? 'postgres';
            $this->password = isset($parts['pass']) ? urldecode($parts['pass']) : '';
        } else {
            $this->host     = getenv('DB_HOST') ?: "localhost";
            $this->port     = getenv('DB_PORT') ?: 5432;
            $this->dbname   = getenv('DB_NAME') ?: "subspilot";
            $this->username = getenv('DB_USER') ?: "postgres";
            $this->password = getenv('DB_PASS') ?: "";
        }
    }

    public $conn;

    public function connect()
    {
        $this->conn = null;

        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname};sslmode=require";

            // Workaround untuk Neon: versi libpq yang dipakai runtime PHP di
            // Vercel kadang belum mendukung SNI, sehingga Neon tidak tahu
            // "endpoint" mana yang dituju hanya dari hostname. Solusinya,
            // sisipkan endpoint ID (bagian pertama dari hostname) secara
            // eksplisit lewat parameter "options".
            if (str_contains($this->host, '.neon.tech')) {
                $endpointId = explode('.', $this->host)[0];
                $dsn .= ";options=endpoint={$endpointId}";
            }

            $this->conn = new PDO($dsn, $this->username, $this->password);

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
