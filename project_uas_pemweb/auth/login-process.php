<?php

require_once "../config/session.php";
require_once "../config/database.php";

$database = new Database();
$conn = $database->connect();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $sql = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $sql->execute([$email]);
    $user = $sql->fetch();

    if ($user && password_verify($password, $user['password'])) {

        if ($user['status'] !== 'active') {
            echo "<h2 style='color:red;text-align:center;margin-top:50px;'>Akun Anda sedang dinonaktifkan.</h2>";
            exit;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['photo'] = $user['photo'];
        $_SESSION['dark_mode'] = $user['dark_mode'];

        $log = $conn->prepare("INSERT INTO activity_logs(user_id, activity) VALUES(?, ?)");
        $log->execute([$user['id'], "Login ke sistem"]);

        header("Location: ../dashboard/index.php");
        exit;
    }

    echo "<h2 style='color:red;text-align:center;margin-top:50px;'>Email atau Password Salah</h2>";
}
