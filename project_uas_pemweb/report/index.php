<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

$db = new Database();
$conn = $db->connect();

checkLogin($conn);
$userId = $_SESSION['user_id'];

/* Spending by category (active subs) */
$stmt = $conn->prepare("
    SELECT IFNULL(c.category_name,'Lainnya') AS category_name, SUM(s.amount) AS total
    FROM subscriptions s
    LEFT JOIN categories c ON c.id = s.category_id
    WHERE s.user_id = ? AND s.status = 'Active'
    GROUP BY c.category_name
    ORDER BY total DESC
");
$stmt->execute([$userId]);
$byCategory = $stmt->fetchAll();

/* Spending by status */
$stmt = $conn->prepare("
    SELECT status, COUNT(*) AS total
    FROM subscriptions
    WHERE user_id = ?
    GROUP BY status
");
$stmt->execute([$userId]);
$byStatus = $stmt->fetchAll();

/* Payment history last 6 months */
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(ph.payment_date, '%Y-%m') AS ym, SUM(ph.amount) AS total
    FROM payment_history ph
    JOIN subscriptions s ON s.id = ph.subscription_id
    WHERE s.user_id = ? AND ph.payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY ym
    ORDER BY ym ASC
");
$stmt->execute([$userId]);
$monthly = $stmt->fetchAll();

/* Summary */
$stmt = $conn->prepare("SELECT IFNULL(SUM(amount),0) FROM subscriptions WHERE user_id=? AND status='Active'");
$stmt->execute([$userId]);
$totalActiveSpend = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE user_id=?");
$stmt->execute([$userId]);
$totalAll = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT IFNULL(AVG(amount),0) FROM subscriptions WHERE user_id=? AND status='Active'");
$stmt->execute([$userId]);
$avgSpend = $stmt->fetchColumn();

/* Top service */
$stmt = $conn->prepare("SELECT service_name, amount FROM subscriptions WHERE user_id=? ORDER BY amount DESC LIMIT 1");
$stmt->execute([$userId]);
$topService = $stmt->fetch();

/* Normalized monthly projection (weekly/quarterly/yearly converted to a monthly equivalent) */
$stmt = $conn->prepare("
    SELECT billing_cycle, SUM(amount) AS total
    FROM subscriptions
    WHERE user_id = ? AND status = 'Active'
    GROUP BY billing_cycle
");
$stmt->execute([$userId]);
$cycleFactor = ['Weekly' => 4.345, 'Monthly' => 1, 'Quarterly' => 1/3, 'Yearly' => 1/12];
$monthlyProjection = 0;
foreach ($stmt->fetchAll() as $row) {
    $monthlyProjection += $row['total'] * ($cycleFactor[$row['billing_cycle']] ?? 1);
}

/* Upcoming renewals in the next 7 days */
$stmt = $conn->prepare("
    SELECT service_name, amount, next_payment, DATEDIFF(next_payment, CURDATE()) AS days_left
    FROM subscriptions
    WHERE user_id = ? AND status = 'Active'
      AND next_payment BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY next_payment ASC
");
$stmt->execute([$userId]);
$upcomingRenewals = $stmt->fetchAll();

$needsChart = true;
$pageTitle = "Reports";
include "../templates/header.php";
include "../templates/sidebar.php";
include "../templates/navbar.php";
?>

<div class="main-content">
<div class="container-fluid py-4">

    <div class="page-heading mb-4">
        <div class="page-icon"><i class="fa-solid fa-chart-column"></i></div>
        <div>
            <h2 class="title mb-0">Reports</h2>
            <p class="subtitle mb-0">Ringkasan pengeluaran subscription kamu</p>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-pill fade-up">
                <div class="stat-icon" style="background:#6366F1;"><i class="fa-solid fa-wallet"></i></div>
                <div>
                    <h6>Pengeluaran Aktif / Bulan</h6>
                    <h4 data-counter="<?= $totalActiveSpend ?>" data-money>Rp 0</h4>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-pill fade-up d1">
                <div class="stat-icon" style="background:#22C55E;"><i class="fa-solid fa-layer-group"></i></div>
                <div>
                    <h6>Total Subscription</h6>
                    <h4 data-counter="<?= $totalAll ?>">0</h4>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-pill fade-up d2">
                <div class="stat-icon" style="background:#F59E0B;"><i class="fa-solid fa-scale-balanced"></i></div>
                <div>
                    <h6>Rata-rata / Layanan</h6>
                    <h4 data-counter="<?= round($avgSpend) ?>" data-money>Rp 0</h4>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-pill fade-up d3">
                <div class="stat-icon" style="background:#EC4899;"><i class="fa-solid fa-crown"></i></div>
                <div>
                    <h6>Termahal</h6>
                    <h4 class="text-truncate" style="max-width:170px;"><?= $topService ? htmlspecialchars($topService['service_name']) : '-' ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="content-card p-4 fade-up d1">
                <h5 class="fw-bold mb-3">Tren Pembayaran (6 Bulan Terakhir)</h5>
                <div class="chart-wrap">
                    <canvas id="monthlyChart" height="230"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5 mb-4">
            <div class="content-card p-4 fade-up d2">
                <h5 class="fw-bold mb-3">Pengeluaran per Kategori</h5>
                <div class="chart-wrap">
                    <canvas id="categoryChart" height="230"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="content-card p-4 fade-up d3 h-100">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <h5 class="fw-bold mb-1">Proyeksi Bulan Depan</h5>
                    <span class="badge" style="background:#6366F11A;color:#6366F1;">Estimasi</span>
                </div>
                <p class="text-muted small mb-3">Perkiraan total pengeluaran per bulan, dinormalisasi dari semua siklus billing (mingguan, bulanan, kuartalan, tahunan).</p>
                <h2 class="fw-bold mb-0" style="color:#6366F1;">Rp <?= number_format($monthlyProjection, 0, ',', '.') ?></h2>
                <small class="text-muted">per bulan, berdasarkan subscription aktif saat ini</small>

                <hr class="my-3">

                <h6 class="fw-bold mb-2">Jatuh Tempo 7 Hari ke Depan</h6>
                <?php if (count($upcomingRenewals) > 0): ?>
                    <?php foreach ($upcomingRenewals as $u): ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <strong class="d-block"><?= htmlspecialchars($u['service_name']) ?></strong>
                            <small class="text-muted"><?= $u['days_left'] == 0 ? 'Hari ini' : ($u['days_left'] == 1 ? 'Besok' : $u['days_left'] . ' hari lagi') ?></small>
                        </div>
                        <strong>Rp <?= number_format($u['amount'], 0, ',', '.') ?></strong>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small mb-0">Tidak ada tagihan dalam 7 hari ke depan. Aman! 🎉</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="content-card p-4 fade-up d4 h-100">
                <h5 class="fw-bold mb-3">Status Subscription</h5>
                <div class="chart-wrap">
                    <canvas id="statusChart" height="180"></canvas>
                </div>
                <hr class="my-3">
                <h6 class="fw-bold mb-2">Rincian Kategori</h6>
                <?php if (count($byCategory) > 0): foreach ($byCategory as $c):
                    $pct = $totalActiveSpend > 0 ? round(($c['total'] / $totalActiveSpend) * 100) : 0;
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span><?= htmlspecialchars($c['category_name']) ?></span>
                        <strong>Rp <?= number_format($c['total'], 0, ',', '.') ?></strong>
                    </div>
                    <div class="thin-progress">
                        <div style="width:<?= $pct ?>%;background:#6366F1;"></div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <p class="text-muted mb-0">Belum ada data.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
</div>

<?php include "../templates/footer.php"; ?>

<script>
const monthlyLabels = <?= json_encode(array_map(fn($m) => date('M Y', strtotime($m['ym'] . '-01')), $monthly)) ?>;
const monthlyData = <?= json_encode(array_map(fn($m) => (float)$m['total'], $monthly)) ?>;

const categoryLabels = <?= json_encode(array_map(fn($c) => $c['category_name'], $byCategory)) ?>;
const categoryData = <?= json_encode(array_map(fn($c) => (float)$c['total'], $byCategory)) ?>;

const statusLabels = <?= json_encode(array_map(fn($s) => $s['status'], $byStatus)) ?>;
const statusData = <?= json_encode(array_map(fn($s) => (int)$s['total'], $byStatus)) ?>;

Chart.defaults.font.family = "Inter";

new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: monthlyLabels.length ? monthlyLabels : ['Belum ada data'],
        datasets: [{
            label: 'Total Pembayaran',
            data: monthlyData.length ? monthlyData : [0],
            borderColor: '#6366F1',
            backgroundColor: 'rgba(99,102,241,.12)',
            tension: .4,
            fill: true,
            pointBackgroundColor: '#6366F1',
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: categoryLabels.length ? categoryLabels : ['Belum ada data'],
        datasets: [{
            data: categoryData.length ? categoryData : [1],
            backgroundColor: ['#6366F1','#22C55E','#F59E0B','#EC4899','#0EA5E9','#8B5CF6','#14B8A6'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: { legend: { position: 'bottom' } }
    }
});

new Chart(document.getElementById('statusChart'), {
    type: 'bar',
    data: {
        labels: statusLabels.length ? statusLabels : ['Belum ada data'],
        datasets: [{
            label: 'Jumlah',
            data: statusData.length ? statusData : [0],
            backgroundColor: ['#22C55E','#F59E0B','#EF4444'],
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
