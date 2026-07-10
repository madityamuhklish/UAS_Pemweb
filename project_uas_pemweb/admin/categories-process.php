<?php
require_once "inc/auth.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

checkAdminLogin();

$db = new Database();
$conn = $db->connect();

$action = $_POST['action'] ?? '';

if ($action === 'add') {

    $name = trim($_POST['category_name'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($name === '') {
        flash('error', 'Nama kategori wajib diisi.');
        header("Location: categories.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
    $stmt->execute([$name, $desc]);
    flash('success', 'Kategori "' . $name . '" berhasil ditambahkan.');

} elseif ($action === 'edit') {

    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['category_name'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    $stmt = $conn->prepare("UPDATE categories SET category_name=?, description=? WHERE id=?");
    $stmt->execute([$name, $desc, $id]);
    flash('success', 'Kategori berhasil diperbarui.');

} elseif ($action === 'delete') {

    $id = (int) ($_POST['id'] ?? 0);

    $check = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE category_id = ?");
    $check->execute([$id]);

    if ($check->fetchColumn() > 0) {
        flash('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh subscription pengguna.');
        header("Location: categories.php");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->execute([$id]);
    flash('success', 'Kategori berhasil dihapus.');
}

header("Location: categories.php");
exit;
