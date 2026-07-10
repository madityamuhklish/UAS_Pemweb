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
    $estimated_price = input('estimated_price') !== '' ? input('estimated_price') : 0;
    $priority = input('priority') ?? 'Medium';
    $note = trim(input('note') ?? '');

    if ($service_name === '') {
        flash('error', 'Nama layanan wajib diisi.');
        header("Location: index.php");
        exit;
    }

    if ($action === 'add') {
        $stmt = $conn->prepare("
            INSERT INTO wishlist (user_id, service_name, estimated_price, priority, note)
            VALUES (?,?,?,?,?)
        ");
        $stmt->execute([$userId, $service_name, $estimated_price, $priority, $note]);

        logActivity($conn, $userId, "Menambahkan \"$service_name\" ke wishlist");
        flash('success', 'Wishlist "' . $service_name . '" berhasil ditambahkan.');
    } else {
        $id = input('id');
        $stmt = $conn->prepare("
            UPDATE wishlist
            SET service_name=?, estimated_price=?, priority=?, note=?
            WHERE id=? AND user_id=?
        ");
        $stmt->execute([$service_name, $estimated_price, $priority, $note, $id, $userId]);

        flash('success', 'Wishlist berhasil diperbarui.');
    }

} elseif ($action === 'delete') {

    $id = input('id');

    $stmt = $conn->prepare("SELECT service_name FROM wishlist WHERE id=? AND user_id=?");
    $stmt->execute([$id, $userId]);
    $item = $stmt->fetch();

    $stmt = $conn->prepare("DELETE FROM wishlist WHERE id=? AND user_id=?");
    $stmt->execute([$id, $userId]);

    if ($item) {
        logActivity($conn, $userId, "Menghapus \"" . $item['service_name'] . "\" dari wishlist");
    }
    flash('success', 'Wishlist berhasil dihapus.');

} elseif ($action === 'convert') {

    $id = input('id');
    $amount = str_replace(['.', ','], '', input('amount'));
    $billing_cycle = input('billing_cycle');
    $start_date = input('start_date');
    $next_payment = input('next_payment');
    $category_id = !empty(input('category_id')) ? input('category_id') : null;

    $stmt = $conn->prepare("SELECT * FROM wishlist WHERE id=? AND user_id=?");
    $stmt->execute([$id, $userId]);
    $item = $stmt->fetch();

    if (!$item) {
        flash('error', 'Item wishlist tidak ditemukan.');
        header("Location: index.php");
        exit;
    }

    if ($amount === '' || $start_date === '' || $next_payment === '') {
        flash('error', 'Lengkapi semua field untuk membuat subscription.');
        header("Location: index.php");
        exit;
    }

    $conn->beginTransaction();
    try {
        $stmt = $conn->prepare("
            INSERT INTO subscriptions
                (user_id, category_id, service_name, amount, billing_cycle, start_date, next_payment, auto_renew, status)
            VALUES (?,?,?,?,?,?,?,1,'Active')
        ");
        $stmt->execute([$userId, $category_id, $item['service_name'], $amount, $billing_cycle, $start_date, $next_payment]);

        $stmt = $conn->prepare("DELETE FROM wishlist WHERE id=? AND user_id=?");
        $stmt->execute([$id, $userId]);

        $conn->commit();

        logActivity($conn, $userId, "Mengubah wishlist \"" . $item['service_name'] . "\" menjadi subscription");
        flash('success', '"' . $item['service_name'] . '" berhasil dijadikan subscription aktif!');
    } catch (Throwable $e) {
        $conn->rollBack();
        flash('error', 'Gagal memproses konversi. Coba lagi.');
    }
}

header("Location: index.php");
exit;
