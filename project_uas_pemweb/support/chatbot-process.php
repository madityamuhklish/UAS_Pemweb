<?php
/**
 * Endpoint AJAX untuk widget chatbot bantuan.
 *
 * Actions:
 *  - menu            -> daftar kategori (tombol pilihan awal)
 *  - category        -> daftar pertanyaan pada satu kategori
 *  - answer          -> jawaban satu FAQ berdasarkan id
 *  - ask             -> pencarian bebas (keyword matching) dari teks user
 *  - contact         -> info kontak CS
 */

require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

header("Content-Type: application/json");

// Selalu balas JSON, jangan sampai fatal error PHP membuat responsenya kosong/HTML
// (itulah yang membuat widget menampilkan "terjadi kendala jaringan" terus-menerus).
set_exception_handler(function ($e) {
    error_log("[chatbot-process] " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "message" => "Terjadi kesalahan pada server. Pastikan migrasi database/support_migration.sql sudah dijalankan.",
    ]);
    exit;
});

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "message" => "Sesi Anda telah berakhir. Silakan login kembali."]);
    exit;
}

$db = new Database();
$conn = $db->connect();

// Cek tabel pendukung FAQ sudah ada. Kalau belum, kasih pesan yang jelas
// alih-alih membiarkan query berikutnya melempar PDOException mentah.
try {
    $conn->query("SELECT 1 FROM faq_categories LIMIT 1");
    $conn->query("SELECT 1 FROM faqs LIMIT 1");
    $conn->query("SELECT 1 FROM support_settings LIMIT 1");
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "message" => "Tabel FAQ belum tersedia di database. Silakan import file database/support_migration.sql terlebih dahulu.",
    ]);
    exit;
}

$action = input('action', inputGet('action', ''));

function faqToOption($f) {
    return ["id" => (int) $f['id'], "label" => $f['question']];
}

if ($action === 'menu') {

    $cats = $conn->query("SELECT id, name, icon FROM faq_categories ORDER BY sort_order, name")->fetchAll();
    echo json_encode(["ok" => true, "categories" => $cats]);

} elseif ($action === 'category') {

    $catId = (int) input('category_id');
    $stmt = $conn->prepare("SELECT id, question FROM faqs WHERE category_id = ? AND is_active = 1 ORDER BY sort_order, id");
    $stmt->execute([$catId]);
    $items = array_map('faqToOption', $stmt->fetchAll());
    echo json_encode(["ok" => true, "questions" => $items]);

} elseif ($action === 'answer') {

    $id = (int) input('faq_id');
    $stmt = $conn->prepare("SELECT question, answer FROM faqs WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $faq = $stmt->fetch();

    if ($faq) {
        echo json_encode(["ok" => true, "found" => true, "question" => $faq['question'], "answer" => $faq['answer']]);
    } else {
        echo json_encode(["ok" => true, "found" => false]);
    }

} elseif ($action === 'ask') {

    $text = trim((string) input('message'));

    if ($text === '') {
        echo json_encode(["ok" => true, "found" => false]);
        exit;
    }

    $textLower = mb_strtolower($text);

    $stmt = $conn->query("SELECT id, question, answer, keywords FROM faqs WHERE is_active = 1");
    $faqs = $stmt->fetchAll();

    $best = null;
    $bestScore = 0;

    foreach ($faqs as $f) {
        $score = 0;

        $questionLower = mb_strtolower($f['question']);
        if ($questionLower === $textLower) {
            $score += 100;
        } elseif (str_contains($questionLower, $textLower) || str_contains($textLower, $questionLower)) {
            $score += 20;
        }

        $keywords = array_filter(array_map('trim', explode(',', mb_strtolower((string) $f['keywords']))));
        foreach ($keywords as $kw) {
            if ($kw !== '' && str_contains($textLower, $kw)) {
                $score += 10;
            }
        }

        $questionWords = preg_split('/\s+/', $questionLower);
        foreach ($questionWords as $w) {
            if (mb_strlen($w) > 3 && str_contains($textLower, $w)) {
                $score += 2;
            }
        }

        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $f;
        }
    }

    if ($best && $bestScore >= 6) {
        echo json_encode([
            "ok" => true,
            "found" => true,
            "question" => $best['question'],
            "answer" => $best['answer'],
        ]);
    } else {
        echo json_encode(["ok" => true, "found" => false]);
    }

} elseif ($action === 'contact') {

    $stmt = $conn->query("SELECT whatsapp, email, operational_hours FROM support_settings LIMIT 1");
    $settings = $stmt->fetch() ?: [];
    echo json_encode(["ok" => true, "contact" => $settings]);

} else {

    http_response_code(400);
    echo json_encode(["ok" => false, "message" => "Aksi tidak dikenali."]);

}
