<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

$db = new Database();
$conn = $db->connect();

checkLogin($conn);
$userId = $_SESSION['user_id'];

$action = $_POST['action'] ?? '';

if ($action === 'add_method') {

    $method = trim(input('method_name'));
    $provider = trim(input('provider'));

    if ($method === '') {
        flash('error', 'Nama metode wajib diisi.');
        header("Location: index.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO payment_methods (method_name, provider) VALUES (?, ?)");
    $stmt->execute([$method, $provider]);

    logActivity($conn, $userId, "Menambahkan metode pembayaran " . $method);
    flash('success', 'Metode pembayaran berhasil ditambahkan.');

} elseif ($action === 'edit_method') {

    $id = input('id');
    $method = trim(input('method_name'));
    $provider = trim(input('provider'));

    $stmt = $conn->prepare("UPDATE payment_methods SET method_name=?, provider=? WHERE id=?");
    $stmt->execute([$method, $provider, $id]);

    flash('success', 'Metode pembayaran berhasil diperbarui.');

} elseif ($action === 'delete_method') {

    $id = input('id');

    $check = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE payment_method_id = ?");
    $check->execute([$id]);

    if ($check->fetchColumn() > 0) {
        flash('error', 'Metode tidak bisa dihapus karena masih digunakan subscription.');
        header("Location: index.php");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM payment_methods WHERE id=?");
    $stmt->execute([$id]);

    flash('success', 'Metode pembayaran berhasil dihapus.');

} elseif ($action === 'add_history') {

    $subscription_id = input('subscription_id');
    $payment_date = input('payment_date');
    $amount = str_replace(['.', ','], '', input('amount'));
    $status = input('status');
    $note = trim(input('note') ?? '');

    $stmt = $conn->prepare("
        INSERT INTO payment_history (subscription_id, payment_date, amount, status, note)
        VALUES (?,?,?,?,?)
    ");
    $stmt->execute([$subscription_id, $payment_date, $amount, $status, $note]);

    logActivity($conn, $userId, "Mencatat pembayaran sebesar Rp " . number_format($amount, 0, ',', '.'));
    flash('success', 'Riwayat pembayaran berhasil dicatat.');

} elseif ($action === 'delete_history') {

    $id = input('id');
    $stmt = $conn->prepare("
        DELETE ph FROM payment_history ph
        JOIN subscriptions s ON s.id = ph.subscription_id
        WHERE ph.id = ? AND s.user_id = ?
    ");
    $stmt->execute([$id, $userId]);

    flash('success', 'Riwayat pembayaran dihapus.');
}

header("Location: index.php");
exit;
