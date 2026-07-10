<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

$db = new Database();
$conn = $db->connect();

checkLogin($conn);
$userId = $_SESSION['user_id'];

$paymentMethods = $conn->query("SELECT * FROM payment_methods ORDER BY method_name")->fetchAll();

$subs = $conn->prepare("SELECT id, service_name FROM subscriptions WHERE user_id = ? ORDER BY service_name");
$subs->execute([$userId]);
$subscriptions = $subs->fetchAll();

$stmt = $conn->prepare("
    SELECT ph.*, s.service_name
    FROM payment_history ph
    JOIN subscriptions s ON s.id = ph.subscription_id
    WHERE s.user_id = ?
    ORDER BY ph.payment_date DESC
    LIMIT 30
");
$stmt->execute([$userId]);
$history = $stmt->fetchAll();

$icons = ['fa-building-columns','fa-wallet','fa-credit-card','fa-mobile-screen'];

$pageTitle = "Payment";
include "../templates/header.php";
include "../templates/sidebar.php";
include "../templates/navbar.php";
?>

<div class="main-content">
<div class="container-fluid py-4">

    <div class="page-heading mb-4">
        <div class="page-icon"><i class="fa-solid fa-money-check-dollar"></i></div>
        <div>
            <h2 class="title mb-0">Payment</h2>
            <p class="subtitle mb-0">Metode pembayaran &amp; riwayat transaksi</p>
        </div>
    </div>

    <?php renderFlash(); ?>

    <div class="row">

        <!-- Payment Methods -->
        <div class="col-lg-5 mb-4">
            <div class="content-card p-4 fade-up">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Metode Pembayaran</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMethodModal">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>

                <?php if (count($paymentMethods) > 0): foreach ($paymentMethods as $i => $pm): ?>
                <div class="d-flex align-items-center justify-content-between py-3 <?= $i < count($paymentMethods)-1 ? 'border-bottom' : '' ?>">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:44px;height:44px;border-radius:12px;background:rgba(99,102,241,.1);color:#6366F1;display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid <?= $icons[$i % count($icons)] ?>"></i>
                        </div>
                        <div>
                            <strong class="d-block"><?= htmlspecialchars($pm['method_name']) ?></strong>
                            <small class="text-muted"><?= htmlspecialchars($pm['provider']) ?></small>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-light edit-method"
                            data-bs-toggle="modal" data-bs-target="#editMethodModal"
                            data-id="<?= $pm['id'] ?>"
                            data-method="<?= htmlspecialchars($pm['method_name']) ?>"
                            data-provider="<?= htmlspecialchars($pm['provider']) ?>">
                            <i class="fa-solid fa-pen text-primary"></i>
                        </button>
                        <form action="process.php" method="POST" class="d-inline confirm-delete" data-confirm="Hapus metode ini?">
                            <input type="hidden" name="action" value="delete_method">
                            <input type="hidden" name="id" value="<?= $pm['id'] ?>">
                            <button class="btn btn-sm btn-light"><i class="fa-solid fa-trash text-danger"></i></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="empty-state py-4">
                    <i class="fa-solid fa-wallet"></i>
                    <p class="mb-0">Belum ada metode pembayaran.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- History -->
        <div class="col-lg-7 mb-4">
            <div class="content-card p-4 fade-up d2">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Riwayat Pembayaran</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addHistoryModal">
                        <i class="fa-solid fa-plus me-1"></i>Catat
                    </button>
                </div>

                <?php if (count($history) > 0): ?>
                <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?= htmlspecialchars($h['service_name']) ?></td>
                            <td><?= date('d M Y', strtotime($h['payment_date'])) ?></td>
                            <td>Rp <?= number_format($h['amount'], 0, ',', '.') ?></td>
                            <td>
                                <?php
                                $badgeClass = $h['status'] === 'Paid' ? 'status-active' : ($h['status'] === 'Pending' ? 'status-paused' : 'status-cancelled');
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($h['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <form action="process.php" method="POST" class="confirm-delete" data-confirm="Hapus riwayat ini?">
                                    <input type="hidden" name="action" value="delete_history">
                                    <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                    <button class="btn btn-sm btn-light"><i class="fa-solid fa-trash text-danger"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?>
                <div class="empty-state py-4">
                    <i class="fa-solid fa-receipt"></i>
                    <p class="mb-0">Belum ada riwayat pembayaran.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>
</div>

<!-- Add Method Modal -->
<div class="modal fade" id="addMethodModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="process.php" method="POST">
        <input type="hidden" name="action" value="add_method">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Tambah Metode Pembayaran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nama Metode</label>
                <input type="text" name="method_name" class="form-control" placeholder="Bank Transfer / E-Wallet / Credit Card" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Provider</label>
                <input type="text" name="provider" class="form-control" placeholder="BCA, DANA, Visa, dll">
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

<!-- Edit Method Modal -->
<div class="modal fade" id="editMethodModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="process.php" method="POST">
        <input type="hidden" name="action" value="edit_method">
        <input type="hidden" name="id" id="edit_method_id">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Edit Metode Pembayaran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nama Metode</label>
                <input type="text" name="method_name" id="edit_method_name" class="form-control" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Provider</label>
                <input type="text" name="provider" id="edit_method_provider" class="form-control">
            </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add History Modal -->
<div class="modal fade" id="addHistoryModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="process.php" method="POST">
        <input type="hidden" name="action" value="add_history">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Catat Pembayaran</h5>
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
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="payment_date" class="form-control" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Jumlah (Rp)</label>
                    <input type="number" name="amount" class="form-control" required min="0">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option selected>Paid</option>
                    <option>Pending</option>
                    <option>Failed</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Catatan</label>
                <textarea name="note" class="form-control" rows="2"></textarea>
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

<script>
document.querySelectorAll('.edit-method').forEach(el => {
    el.addEventListener('click', function () {
        document.getElementById('edit_method_id').value = this.dataset.id;
        document.getElementById('edit_method_name').value = this.dataset.method;
        document.getElementById('edit_method_provider').value = this.dataset.provider;
    });
});
</script>

<?php include "../templates/footer.php"; ?>
