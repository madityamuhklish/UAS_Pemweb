<?php
require_once "inc/auth.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

checkAdminLogin();

$db = new Database();
$conn = $db->connect();

$action = $_POST['action'] ?? '';

if ($action === 'add_category') {

    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? '') ?: 'fa-circle-question';
    $order = (int) ($_POST['sort_order'] ?? 0);

    if ($name === '') {
        flash('error', 'Nama kategori wajib diisi.');
        header("Location: faqs.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO faq_categories (name, icon, sort_order) VALUES (?, ?, ?)");
    $stmt->execute([$name, $icon, $order]);
    flash('success', 'Kategori "' . $name . '" berhasil ditambahkan.');

} elseif ($action === 'edit_category') {

    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? '') ?: 'fa-circle-question';
    $order = (int) ($_POST['sort_order'] ?? 0);

    $stmt = $conn->prepare("UPDATE faq_categories SET name=?, icon=?, sort_order=? WHERE id=?");
    $stmt->execute([$name, $icon, $order, $id]);
    flash('success', 'Kategori berhasil diperbarui.');

} elseif ($action === 'delete_category') {

    $id = (int) ($_POST['id'] ?? 0);

    $stmt = $conn->prepare("DELETE FROM faq_categories WHERE id=?");
    $stmt->execute([$id]);
    flash('success', 'Kategori beserta FAQ di dalamnya berhasil dihapus.');

} elseif ($action === 'add_faq') {

    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $keywords = trim($_POST['keywords'] ?? '');
    $order = (int) ($_POST['sort_order'] ?? 0);

    if ($question === '' || $answer === '' || $categoryId === 0) {
        flash('error', 'Kategori, pertanyaan, dan jawaban wajib diisi.');
        header("Location: faqs.php");
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO faqs (category_id, question, answer, keywords, sort_order)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$categoryId, $question, $answer, $keywords, $order]);
    flash('success', 'FAQ berhasil ditambahkan.');

} elseif ($action === 'edit_faq') {

    $id = (int) ($_POST['id'] ?? 0);
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $keywords = trim($_POST['keywords'] ?? '');
    $order = (int) ($_POST['sort_order'] ?? 0);

    $stmt = $conn->prepare("
        UPDATE faqs SET category_id=?, question=?, answer=?, keywords=?, sort_order=?
        WHERE id=?
    ");
    $stmt->execute([$categoryId, $question, $answer, $keywords, $order, $id]);
    flash('success', 'FAQ berhasil diperbarui.');

} elseif ($action === 'toggle_faq') {

    $id = (int) ($_POST['id'] ?? 0);
    $isActive = ($_POST['is_active'] ?? '0') === '1' ? 1 : 0;

    $stmt = $conn->prepare("UPDATE faqs SET is_active=? WHERE id=?");
    $stmt->execute([$isActive, $id]);
    flash('success', 'Status FAQ diperbarui.');

} elseif ($action === 'delete_faq') {

    $id = (int) ($_POST['id'] ?? 0);

    $stmt = $conn->prepare("DELETE FROM faqs WHERE id=?");
    $stmt->execute([$id]);
    flash('success', 'FAQ berhasil dihapus.');

} elseif ($action === 'update_settings') {

    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hours = trim($_POST['operational_hours'] ?? '');

    $exists = $conn->query("SELECT COUNT(*) FROM support_settings")->fetchColumn();

    if ($exists > 0) {
        $stmt = $conn->prepare("UPDATE support_settings SET whatsapp=?, email=?, operational_hours=? LIMIT 1");
        $stmt->execute([$whatsapp, $email, $hours]);
    } else {
        $stmt = $conn->prepare("INSERT INTO support_settings (whatsapp, email, operational_hours) VALUES (?, ?, ?)");
        $stmt->execute([$whatsapp, $email, $hours]);
    }

    flash('success', 'Kontak CS berhasil diperbarui.');

}

header("Location: faqs.php");
exit;
