<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['admin_id']) && !empty($_SESSION['is_admin'])) {
    header("Location: index.php");
    exit;
}
$error = $_SESSION['admin_login_error'] ?? '';
unset($_SESSION['admin_login_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | SubsPilot</title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Shared admin design tokens + this page's styles now live in admin.css -->
    <link rel="stylesheet" href="../assets/css/admin.css?v=20260710a">
</head>
<body class="admin-login-body">

<span class="bg-icon i1"><i class="fa-solid fa-shield-halved"></i></span>
<span class="bg-icon i2"><i class="fa-solid fa-gears"></i></span>
<span class="bg-icon i3"><i class="fa-solid fa-lock"></i></span>
<span class="bg-icon i4"><i class="fa-solid fa-server"></i></span>
<span class="bg-icon i5"><i class="fa-solid fa-key"></i></span>
<div class="glow glow1"></div>
<div class="glow glow2"></div>

<div class="admin-login-card">

    <div class="admin-logo"><i class="fa-solid fa-user-shield"></i></div>
    <h2>Admin Panel</h2>
    <p class="subtitle">Akses khusus administrator SubsPilot</p>

    <div class="badge-wrap">
        <span class="badge-secure"><i class="fa-solid fa-lock"></i> Sistem login terpisah dari akun pengguna</span>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger border-0" style="border-radius:12px;font-size:14px;">
            <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="login-process.php" method="POST">
        <div class="mb-3">
            <label>Email Admin</label>
            <input type="email" name="email" class="form-control" placeholder="admin@subspilot.com" required autofocus>
        </div>
        <div class="mb-2">
            <label>Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button class="btn-admin-login">
            <i class="fa-solid fa-right-to-bracket me-2"></i>Masuk sebagai Admin
        </button>
    </form>

    <a href="../auth/login.php" class="back-user">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke login pengguna
    </a>

</div>

</body>
</html>
