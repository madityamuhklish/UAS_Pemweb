<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

$db = new Database();
$conn = $db->connect();

checkLogin($conn);
$userId = $_SESSION['user_id'];

$action = $_POST['action'] ?? '';

if ($action === 'add' || $action === 'edit') {

    $service_name = trim(input('service_name'));
    $category_id = !empty(input('category_id')) ? input('category_id') : null;
    $payment_method_id = !empty(input('payment_method_id')) ? input('payment_method_id') : null;
    $amount = str_replace(['.', ','], '', input('amount'));
    $billing_cycle = input('billing_cycle');
    $start_date = input('start_date');
    $next_payment = input('next_payment');
    $auto_renew = isset($_POST['auto_renew']) ? 1 : 0;
    $status = input('status');
    $note = trim(input('note') ?? '');

    if ($service_name === '' || $amount === '' || $start_date === '' || $next_payment === '') {
        flash('error', 'Semua field wajib diisi dengan benar.');
        header("Location: index.php");
        exit;
    }

    if ($action === 'add') {

        $stmt = $conn->prepare("
            INSERT INTO subscriptions
                (user_id, category_id, payment_method_id, service_name, amount, billing_cycle, start_date, next_payment, auto_renew, status, note)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([$userId, $category_id, $payment_method_id, $service_name, $amount, $billing_cycle, $start_date, $next_payment, $auto_renew, $status, $note]);

        logActivity($conn, $userId, "Menambahkan subscription " . $service_name);
        flash('success', 'Subscription "' . $service_name . '" berhasil ditambahkan.');

    } else {

        $id = input('id');

        $stmt = $conn->prepare("
            UPDATE subscriptions
            SET category_id=?, payment_method_id=?, service_name=?, amount=?, billing_cycle=?, start_date=?, next_payment=?, auto_renew=?, status=?, note=?
            WHERE id=? AND user_id=?
        ");
        $stmt->execute([$category_id, $payment_method_id, $service_name, $amount, $billing_cycle, $start_date, $next_payment, $auto_renew, $status, $note, $id, $userId]);

        logActivity($conn, $userId, "Mengubah data " . $service_name);
        flash('success', 'Subscription "' . $service_name . '" berhasil diperbarui.');
    }

} elseif ($action === 'delete') {

    $id = input('id');

    $stmt = $conn->prepare("SELECT service_name FROM subscriptions WHERE id=? AND user_id=?");
    $stmt->execute([$id, $userId]);
    $sub = $stmt->fetch();

    $stmt = $conn->prepare("DELETE FROM subscriptions WHERE id=? AND user_id=?");
    $stmt->execute([$id, $userId]);

    if ($sub) {
        logActivity($conn, $userId, "Menghapus subscription " . $sub['service_name']);
    }
    flash('success', 'Subscription berhasil dihapus.');

} elseif ($action === 'toggle_status') {

    $id = input('id');
    $newStatus = input('new_status');

    $stmt = $conn->prepare("UPDATE subscriptions SET status=? WHERE id=? AND user_id=?");
    $stmt->execute([$newStatus, $id, $userId]);

    flash('success', 'Status subscription diperbarui menjadi ' . $newStatus . '.');
}

header("Location: index.php");
exit;
