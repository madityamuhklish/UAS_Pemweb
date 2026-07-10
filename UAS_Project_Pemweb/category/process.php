<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

$db = new Database();
$conn = $db->connect();

checkLogin($conn);
$userId = $_SESSION['user_id'];

$action = $_POST['action'] ?? '';

if ($action === 'add') {

    $name = trim(input('category_name'));
    $desc = trim(input('description') ?? '');

    if ($name === '') {
        flash('error', 'Nama kategori wajib diisi.');
        header("Location: index.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
    $stmt->execute([$name, $desc]);

    logActivity($conn, $userId, "Menambahkan kategori " . $name);
    flash('success', 'Kategori "' . $name . '" berhasil ditambahkan.');

} elseif ($action === 'edit') {

    $id = input('id');
    $name = trim(input('category_name'));
    $desc = trim(input('description') ?? '');

    $stmt = $conn->prepare("UPDATE categories SET category_name=?, description=? WHERE id=?");
    $stmt->execute([$name, $desc, $id]);

    logActivity($conn, $userId, "Mengubah kategori " . $name);
    flash('success', 'Kategori berhasil diperbarui.');

} elseif ($action === 'delete') {

    $id = input('id');

    $check = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE category_id = ?");
    $check->execute([$id]);

    if ($check->fetchColumn() > 0) {
        flash('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh subscription.');
        header("Location: index.php");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->execute([$id]);

    flash('success', 'Kategori berhasil dihapus.');
}

header("Location: index.php");
exit;
