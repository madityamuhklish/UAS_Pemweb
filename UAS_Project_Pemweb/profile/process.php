<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

$db = new Database();
$conn = $db->connect();

checkLogin($conn);
$userId = $_SESSION['user_id'];

$action = $_POST['action'] ?? '';

if ($action === 'update_profile') {

    $fullname = trim(input('fullname'));
    $email = trim(input('email'));

    if ($fullname === '' || $email === '') {
        flash('error', 'Nama dan email wajib diisi.');
        header("Location: index.php");
        exit;
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->execute([$email, $userId]);
    if ($check->rowCount() > 0) {
        flash('error', 'Email sudah digunakan oleh akun lain.');
        header("Location: index.php");
        exit;
    }

    $photoField = "";
    $params = [$fullname, $email];

    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $newName = 'user_' . $userId . '_' . time() . '.' . $ext;
            $target = '../assets/uploads/' . $newName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                $photoField = ", photo = ?";
                $params[] = $newName;
                $_SESSION['photo'] = $newName;
            }
        }
    }

    $params[] = $userId;

    $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?" . $photoField . " WHERE id = ?");
    $stmt->execute($params);

    $_SESSION['fullname'] = $fullname;

    logActivity($conn, $userId, "Memperbarui profil");
    flash('success', 'Profil berhasil diperbarui.');

} elseif ($action === 'change_password') {

    $current = input('current_password');
    $new = input('new_password');
    $confirm = input('confirm_password');

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, $user['password'])) {
        flash('error', 'Password saat ini salah.');
        header("Location: index.php");
        exit;
    }

    if (strlen($new) < 8) {
        flash('error', 'Password baru minimal 8 karakter.');
        header("Location: index.php");
        exit;
    }

    if ($new !== $confirm) {
        flash('error', 'Konfirmasi password tidak sama.');
        header("Location: index.php");
        exit;
    }

    $hash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $userId]);

    logActivity($conn, $userId, "Mengubah password");
    flash('success', 'Password berhasil diubah.');

} elseif ($action === 'toggle_dark_mode') {

    $mode = input('dark_mode') === '1' ? 1 : 0;
    $stmt = $conn->prepare("UPDATE users SET dark_mode = ? WHERE id = ?");
    $stmt->execute([$mode, $userId]);

    flash('success', 'Preferensi tampilan disimpan.');
}

header("Location: index.php");
exit;
