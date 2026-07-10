<?php
require_once "inc/auth.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

checkAdminLogin();

$db = new Database();
$conn = $db->connect();

$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

if ($action === 'delete' && $id > 0) {
    $stmt = $conn->prepare("SELECT service_name FROM subscriptions WHERE id = ?");
    $stmt->execute([$id]);
    $s = $stmt->fetch();

    if ($s) {
        $del = $conn->prepare("DELETE FROM subscriptions WHERE id = ?");
        $del->execute([$id]);
        flash('success', 'Subscription "' . $s['service_name'] . '" berhasil dihapus.');
    }
}

header("Location: subscriptions.php");
exit;
