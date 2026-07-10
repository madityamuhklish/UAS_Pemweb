<?php
require_once "inc/auth.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

checkAdminLogin();

$db = new Database();
$conn = $db->connect();

$action = $_POST['action'] ?? '';

if ($action === 'add') {

    $name = trim($_POST['method_name'] ?? '');
    $provider = trim($_POST['provider'] ?? '');

    if ($name === '') {
        flash('error', 'Nama metode wajib diisi.');
        header("Location: payment-methods.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO payment_methods (method_name, provider) VALUES (?, ?)");
    $stmt->execute([$name, $provider]);
    flash('success', 'Metode pembayaran "' . $name . '" berhasil ditambahkan.');

} elseif ($action === 'edit') {

    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['method_name'] ?? '');
    $provider = trim($_POST['provider'] ?? '');

    $stmt = $conn->prepare("UPDATE payment_methods SET method_name=?, provider=? WHERE id=?");
    $stmt->execute([$name, $provider, $id]);
    flash('success', 'Metode pembayaran berhasil diperbarui.');

} elseif ($action === 'delete') {

    $id = (int) ($_POST['id'] ?? 0);

    $check = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE payment_method_id = ?");
    $check->execute([$id]);

    if ($check->fetchColumn() > 0) {
        flash('error', 'Metode pembayaran tidak bisa dihapus karena masih digunakan.');
        header("Location: payment-methods.php");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM payment_methods WHERE id=?");
    $stmt->execute([$id]);
    flash('success', 'Metode pembayaran berhasil dihapus.');
}

header("Location: payment-methods.php");
exit;
