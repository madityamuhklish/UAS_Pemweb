<?php
require_once "inc/auth.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

checkAdminLogin();

$db = new Database();
$conn = $db->connect();

/* Global stats */
$totalUsers = $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalAdmins = $conn->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$totalSubs = $conn->query("SELECT COUNT(*) FROM subscriptions")->fetchColumn();
$totalActiveSubs = $conn->query("SELECT COUNT(*) FROM subscriptions WHERE status='Active'")->fetchColumn();
$totalRevenue = $conn->query("SELECT IFNULL(SUM(amount),0) FROM subscriptions WHERE status='Active'")->fetchColumn();
$newUsersThisMonth = $conn->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn();

/* Subscriptions per category (global) */
$stmt = $conn->query("
    SELECT c.category_name, COUNT(s.id) AS total
    FROM categories c
    LEFT JOIN subscriptions s ON s.category_id = c.id
    GROUP BY c.id, c.category_name
    ORDER BY total DESC
");
$categoryDist = $stmt->fetchAll();

/* Recently registered users */
$stmt = $conn->query("SELECT id, fullname, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 6");
$recentUsers = $stmt->fetchAll();

/* Recent activity across whole platform */
$stmt = $conn->query("
    SELECT a.activity, a.created_at, u.fullname
    FROM activity_logs a
    LEFT JOIN users u ON u.id = a.user_id
    ORDER BY a.created_at DESC
    LIMIT 8
");
$recentActivity = $stmt->fetchAll();

$needsChart = true;
$pageTitle = "Dashboard";
include "templates/header.php";
include "templates/sidebar.php";
include "templates/navbar.php";
?>

<div class="admin-content">

    <div class="admin-page-title">Ringkasan Sistem</div>
    <p class="admin-page-sub">Pantau seluruh aktivitas pengguna dan subscription SubsPilot dari satu tempat.</p>

    <?php renderFlash(); ?>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="admin-stat">
                <div>
                    <h6>Total Pengguna</h6>
                    <h2><?= number_format($totalUsers) ?></h2>
                </div>
                <div class="stat-icon icon-pill-primary"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="admin-stat">
                <div>
                    <h6>Subscription Aktif</h6>
                    <h2><?= number_format($totalActiveSubs) ?></h2>
                </div>
                <div class="stat-icon icon-pill-success"><i class="fa-solid fa-credit-card"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="admin-stat">
                <div>
                    <h6>Total Revenue Aktif / bulan</h6>
                    <h2 style="font-size:20px;">Rp <?= number_format($totalRevenue, 0, ',', '.') ?></h2>
                </div>
                <div class="stat-icon icon-pill-warning"><i class="fa-solid fa-sack-dollar"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="admin-stat">
                <div>
                    <h6>Pengguna Baru Bulan Ini</h6>
                    <h2><?= number_format($newUsersThisMonth) ?></h2>
                </div>
                <div class="stat-icon icon-pill-danger"><i class="fa-solid fa-user-plus"></i></div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-lg-6">
            <div class="admin-card h-100">
                <h5 class="text-a-white-strong mb-1"><i class="fa-solid fa-chart-pie me-2 text-a-primary"></i>Distribusi Kategori</h5>
                <p class="admin-page-sub mb-3" style="margin-bottom:14px;">Jumlah subscription per kategori di seluruh sistem</p>
                <?php if (count($categoryDist) > 0): ?>
                    <canvas id="adminCategoryChart" height="230"></canvas>
                <?php else: ?>
                    <div class="admin-empty"><i class="fa-solid fa-chart-pie"></i>Belum ada data.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="admin-card h-100">
                <h5 class="text-a-white-strong mb-3"><i class="fa-solid fa-user-clock me-2 text-a-primary"></i>Pengguna Terbaru</h5>
                <?php if (count($recentUsers) > 0): ?>
                <div class="table-responsive">
<table class="admin-table">
                    <thead><tr><th>Nama</th><th>Role</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentUsers as $u): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($u['fullname']) ?>
                                <br><small class="text-a-muted"><?= htmlspecialchars($u['email']) ?></small>
                            </td>
                            <td><span class="admin-badge <?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                            <td><span class="admin-badge <?= $u['status'] === 'active' ? 'active' : 'inactive' ?>"><?= ucfirst($u['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
</div>
                <?php else: ?>
                    <div class="admin-empty"><i class="fa-solid fa-user"></i>Belum ada pengguna.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12">
            <div class="admin-card">
                <h5 class="text-a-white-strong mb-3"><i class="fa-solid fa-clock-rotate-left me-2 text-a-primary"></i>Aktivitas Terbaru Platform</h5>
                <?php if (count($recentActivity) > 0): ?>
                <div class="table-responsive">
<table class="admin-table">
                    <thead><tr><th>Pengguna</th><th>Aktivitas</th><th>Waktu</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentActivity as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['fullname'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($a['activity']) ?></td>
                            <td><small class="text-a-muted"><?= $a['created_at'] ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
</div>
                <?php else: ?>
                    <div class="admin-empty"><i class="fa-solid fa-list"></i>Belum ada aktivitas.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<?php if (count($categoryDist) > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('adminCategoryChart');
    if (!ctx) return;

    const style = getComputedStyle(document.body);
    const mutedColor = style.getPropertyValue('--a-muted').trim() || '#8B98B0';
    const gridColor = style.getPropertyValue('--a-border').trim() || '#233047';
    const primaryColor = style.getPropertyValue('--a-primary').trim() || '#818CF8';

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($categoryDist, 'category_name')) ?>,
            datasets: [{
                label: 'Jumlah Subscription',
                data: <?= json_encode(array_map('intval', array_column($categoryDist, 'total'))) ?>,
                backgroundColor: primaryColor,
                borderRadius: 8
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: mutedColor }, grid: { color: gridColor } },
                y: { ticks: { color: mutedColor }, grid: { color: gridColor }, beginAtZero: true }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php include "templates/footer.php"; ?>
