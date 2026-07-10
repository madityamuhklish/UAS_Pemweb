<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

$db = new Database();
$conn = $db->connect();

checkLogin($conn);
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$stmt = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE user_id = ?");
$stmt->execute([$userId]);
$totalSub = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT IFNULL(SUM(amount),0) FROM subscriptions WHERE user_id = ? AND status='Active'");
$stmt->execute([$userId]);
$totalSpend = $stmt->fetchColumn();

$pageTitle = "Profile";
include "../templates/header.php";
include "../templates/sidebar.php";
include "../templates/navbar.php";
?>

<div class="main-content">
<div class="container-fluid py-4">

    <div class="page-heading mb-4">
        <div class="page-icon"><i class="fa-solid fa-user"></i></div>
        <div>
            <h2 class="title mb-0">Profile</h2>
            <p class="subtitle mb-0">Kelola informasi akun kamu</p>
        </div>
    </div>

    <?php renderFlash(); ?>

    <div class="row">

        <div class="col-lg-4 mb-4">
            <div class="content-card p-4 text-center fade-up">
                <div class="avatar-upload mb-3">
                    <img src="../assets/uploads/<?= htmlspecialchars($user['photo']) ?>"
                         alt="Foto profil <?= htmlspecialchars($user['fullname']) ?>"
                         onerror="this.onerror=null;this.src='../assets/img/default.png'">
                </div>
                <h5 class="fw-bold mb-0"><?= htmlspecialchars($user['fullname']) ?></h5>
                <p class="text-muted mb-3"><?= htmlspecialchars($user['email']) ?></p>
                <span class="badge status-active"><?= ucfirst($user['role']) ?></span>

                <hr class="my-4">

                <div class="d-flex justify-content-around">
                    <div>
                        <h5 class="fw-bold mb-0" data-counter="<?= $totalSub ?>">0</h5>
                        <small class="text-muted">Subscription</small>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" data-counter="<?= $totalSpend ?>" data-money>Rp 0</h5>
                        <small class="text-muted">/ Bulan</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">

            <div class="content-card p-4 mb-4 fade-up d1">
                <h5 class="fw-bold mb-3">Informasi Akun</h5>
                <form action="process.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="mb-3">
                        <label class="form-label">Foto Profil</label>
                        <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <button class="btn btn-primary px-4">Simpan Perubahan</button>
                </form>
            </div>

            <div class="content-card p-4 fade-up d2">
                <h5 class="fw-bold mb-3">Ubah Password</h5>
                <form action="process.php" method="POST">
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="new_password" class="form-control" minlength="8" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                        </div>
                    </div>

                    <button class="btn btn-primary px-4">Ubah Password</button>
                </form>
            </div>

        </div>

    </div>

</div>
</div>

<?php include "../templates/footer.php"; ?>
