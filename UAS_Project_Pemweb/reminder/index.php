<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

$db = new Database();
$conn = $db->connect();

checkLogin($conn);
$userId = $_SESSION['user_id'];

$subs = $conn->prepare("SELECT id, service_name FROM subscriptions WHERE user_id = ? ORDER BY service_name");
$subs->execute([$userId]);
$subscriptions = $subs->fetchAll();

$stmt = $conn->prepare("
    SELECT r.*, s.service_name, s.next_payment, s.amount
    FROM reminders r
    JOIN subscriptions s ON s.id = r.subscription_id
    WHERE s.user_id = ?
    ORDER BY r.is_sent ASC, r.reminder_date ASC
");
$stmt->execute([$userId]);
$reminders = $stmt->fetchAll();

$pending = array_filter($reminders, fn($r) => !$r['is_sent']);
$done = array_filter($reminders, fn($r) => $r['is_sent']);

$typeColor = [
    'H-7' => '#0EA5E9',
    'H-3' => '#F59E0B',
    'H-1' => '#EF4444',
    'Today' => '#EF4444',
];

$pageTitle = "Reminder";
include "../templates/header.php";
include "../templates/sidebar.php";
include "../templates/navbar.php";
?>

<div class="main-content">
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="page-heading">
            <div class="page-icon"><i class="fa-solid fa-bell"></i></div>
            <div>
                <h2 class="title mb-0">Reminder</h2>
                <p class="subtitle mb-0">Jangan sampai lupa tanggal pembayaran</p>
            </div>
        </div>
        <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus me-2"></i>Tambah Reminder
        </button>
    </div>

    <?php renderFlash(); ?>

    <div class="row mb-2">
        <div class="col-md-6 mb-3">
            <div class="stat-pill fade-up">
                <div class="stat-icon" style="background:#F59E0B;"><i class="fa-solid fa-hourglass-half"></i></div>
                <div>
                    <h6>Belum Terkirim</h6>
                    <h4><?= count($pending) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="stat-pill fade-up d1">
                <div class="stat-icon" style="background:#22C55E;"><i class="fa-solid fa-check-double"></i></div>
                <div>
                    <h6>Sudah Dikirim</h6>
                    <h4><?= count($done) ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card p-4 fade-up d2">

        <?php if (count($reminders) > 0): ?>
        <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Layanan</th>
                    <th>Tanggal Reminder</th>
                    <th>Tipe</th>
                    <th>Jatuh Tempo</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reminders as $r): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['service_name']) ?></strong></td>
                    <td><?= date('d M Y', strtotime($r['reminder_date'])) ?></td>
                    <td>
                        <span class="badge" style="background:<?= $typeColor[$r['reminder_type']] ?? '#94A3B8' ?>22;color:<?= $typeColor[$r['reminder_type']] ?? '#64748B' ?>;">
                            <?= htmlspecialchars($r['reminder_type']) ?>
                        </span>
                    </td>
                    <td><?= $r['next_payment'] ? date('d M Y', strtotime($r['next_payment'])) : '-' ?></td>
                    <td>Rp <?= number_format($r['amount'], 0, ',', '.') ?></td>
                    <td>
                        <form action="process.php" method="POST">
                            <input type="hidden" name="action" value="toggle_sent">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="is_sent" value="<?= $r['is_sent'] ? 0 : 1 ?>">
                            <button class="btn btn-sm <?= $r['is_sent'] ? 'btn-success' : 'btn-outline-secondary' ?> rounded-pill">
                                <?= $r['is_sent'] ? 'Terkirim' : 'Tandai Terkirim' ?>
                            </button>
                        </form>
                    </td>
                    <td class="text-end">
                        <form action="process.php" method="POST" class="d-inline confirm-delete" data-confirm="Hapus reminder ini?">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button class="btn btn-sm btn-light"><i class="fa-solid fa-trash text-danger"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-bell-slash"></i>
            <p class="mb-3">Belum ada reminder.</p>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addModal">Tambah Reminder</button>
        </div>
        <?php endif; ?>

    </div>

</div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="process.php" method="POST">
        <input type="hidden" name="action" value="add">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Tambah Reminder</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Subscription</label>
                <select name="subscription_id" class="form-select" required>
                    <?php foreach ($subscriptions as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['service_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Tanggal Reminder</label>
                <input type="date" name="reminder_date" class="form-control" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Tipe</label>
                <select name="reminder_type" class="form-select">
                    <option>H-7</option>
                    <option>H-3</option>
                    <option>H-1</option>
                    <option>Today</option>
                </select>
            </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include "../templates/footer.php"; ?>
