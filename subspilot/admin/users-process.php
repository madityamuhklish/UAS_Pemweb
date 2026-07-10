<?php
require_once "inc/auth.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

checkAdminLogin();

$db = new Database();
$conn = $db->connect();

$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);
$adminId = (int) $_SESSION['admin_id'];

if ($id === $adminId) {
    flash('error', 'Kamu tidak bisa melakukan aksi ini terhadap akunmu sendiri.');
    header("Location: users.php");
    exit;
}

if ($action === 'toggle_status') {

    $stmt = $conn->prepare("SELECT status, fullname FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $u = $stmt->fetch();

    if ($u) {
        $newStatus = $u['status'] === 'active' ? 'inactive' : 'active';
        $upd = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $upd->execute([$newStatus, $id]);
        flash('success', 'Status ' . $u['fullname'] . ' diubah menjadi ' . ucfirst($newStatus) . '.');
    }

} elseif ($action === 'toggle_role') {

    $stmt = $conn->prepare("SELECT role, fullname FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $u = $stmt->fetch();

    if ($u) {
        $newRole = $u['role'] === 'admin' ? 'user' : 'admin';
        $upd = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $upd->execute([$newRole, $id]);
        flash('success', 'Role ' . $u['fullname'] . ' diubah menjadi ' . ucfirst($newRole) . '.');
    }

} elseif ($action === 'delete') {

    $stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $u = $stmt->fetch();

    if ($u) {
        $del = $conn->prepare("DELETE FROM users WHERE id = ?");
        $del->execute([$id]);
        flash('success', 'Pengguna ' . $u['fullname'] . ' berhasil dihapus.');
    }
}

header("Location: users.php");
exit;
