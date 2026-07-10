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

    $subscription_id = input('subscription_id');
    $reminder_date = input('reminder_date');
    $reminder_type = input('reminder_type');

    $stmt = $conn->prepare("
        INSERT INTO reminders (subscription_id, reminder_date, reminder_type)
        VALUES (?,?,?)
    ");
    $stmt->execute([$subscription_id, $reminder_date, $reminder_type]);

    logActivity($conn, $userId, "Menambahkan reminder baru");
    flash('success', 'Reminder berhasil ditambahkan.');

} elseif ($action === 'toggle_sent') {

    $id = input('id');
    $isSent = input('is_sent') === '1' ? 1 : 0;

    $stmt = $conn->prepare("
        UPDATE reminders r
        JOIN subscriptions s ON s.id = r.subscription_id
        SET r.is_sent = ?
        WHERE r.id = ? AND s.user_id = ?
    ");
    $stmt->execute([$isSent, $id, $userId]);

    flash('success', 'Status reminder diperbarui.');

} elseif ($action === 'delete') {

    $id = input('id');

    $stmt = $conn->prepare("
        DELETE r FROM reminders r
        JOIN subscriptions s ON s.id = r.subscription_id
        WHERE r.id = ? AND s.user_id = ?
    ");
    $stmt->execute([$id, $userId]);

    flash('success', 'Reminder berhasil dihapus.');
}

header("Location: index.php");
exit;
