
<?php

require_once "../config/session.php";
require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

checkLogin($conn);

/* ===========================
   Dashboard Statistics
=========================== */

$userId = $_SESSION['user_id'];

/* Total Subscription */
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM subscriptions
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$totalSubscription = $stmt->fetchColumn();

/* Total Wishlist */
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM wishlist
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$totalWishlist = $stmt->fetchColumn();

/* Total Reminder */
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM reminders r
    JOIN subscriptions s
        ON r.subscription_id = s.id
    WHERE s.user_id = ?
");
$stmt->execute([$userId]);
$totalReminder = $stmt->fetchColumn();

/* Total Pengeluaran */
$stmt = $conn->prepare("
    SELECT IFNULL(SUM(amount),0)
    FROM subscriptions
    WHERE user_id = ?
    AND status='Active'
");
$stmt->execute([$userId]);
$totalExpense = $stmt->fetchColumn();
/* ===========================
   Subscription Aktif
=========================== */

$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM subscriptions
    WHERE user_id = ?
    AND status='Active'
");

$stmt->execute([$userId]);

$totalActive = $stmt->fetchColumn();


/* ===========================
   Subscription Cancelled
=========================== */

$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM subscriptions
    WHERE user_id = ?
    AND status='Cancelled'
");

$stmt->execute([$userId]);

$totalCancelled = $stmt->fetchColumn();


/* ===========================
   Due Soon (7 Hari)
=========================== */

$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM subscriptions
    WHERE user_id = ?
    AND next_payment BETWEEN CURDATE()
    AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
");

$stmt->execute([$userId]);

$dueSoon = $stmt->fetchColumn();


/* ===========================
   Upcoming Payments
=========================== */

$stmt = $conn->prepare("
    SELECT
        service_name,
        amount,
        next_payment,
        status
    FROM subscriptions
    WHERE user_id = ?
    ORDER BY next_payment ASC
    LIMIT 5
");

$stmt->execute([$userId]);

$upcomingPayments = $stmt->fetchAll();


/* ===========================
   Recent Activity
=========================== */

$stmt = $conn->prepare("
    SELECT
        activity,
        created_at
    FROM activity_logs
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");

$stmt->execute([$userId]);

$recentActivities = $stmt->fetchAll();

/* ===========================
   Spending by Category (chart)
=========================== */

$stmt = $conn->prepare("
    SELECT c.category_name, IFNULL(SUM(s.amount),0) AS total
    FROM categories c
    LEFT JOIN subscriptions s ON s.category_id = c.id AND s.user_id = ? AND s.status = 'Active'
    GROUP BY c.id, c.category_name
    HAVING total > 0
    ORDER BY total DESC
");
$stmt->execute([$userId]);
$categorySpending = $stmt->fetchAll();

/* Quick wishlist preview */
$stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? ORDER BY created_at DESC LIMIT 4");
$stmt->execute([$userId]);
$wishlistPreview = $stmt->fetchAll();
/* ===========================
   Priority Badge Helper
=========================== */

function priorityBadgeClass($priority)
{
    switch ($priority) {
        case 'High':
            return 'bg-danger';

        case 'Medium':
            return 'bg-warning text-dark';

        case 'Low':
            return 'bg-success';

        default:
            return 'bg-secondary';
    }
}
$needsChart = true;
$pageTitle = "Dashboard";

include "../templates/header.php";
include "../templates/sidebar.php";
include "../templates/navbar.php";

?>

<div class="main-content">

<div class="container-fluid py-4">

<h2 class="title">
Dashboard
</h2>

<p class="subtitle">
Selamat datang kembali,
<strong><?= $_SESSION['fullname']; ?></strong>
</p>

<div class="row mt-4">

<div class="col-lg-3 mb-4 fade-up d1">

<div class="dashboard-card card-subscription"
     onclick="window.location='../subscription/index.php'">
<div>

<h6>Total Subscription</h6>

<h2 data-counter="<?= $totalSubscription ?>">0</h2>

</div>

<i class="fa-solid fa-credit-card icon"></i>

</div>

</div>

<div class="col-lg-3 mb-4 fade-up d2">

<div class="dashboard-card card-spending"
     onclick="window.location='../report/index.php'">
<div>

<h6>Monthly Spending</h6>

<h2 data-counter="<?= $totalExpense ?>" data-money>Rp 0</h2>

</div>

<i class="fa-solid fa-wallet icon"></i>

</div>

</div>

<div class="col-lg-3 mb-4 fade-up d3">

<div class="dashboard-card card-reminder"
     onclick="window.location='../reminder/index.php'">
<div>

<h6>Reminder</h6>

<h2 data-counter="<?= $totalReminder ?>">0</h2>

</div>

<i class="fa-solid fa-bell icon"></i>

</div>

</div>

<div class="col-lg-3 mb-4 fade-up d4">

<div class="dashboard-card card-wishlist"
     onclick="window.location='../wishlist/index.php'">
<div>

<h6>Wishlist</h6>

<h2 data-counter="<?= $totalWishlist ?>">0</h2>

</div>

<i class="fa-solid fa-heart icon"></i>

</div>

</div>

</div>
<div class="row">

    <!-- Spending by Category Chart -->
    <div class="col-lg-7 mb-4 fade-up d1">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0"><i class="fa-solid fa-chart-pie text-primary me-2"></i>Spending by Category</h5>
                <span class="streak-badge"><i class="fa-solid fa-fire"></i> Aktif</span>
            </div>
            <p class="subtitle mb-3" style="font-size:14px;">Distribusi pengeluaran subscription aktif kamu</p>
            <?php if (count($categorySpending) > 0): ?>
                <canvas id="categoryChart" height="220"></canvas>
                <div class="chart-legend" id="categoryLegend"></div>
            <?php else: ?>
                <div class="empty-state py-4">
                    <i class="fa-solid fa-chart-pie"></i>
                    <p class="mb-0">Belum ada data pengeluaran.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Wishlist quick preview -->
    <div class="col-lg-5 mb-4 fade-up d2">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fa-solid fa-heart text-primary me-2"></i>Wishlist Kamu</h5>
                <a href="../subscription/index.php" class="btn btn-sm btn-light rounded-pill px-3">Lihat Semua</a>
            </div>
            <?php if (count($wishlistPreview) > 0): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($wishlistPreview as $w): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <strong style="font-size:14.5px;"><?= htmlspecialchars($w['service_name']) ?></strong>
                                <br>
                                <span class="badge <?= priorityBadgeClass($w['priority']) ?>"><?= $w['priority'] ?></span>
                            </div>
                            <span class="fw-semibold" style="font-size:14px;">
                                Rp <?= number_format($w['estimated_price'], 0, ',', '.') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state py-3">
                    <i class="fa-solid fa-heart-crack"></i>
                    <p class="mb-0">Belum ada wishlist. Tambahkan layanan impianmu!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<div class="row">

    <!-- Upcoming Payments -->
    <div class="col-lg-7 mb-4">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fa-solid fa-calendar-days text-primary"></i>
                    Upcoming Payments
                </h5>
            </div>

            <div class="card-body">

                <?php if(count($upcomingPayments)>0): ?>

                <div class="table-responsive">
<table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>Service</th>

                            <th>Next Payment</th>

                            <th>Amount</th>

                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach($upcomingPayments as $item): ?>

                    <tr>

                        <td><?= htmlspecialchars($item['service_name']) ?></td>

                        <td><?= $item['next_payment'] ?></td>

                        <td>

                            Rp <?= number_format($item['amount'],0,",",".") ?>

                        </td>

                        <td>

                            <span class="badge bg-success">

                                <?= htmlspecialchars($item['status']) ?>

                            </span>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>
</div>

                <?php else: ?>

                    <p class="text-muted mb-0">
                        Belum ada subscription.
                    </p>

                <?php endif; ?>

            </div>

        </div>

    </div>

    <!-- Recent Activity -->

    <div class="col-lg-5 mb-4">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-white">

                <h5 class="mb-0">

                    <i class="fa-solid fa-clock-rotate-left text-primary"></i>

                    Recent Activity

                </h5>

            </div>

            <div class="card-body">

                <?php if(count($recentActivities)>0): ?>

                    <ul class="list-group list-group-flush">

                    <?php foreach($recentActivities as $activity): ?>

                        <li class="list-group-item">

                            <strong>

                                <?= htmlspecialchars($activity['activity']) ?>

                            </strong>

                            <br>

                            <small class="text-muted">

                                <?= $activity['created_at'] ?>

                            </small>

                        </li>

                    <?php endforeach; ?>

                    </ul>

                <?php else: ?>

                    <p class="text-muted mb-0">

                        Belum ada aktivitas.

                    </p>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>
</div>

</div>

<?php if (count($categorySpending) > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('categoryChart');
    if (!ctx) return;

    const labels = <?= json_encode(array_column($categorySpending, 'category_name')) ?>;
    const data = <?= json_encode(array_map('floatval', array_column($categorySpending, 'total'))) ?>;
    const colors = ['#6366F1', '#22C55E', '#F59E0B', '#EC4899', '#0EA5E9', '#8B5CF6', '#14B8A6'];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            cutout: '68%',
            plugins: { legend: { display: false } }
        }
    });

    const legend = document.getElementById('categoryLegend');
    labels.forEach((label, i) => {
        const item = document.createElement('span');
        item.innerHTML = `<i style="background:${colors[i % colors.length]}"></i> ${label}`;
        legend.appendChild(item);
    });
});
</script>
<?php endif; ?>

<?php

include "../templates/footer.php";
?>
