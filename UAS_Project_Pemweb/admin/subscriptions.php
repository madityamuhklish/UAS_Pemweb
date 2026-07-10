<?php
require_once "inc/auth.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

checkAdminLogin();

$db = new Database();
$conn = $db->connect();

$filter = $_GET['status'] ?? '';

$sql = "
    SELECT s.*, u.fullname, u.email, c.category_name
    FROM subscriptions s
    JOIN users u ON u.id = s.user_id
    LEFT JOIN categories c ON c.id = s.category_id
";
$params = [];
if (in_array($filter, ['Active', 'Cancelled', 'Paused'])) {
    $sql .= " WHERE s.status = ?";
    $params[] = $filter;
}
$sql .= " ORDER BY s.next_payment ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$subs = $stmt->fetchAll();

$pageTitle = "Semua Subscription";
include "templates/header.php";
include "templates/sidebar.php";
include "templates/navbar.php";
?>

<div class="admin-content">

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-1">
        <div>
            <div class="admin-page-title">Semua Subscription</div>
            <p class="admin-page-sub mb-0">Pantau seluruh subscription milik semua pengguna.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="btn-group">
                <a href="subscriptions.php" class="btn btn-sm <?= $filter === '' ? 'admin-btn-primary' : 'admin-btn-outline' ?>">Semua</a>
                <a href="subscriptions.php?status=Active" class="btn btn-sm <?= $filter === 'Active' ? 'admin-btn-primary' : 'admin-btn-outline' ?>">Active</a>
                <a href="subscriptions.php?status=Paused" class="btn btn-sm <?= $filter === 'Paused' ? 'admin-btn-primary' : 'admin-btn-outline' ?>">Paused</a>
                <a href="subscriptions.php?status=Cancelled" class="btn btn-sm <?= $filter === 'Cancelled' ? 'admin-btn-primary' : 'admin-btn-outline' ?>">Cancelled</a>
            </div>
            <a href="export.php?type=subscriptions" class="btn btn-sm admin-btn-primary">
                <i class="fa-solid fa-file-arrow-down me-1"></i> Export CSV
            </a>
        </div>
    </div>

    <?php renderFlash(); ?>

    <div class="admin-card">
        <?php if (count($subs) > 0): ?>
        <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Layanan</th>
                    <th>Pengguna</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Siklus</th>
                    <th>Pembayaran Berikutnya</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($subs as $s): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($s['service_name']) ?></strong></td>
                    <td>
                        <?= htmlspecialchars($s['fullname']) ?>
                        <br><small style="color:var(--a-muted);"><?= htmlspecialchars($s['email']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($s['category_name'] ?? '-') ?></td>
                    <td>Rp <?= number_format($s['amount'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($s['billing_cycle']) ?></td>
                    <td><?= $s['next_payment'] ?></td>
                    <td>
                        <span class="admin-badge <?= $s['status'] === 'Active' ? 'active' : ($s['status'] === 'Cancelled' ? 'inactive' : 'user') ?>">
                            <?= htmlspecialchars($s['status']) ?>
                        </span>
                    </td>
                    <td>
                        <form action="subscriptions-process.php" method="POST" class="confirm-delete"
                            data-confirm="Hapus subscription <?= htmlspecialchars($s['service_name']) ?> milik <?= htmlspecialchars($s['fullname']) ?>?">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
            <div class="admin-empty"><i class="fa-solid fa-credit-card"></i>Tidak ada subscription untuk filter ini.</div>
        <?php endif; ?>
    </div>

</div>

<?php include "templates/footer.php"; ?>
