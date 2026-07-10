<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../config/database.php";
require_once "../config/helpers.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['admin_login_error'] = "Email atau password salah.";
    header("Location: login.php");
    exit;
}

if ($user['role'] !== 'admin') {
    $_SESSION['admin_login_error'] = "Akun ini tidak memiliki akses admin.";
    header("Location: login.php");
    exit;
}

if ($user['status'] !== 'active') {
    $_SESSION['admin_login_error'] = "Akun admin ini sedang dinonaktifkan.";
    header("Location: login.php");
    exit;
}

/* Separate session namespace from the regular user login (user_id, fullname, ...) */
$_SESSION['admin_id'] = $user['id'];
$_SESSION['admin_name'] = $user['fullname'];
$_SESSION['admin_email'] = $user['email'];
$_SESSION['is_admin'] = true;

logActivity($conn, $user['id'], "Admin login ke panel administrasi");

header("Location: index.php");
exit;
