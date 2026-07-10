<?php
require_once "inc/auth.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

checkAdminLogin();

$db = new Database();
$conn = $db->connect();

$stmt = $conn->query("
    SELECT u.*,
        (SELECT COUNT(*) FROM subscriptions s WHERE s.user_id = u.id) AS total_subs,
        (SELECT IFNULL(SUM(amount),0) FROM subscriptions s WHERE s.user_id = u.id AND s.status='Active') AS total_spend
    FROM users u
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

$pageTitle = "Kelola Pengguna";
include "templates/header.php";
include "templates/sidebar.php";
include "templates/navbar.php";
?>

<div class="admin-content">

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-1">
        <div>
            <div class="admin-page-title">Kelola Pengguna</div>
            <p class="admin-page-sub mb-0">Kelola akun, status, dan hak akses seluruh pengguna SubsPilot.</p>
        </div>
        <a href="export.php?type=users" class="btn btn-sm admin-btn-primary">
            <i class="fa-solid fa-file-arrow-down me-1"></i> Export CSV
        </a>
    </div>

    <?php renderFlash(); ?>

    <div class="admin-card">
        <?php if (count($users) > 0): ?>
        <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Pengguna</th>
                    <th>Role</th>
                    <th>Subscription</th>
                    <th>Pengeluaran Aktif</th>
                    <th>Status</th>
                    <th>Bergabung</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($u['fullname']) ?></strong>
                        <br><small style="color:var(--a-muted);"><?= htmlspecialchars($u['email']) ?></small>
                    </td>
                    <td><span class="admin-badge <?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= $u['total_subs'] ?></td>
                    <td>Rp <?= number_format($u['total_spend'], 0, ',', '.') ?></td>
                    <td><span class="admin-badge <?= $u['status'] === 'active' ? 'active' : 'inactive' ?>"><?= ucfirst($u['status']) ?></span></td>
                    <td><small style="color:var(--a-muted);"><?= date('d M Y', strtotime($u['created_at'])) ?></small></td>
                    <td>
                        <div class="d-flex gap-2">
                            <form action="users-process.php" method="POST">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button class="btn btn-sm admin-btn-outline"
                                    title="<?= $u['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan' ?>"
                                    <?= $u['id'] == ($_SESSION['admin_id'] ?? 0) ? 'disabled' : '' ?>>
                                    <i class="fa-solid <?= $u['status'] === 'active' ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                </button>
                            </form>
                            <form action="users-process.php" method="POST">
                                <input type="hidden" name="action" value="toggle_role">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button class="btn btn-sm admin-btn-outline"
                                    title="<?= $u['role'] === 'admin' ? 'Jadikan User' : 'Jadikan Admin' ?>"
                                    <?= $u['id'] == ($_SESSION['admin_id'] ?? 0) ? 'disabled' : '' ?>>
                                    <i class="fa-solid fa-user-shield"></i>
                                </button>
                            </form>
                            <form action="users-process.php" method="POST" class="confirm-delete"
                                data-confirm="Hapus pengguna <?= htmlspecialchars($u['fullname']) ?> beserta seluruh datanya?">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"
                                    <?= $u['id'] == ($_SESSION['admin_id'] ?? 0) ? 'disabled' : '' ?>>
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
            <div class="admin-empty"><i class="fa-solid fa-users"></i>Belum ada pengguna terdaftar.</div>
        <?php endif; ?>
    </div>

</div>

<?php include "templates/footer.php"; ?>
