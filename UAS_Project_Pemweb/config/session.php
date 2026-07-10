<?php

/**
 * Don't show raw PHP warnings/errors/notices in the browser (this is what breaks
 * the page layout with green "Warning: ..." text). Log them to a file instead so
 * they can still be debugged, but the user never sees broken HTML.
 */
ini_set('display_errors', '0');
ini_set('log_errors', '1');
// Di Vercel, filesystem project bersifat read-only. Hanya folder /tmp yang
// bisa ditulis. Kalau folder /tmp tidak ada (misal di localhost lama), fallback
// ke folder project seperti biasa.
$logDir = is_dir('/tmp') && is_writable('/tmp') ? '/tmp' : __DIR__ . '/..';
ini_set('error_log', $logDir . '/php_errors.log');
error_reporting(E_ALL);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/DbSessionHandler.php';

/**
 * Session default PHP disimpan sebagai file di server. Ini TIDAK cocok untuk
 * Vercel (serverless): tiap request bisa dieksekusi di instance server yang
 * berbeda-beda, sehingga file session dari request sebelumnya tidak ketemu
 * lagi -> user selalu "kelempar" balik ke halaman login walau sudah login.
 *
 * Solusinya: simpan session di database (tabel `sessions`) supaya konsisten
 * di request manapun. Ini otomatis aktif kalau koneksi DB berhasil; kalau
 * gagal, fallback diam-diam ke session file bawaan PHP (misal saat di XAMPP).
 */
if (session_status() === PHP_SESSION_NONE) {
    try {
        $db = new Database();
        $conn = $db->connect();
        $handler = new DbSessionHandler($conn);
        session_set_save_handler($handler, true);
    } catch (Throwable $e) {
        error_log("DB session handler gagal diaktifkan, fallback ke file session: " . $e->getMessage());
    }

    session_start();
}

/**
 * Checks that the user is logged in. If a PDO connection is passed, it also
 * verifies the account still exists in the database (e.g. it was deleted,
 * or the DB was reset while an old session cookie was still around) and, if
 * not, cleanly logs the session out instead of letting every page below crash
 * with "Trying to access array offset on value of type bool".
 */
function checkLogin($conn = null)
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit;
    }

    if ($conn) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            session_unset();
            session_destroy();
            header("Location: ../auth/login.php?expired=1");
            exit;
        }
    }
}