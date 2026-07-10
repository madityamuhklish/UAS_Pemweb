<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

$db = new Database();
$conn = $db->connect();

checkLogin($conn);
$userId = $_SESSION['user_id'];

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
$paymentMethods = $conn->query("SELECT * FROM payment_methods ORDER BY method_name")->fetchAll();

$statusFilter = $_GET['status'] ?? 'all';

$sql = "SELECT s.*, c.category_name, pm.method_name
        FROM subscriptions s
        LEFT JOIN categories c ON c.id = s.category_id
        LEFT JOIN payment_methods pm ON pm.id = s.payment_method_id
        WHERE s.user_id = ?";
$params = [$userId];

if ($statusFilter !== 'all') {
    $sql .= " AND s.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY s.next_payment ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$subscriptions = $stmt->fetchAll();

$pageTitle = "Subscription";
include "../templates/header.php";
include "../templates/sidebar.php";
include "../templates/navbar.php";
?>

<div class="main-content">
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="page-heading">
            <div class="page-icon"><i class="fa-solid fa-credit-card"></i></div>
            <div>
                <h2 class="title mb-0">Subscription</h2>
                <p class="subtitle mb-0">Kelola semua langganan kamu di satu tempat</p>
            </div>
        </div>

        <button class="btn btn-primary px-4 py-2 fade-up" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus me-2"></i>Tambah Subscription
        </button>
    </div>

    <?php renderFlash(); ?>

    <!-- Filter Tabs -->
    <div class="d-flex gap-2 mb-3 flex-wrap fade-up d1">
        <?php
        $tabs = ['all' => 'Semua', 'Active' => 'Active', 'Paused' => 'Paused', 'Cancelled' => 'Cancelled'];
        foreach ($tabs as $key => $label):
            $active = $statusFilter === $key ? 'btn-primary' : 'btn-light';
        ?>
            <a href="?status=<?= $key ?>" class="btn <?= $active ?> btn-sm rounded-pill px-3"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <div class="content-card p-4 fade-up d2" data-search-target>

        <?php if (count($subscriptions) > 0): ?>
        <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Layanan</th>
                    <th>Kategori</th>
                    <th>Metode</th>
                    <th>Jumlah</th>
                    <th>Siklus</th>
                    <th>Pembayaran Berikutnya</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions as $sub): ?>
                <tr data-search-row>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:38px;height:38px;border-radius:10px;background:rgba(99,102,241,.1);display:flex;align-items:center;justify-content:center;color:#6366F1;">
                                <i class="fa-solid fa-circle-play"></i>
                            </div>
                            <strong><?= htmlspecialchars($sub['service_name']) ?></strong>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($sub['category_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($sub['method_name'] ?? '-') ?></td>
                    <td>Rp <?= number_format($sub['amount'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($sub['billing_cycle']) ?></td>
                    <td><?= date('d M Y', strtotime($sub['next_payment'])) ?></td>
                    <td><span class="badge <?= statusBadgeClass($sub['status']) ?>"><?= htmlspecialchars($sub['status']) ?></span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light" title="Edit"
                            data-bs-toggle="modal" data-bs-target="#editModal"
                            data-id="<?= $sub['id'] ?>"
                            data-service="<?= htmlspecialchars($sub['service_name']) ?>"
                            data-category="<?= $sub['category_id'] ?>"
                            data-payment="<?= $sub['payment_method_id'] ?>"
                            data-amount="<?= $sub['amount'] ?>"
                            data-cycle="<?= $sub['billing_cycle'] ?>"
                            data-start="<?= $sub['start_date'] ?>"
                            data-next="<?= $sub['next_payment'] ?>"
                            data-renew="<?= $sub['auto_renew'] ?>"
                            data-status="<?= $sub['status'] ?>"
                            data-note="<?= htmlspecialchars($sub['note']) ?>">
                            <i class="fa-solid fa-pen text-primary"></i>
                        </button>
                        <form action="process.php" method="POST" class="d-inline confirm-delete" data-confirm="Hapus subscription <?= htmlspecialchars($sub['service_name']) ?>?">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                            <button class="btn btn-sm btn-light" title="Hapus">
                                <i class="fa-solid fa-trash text-danger"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <p class="mb-3">Belum ada subscription untuk filter ini.</p>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addModal">
                    Tambah Subscription Pertama
                </button>
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
          <h5 class="modal-title fw-bold">Tambah Subscription</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nama Layanan</label>
                <input type="text" name="service_name" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select">
                        <option value="">- Pilih -</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Metode Bayar</label>
                    <select name="payment_method_id" class="form-select">
                        <option value="">- Pilih -</option>
                        <?php foreach ($paymentMethods as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['method_name']) ?> (<?= htmlspecialchars($p['provider']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Jumlah (Rp)</label>
                    <input type="number" name="amount" class="form-control" required min="0">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Siklus</label>
                    <select name="billing_cycle" class="form-select">
                        <option>Weekly</option>
                        <option selected>Monthly</option>
                        <option>Quarterly</option>
                        <option>Yearly</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Pembayaran Berikutnya</label>
                    <input type="date" name="next_payment" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option selected>Active</option>
                    <option>Paused</option>
                    <option>Cancelled</option>
                </select>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="auto_renew" checked>
                <label class="form-check-label">Perpanjang Otomatis</label>
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="process.php" method="POST" id="editForm">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_id">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Edit Subscription</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nama Layanan</label>
                <input type="text" name="service_name" id="edit_service" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" id="edit_category" class="form-select">
                        <option value="">- Pilih -</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Metode Bayar</label>
                    <select name="payment_method_id" id="edit_payment" class="form-select">
                        <option value="">- Pilih -</option>
                        <?php foreach ($paymentMethods as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['method_name']) ?> (<?= htmlspecialchars($p['provider']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Jumlah (Rp)</label>
                    <input type="number" name="amount" id="edit_amount" class="form-control" required min="0">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Siklus</label>
                    <select name="billing_cycle" id="edit_cycle" class="form-select">
                        <option>Weekly</option>
                        <option>Monthly</option>
                        <option>Quarterly</option>
                        <option>Yearly</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="edit_start" class="form-control" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Pembayaran Berikutnya</label>
                    <input type="date" name="next_payment" id="edit_next" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" id="edit_status" class="form-select">
                    <option>Active</option>
                    <option>Paused</option>
                    <option>Cancelled</option>
                </select>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="auto_renew" id="edit_renew">
                <label class="form-check-label">Perpanjang Otomatis</label>
            </div>
            <div class="mb-2">
                <label class="form-label">Catatan</label>
                <textarea name="note" id="edit_note" class="form-control" rows="2"></textarea>
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

<script>
document.getElementById('editModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('edit_id').value = btn.dataset.id;
    document.getElementById('edit_service').value = btn.dataset.service;
    document.getElementById('edit_category').value = btn.dataset.category || '';
    document.getElementById('edit_payment').value = btn.dataset.payment || '';
    document.getElementById('edit_amount').value = btn.dataset.amount;
    document.getElementById('edit_cycle').value = btn.dataset.cycle;
    document.getElementById('edit_start').value = btn.dataset.start;
    document.getElementById('edit_next').value = btn.dataset.next;
    document.getElementById('edit_status').value = btn.dataset.status;
    document.getElementById('edit_renew').checked = btn.dataset.renew === '1';
    document.getElementById('edit_note').value = btn.dataset.note;
});
</script>

<?php include "../templates/footer.php"; ?>
