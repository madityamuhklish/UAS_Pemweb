<?php
require_once "inc/auth.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

checkAdminLogin();

$db = new Database();
$conn = $db->connect();

$search = trim($_GET['q'] ?? '');
$userFilter = $_GET['user_id'] ?? '';
$dateFrom = $_GET['from'] ?? '';
$dateTo = $_GET['to'] ?? '';

$sql = "
    SELECT a.id, a.activity, a.created_at, u.id AS uid, u.fullname, u.email
    FROM activity_logs a
    LEFT JOIN users u ON u.id = a.user_id
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (a.activity LIKE ? OR u.fullname LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($userFilter !== '') {
    $sql .= " AND u.id = ?";
    $params[] = $userFilter;
}
if ($dateFrom !== '') {
    $sql .= " AND DATE(a.created_at) >= ?";
    $params[] = $dateFrom;
}
if ($dateTo !== '') {
    $sql .= " AND DATE(a.created_at) <= ?";
    $params[] = $dateTo;
}

$sql .= " ORDER BY a.created_at DESC LIMIT 200";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

$users = $conn->query("SELECT id, fullname FROM users ORDER BY fullname")->fetchAll();

$todayCount = $conn->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$weekCount = $conn->query("SELECT COUNT(*) FROM activity_logs WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
$totalCount = $conn->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();

$pageTitle = "Activity Log";
include "templates/header.php";
include "templates/sidebar.php";
include "templates/navbar.php";
?>

<div class="admin-content">

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-1">
        <div>
            <div class="admin-page-title">Activity Log</div>
            <p class="admin-page-sub mb-0">Riwayat aktivitas seluruh pengguna di platform.</p>
        </div>
        <a href="export.php?type=activity&<?= http_build_query($_GET) ?>" class="btn btn-sm admin-btn-primary">
            <i class="fa-solid fa-file-arrow-down me-1"></i> Export CSV
        </a>
    </div>

    <?php renderFlash(); ?>

    <div class="row g-4 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="admin-stat">
                <div>
                    <h6>Aktivitas Hari Ini</h6>
                    <h2><?= number_format($todayCount) ?></h2>
                </div>
                <div class="stat-icon icon-pill-primary"><i class="fa-solid fa-bolt"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="admin-stat">
                <div>
                    <h6>7 Hari Terakhir</h6>
                    <h2><?= number_format($weekCount) ?></h2>
                </div>
                <div class="stat-icon icon-pill-warning"><i class="fa-solid fa-calendar-week"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="admin-stat">
                <div>
                    <h6>Total Sepanjang Waktu</h6>
                    <h2><?= number_format($totalCount) ?></h2>
                </div>
                <div class="stat-icon icon-pill-success"><i class="fa-solid fa-database"></i></div>
            </div>
        </div>
    </div>

    <div class="admin-card mb-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label small text-a-muted">Cari aktivitas / nama</label>
                <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="mis. menambahkan subscription">
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label small text-a-muted">Pengguna</label>
                <select name="user_id" class="form-select">
                    <option value="">Semua pengguna</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $userFilter == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label small text-a-muted">Dari</label>
                <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label small text-a-muted">Sampai</label>
                <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
            </div>
            <div class="col-lg-1 col-md-6 d-grid">
                <button type="submit" class="btn admin-btn-primary"><i class="fa-solid fa-filter"></i></button>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <?php if (count($logs) > 0): ?>
        <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Pengguna</th>
                    <th>Aktivitas</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($log['fullname'] ?? 'Pengguna dihapus') ?>
                        <?php if ($log['email']): ?>
                        <br><small class="text-a-muted"><?= htmlspecialchars($log['email']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($log['activity']) ?></td>
                    <td><small class="text-a-muted"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></small></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
            <div class="admin-empty"><i class="fa-solid fa-clock-rotate-left"></i>Tidak ada aktivitas yang cocok dengan filter ini.</div>
        <?php endif; ?>
    </div>

</div>

<?php include "templates/footer.php"; ?>
