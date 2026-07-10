<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

$db = new Database();
$conn = $db->connect();

checkLogin($conn);
$userId = $_SESSION['user_id'];

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();

$sortBy = $_GET['sort'] ?? 'priority';
$orderMap = [
    'priority' => "FIELD(priority,'High','Medium','Low')",
    'newest'   => "created_at DESC",
    'price'    => "estimated_price DESC",
];
$order = $orderMap[$sortBy] ?? $orderMap['priority'];

$stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? ORDER BY $order");
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

$totalEstimate = array_sum(array_column($items, 'estimated_price'));
$highCount = count(array_filter($items, fn($i) => $i['priority'] === 'High'));

$pageTitle = "Wishlist";
include "../templates/header.php";
include "../templates/sidebar.php";
include "../templates/navbar.php";
?>

<div class="main-content">
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="page-heading">
            <div class="page-icon"><i class="fa-solid fa-heart"></i></div>
            <div>
                <h2 class="title mb-0">Wishlist</h2>
                <p class="subtitle mb-0">Layanan yang ingin kamu langgan nanti</p>
            </div>
        </div>
        <button class="btn btn-primary px-4 py-2 fade-up" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus me-2"></i>Tambah ke Wishlist
        </button>
    </div>

    <?php renderFlash(); ?>

    <div class="row mb-2">
        <div class="col-md-4 mb-3">
            <div class="stat-pill fade-up">
                <div class="stat-icon" style="background:#EC4899;"><i class="fa-solid fa-heart"></i></div>
                <div>
                    <h6>Total Wishlist</h6>
                    <h4 data-counter="<?= count($items) ?>">0</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-pill fade-up d1">
                <div class="stat-icon" style="background:#EF4444;"><i class="fa-solid fa-fire"></i></div>
                <div>
                    <h6>Prioritas Tinggi</h6>
                    <h4 data-counter="<?= $highCount ?>">0</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-pill fade-up d2">
                <div class="stat-icon" style="background:#6366F1;"><i class="fa-solid fa-sack-dollar"></i></div>
                <div>
                    <h6>Estimasi Total / Bulan</h6>
                    <h4 data-counter="<?= $totalEstimate ?>" data-money>Rp 0</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card p-4 fade-up d2" data-search-target>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div class="search-box" style="max-width:280px;">
                <i class="fa-solid fa-magnifying-glass"></i>
<input
    type="text"
    id="searchWishlist"
    placeholder="Cari wishlist...">            
</div>
            <div class="d-flex gap-2">
                <a href="?sort=priority" class="btn btn-sm <?= $sortBy==='priority'?'btn-primary':'btn-light' ?> rounded-pill px-3">Prioritas</a>
                <a href="?sort=newest" class="btn btn-sm <?= $sortBy==='newest'?'btn-primary':'btn-light' ?> rounded-pill px-3">Terbaru</a>
                <a href="?sort=price" class="btn btn-sm <?= $sortBy==='price'?'btn-primary':'btn-light' ?> rounded-pill px-3">Termahal</a>
            </div>
        </div>

        <?php if (count($items) > 0): ?>
        <div class="row">
            <?php foreach ($items as $item):
                $prioColor = ['High' => '#EF4444', 'Medium' => '#F59E0B', 'Low' => '#22C55E'][$item['priority']] ?? '#6366F1';
            ?>
            <div class="col-lg-4 col-md-6 mb-4" data-search-row>
                <div class="content-card p-3 h-100" style="border-left:4px solid <?= $prioColor ?>;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong class="d-block"><?= htmlspecialchars($item['service_name']) ?></strong>
                            <span class="badge" style="background:<?= $prioColor ?>1A;color:<?= $prioColor ?>;">
                                <?= htmlspecialchars($item['priority']) ?> Priority
                            </span>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item edit-wish" href="#"
                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                        data-id="<?= $item['id'] ?>"
                                        data-name="<?= htmlspecialchars($item['service_name']) ?>"
                                        data-price="<?= $item['estimated_price'] ?>"
                                        data-priority="<?= $item['priority'] ?>"
                                        data-note="<?= htmlspecialchars($item['note'] ?? '') ?>">
                                        <i class="fa-solid fa-pen text-primary me-2"></i>Edit
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item convert-wish" href="#"
                                        data-bs-toggle="modal" data-bs-target="#convertModal"
                                        data-id="<?= $item['id'] ?>"
                                        data-name="<?= htmlspecialchars($item['service_name']) ?>"
                                        data-price="<?= $item['estimated_price'] ?>">
                                        <i class="fa-solid fa-arrow-right-arrow-left text-success me-2"></i>Jadikan Subscription
                                    </a>
                                </li>
                                <li>
                                    <form action="process.php" method="POST" class="confirm-delete" data-confirm="Hapus '<?= htmlspecialchars($item['service_name']) ?>' dari wishlist?">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button class="dropdown-item text-danger"><i class="fa-solid fa-trash me-2"></i>Hapus</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-2">Rp <?= number_format($item['estimated_price'] ?? 0, 0, ',', '.') ?></h5>
                    <p class="text-muted small mb-0"><?= htmlspecialchars($item['note'] ?: 'Tidak ada catatan') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-heart-crack"></i>
                <p class="mb-1 fw-semibold">Wishlist kamu masih kosong</p>
                <p class="mb-3 text-muted small">Simpan layanan yang kamu incar di sini, biar gak lupa dan gampang dipantau harganya.</p>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addModal">
                    Tambah Wishlist Pertama
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
      <form action="process.php" method="POST" class="needs-loading">
        <input type="hidden" name="action" value="add">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Tambah ke Wishlist</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nama Layanan</label>
                <input type="text" name="service_name" class="form-control" placeholder="Netflix, Spotify, dll" required>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Estimasi Harga (Rp)</label>
                    <input type="number" name="estimated_price" class="form-control" min="0">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Prioritas</label>
                    <select name="priority" class="form-select">
                        <option>Low</option>
                        <option selected>Medium</option>
                        <option>High</option>
                    </select>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Catatan</label>
                <textarea name="note" class="form-control" rows="2" placeholder="Kenapa kamu mau langganan ini?"></textarea>
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
      <form action="process.php" method="POST" class="needs-loading">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_id">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Edit Wishlist</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nama Layanan</label>
                <input type="text" name="service_name" id="edit_name" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Estimasi Harga (Rp)</label>
                    <input type="number" name="estimated_price" id="edit_price" class="form-control" min="0">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Prioritas</label>
                    <select name="priority" id="edit_priority" class="form-select">
                        <option>Low</option>
                        <option>Medium</option>
                        <option>High</option>
                    </select>
                </div>
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

<!-- Convert to Subscription Modal -->
<div class="modal fade" id="convertModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="process.php" method="POST" class="needs-loading">
        <input type="hidden" name="action" value="convert">
        <input type="hidden" name="id" id="convert_id">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Jadikan Subscription</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <p class="text-muted mb-3">Item ini akan dipindahkan jadi subscription aktif dan otomatis hilang dari wishlist. Lengkapi detail berikut:</p>
            <div class="mb-3">
                <label class="form-label">Nama Layanan</label>
                <input type="text" class="form-control" id="convert_name" disabled>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Jumlah (Rp)</label>
                    <input type="number" name="amount" id="convert_amount" class="form-control" required min="0">
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
                    <input type="date" name="start_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Pembayaran Berikutnya</label>
                    <input type="date" name="next_payment" class="form-control" required>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-select">
                    <option value="">- Pilih -</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success px-4"><i class="fa-solid fa-check me-1"></i>Jadikan Subscription</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.edit-wish').forEach(el => {
    el.addEventListener('click', function () {
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('edit_name').value = this.dataset.name;
        document.getElementById('edit_price').value = this.dataset.price;
        document.getElementById('edit_priority').value = this.dataset.priority;
        document.getElementById('edit_note').value = this.dataset.note;
    });
});
document.querySelectorAll('.convert-wish').forEach(el => {
    el.addEventListener('click', function () {
        document.getElementById('convert_id').value = this.dataset.id;
        document.getElementById('convert_name').value = this.dataset.name;
        document.getElementById('convert_amount').value = this.dataset.price;
    });
});
/* Search Wishlist */

const searchWishlist = document.getElementById("searchWishlist");

searchWishlist.addEventListener("keyup", function () {

    const keyword = this.value.toLowerCase();

    document.querySelectorAll("[data-search-row]").forEach(function (row) {

        const service = row.querySelector("strong")
                           .textContent
                           .toLowerCase();

        if (service.includes(keyword)) {

            row.style.display = "";

        } else {

            row.style.display = "none";

        }

    });

});
</script>

<?php include "../templates/footer.php"; ?>
