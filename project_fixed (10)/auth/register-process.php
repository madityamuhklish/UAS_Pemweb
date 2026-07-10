<?php

require_once "../config/session.php";
require_once "../config/database.php";

$database = new Database();
$conn = $database->connect();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($fullname === '' || $email === '' || $password === '' || $confirm === '') {
        die("Semua field wajib diisi.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Format email tidak valid.");
    }

    if ($password !== $confirm) {
        die("Password tidak sama.");
    }

    if (strlen($password) < 8) {
        die("Password minimal 8 karakter.");
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        die("Email sudah digunakan.");
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = $conn->prepare("INSERT INTO users(fullname, email, password) VALUES (?, ?, ?)");
    $sql->execute([$fullname, $email, $hash]);

    header("Location: login.php");
    exit;
}
