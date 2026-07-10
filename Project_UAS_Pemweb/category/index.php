<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

$db = new Database();
$conn = $db->connect();

checkLogin($conn);
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT c.*,
        (SELECT COUNT(*) FROM subscriptions s WHERE s.category_id = c.id AND s.user_id = ?) AS total_sub,
        (SELECT IFNULL(SUM(amount),0) FROM subscriptions s WHERE s.category_id = c.id AND s.user_id = ? AND s.status='Active') AS total_spend
    FROM categories c
    ORDER BY c.category_name
");
$stmt->execute([$userId, $userId]);
$categories = $stmt->fetchAll();

$palette = ['#6366F1', '#22C55E', '#F59E0B', '#EC4899', '#0EA5E9', '#8B5CF6', '#14B8A6'];

// Icon/color is matched to the category NAME (keyword-based) instead of
// row order, so "Music" always gets a music note, "Design" a palette, etc.
// Falls back to a consistent (hash-based, not random) icon for categories
// that don't match a known keyword, e.g. custom categories the user adds.
$iconMap = [
    'ai'        => 'fa-robot',
    'artificial'=> 'fa-robot',
    'cloud'     => 'fa-cloud',
    'storage'   => 'fa-cloud',
    'design'    => 'fa-palette',
    'music'     => 'fa-music',
    'audio'     => 'fa-music',
    'stream'    => 'fa-clapperboard',
    'video'     => 'fa-clapperboard',
    'film'      => 'fa-clapperboard',
    'game'      => 'fa-gamepad',
    'gaming'    => 'fa-gamepad',
    'fitness'   => 'fa-dumbbell',
    'gym'       => 'fa-dumbbell',
    'health'    => 'fa-heart-pulse',
    'news'      => 'fa-newspaper',
    'baca'      => 'fa-book',
    'book'      => 'fa-book',
    'education' => 'fa-graduation-cap',
    'pendidikan'=> 'fa-graduation-cap',
    'productiv' => 'fa-list-check',
    'kerja'     => 'fa-briefcase',
    'kantor'    => 'fa-briefcase',
    'finance'   => 'fa-wallet',
    'keuangan'  => 'fa-wallet',
    'internet'  => 'fa-wifi',
    'security'  => 'fa-shield-halved',
    'vpn'       => 'fa-shield-halved',
    'food'      => 'fa-utensils',
    'makanan'   => 'fa-utensils',
];
$fallbackIcons = ['fa-layer-group','fa-box','fa-star','fa-tag','fa-puzzle-piece'];

function categoryVisual($name, $iconMap, $palette, $fallbackIcons) {
    $lower = strtolower($name);
    foreach ($iconMap as $keyword => $icon) {
        if (str_contains($lower, $keyword)) {
            // deterministic color based on the keyword, not row position
            $hash = crc32($keyword);
            return [$icon, $palette[$hash % count($palette)]];
        }
    }
    // no keyword match: still deterministic (same name -> same icon/color
    // every time), just not from the curated keyword map
    $hash = crc32($lower);
    return [$fallbackIcons[$hash % count($fallbackIcons)], $palette[$hash % count($palette)]];
}

$pageTitle = "Category";
include "../templates/header.php";
include "../templates/sidebar.php";
include "../templates/navbar.php";
?>

<div class="main-content">
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="page-heading">
            <div class="page-icon"><i class="fa-solid fa-folder"></i></div>
            <div>
                <h2 class="title mb-0">Category</h2>
                <p class="subtitle mb-0">Kelompokkan subscription berdasarkan jenis layanan</p>
            </div>
        </div>
        <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus me-2"></i>Tambah Kategori
        </button>
    </div>

    <?php renderFlash(); ?>

    <div class="row">
        <?php if (count($categories) > 0): foreach ($categories as $i => $cat):
            [$icon, $color] = categoryVisual($cat['category_name'], $iconMap, $palette, $fallbackIcons);
        ?>
        <div class="col-lg-4 col-md-6 mb-4 fade-up d<?= min(($i%6)+1,6) ?>">
            <div class="content-card p-4 h-100 category-card">
                <i class="fa-solid <?= $icon ?> category-card-watermark" style="color:<?= $color ?>;"></i>
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width:52px;height:52px;border-radius:14px;background:<?= $color ?>1A;color:<?= $color ?>;display:flex;align-items:center;justify-content:center;font-size:22px;">
                        <i class="fa-solid <?= $icon ?>"></i>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item edit-cat"
                                    href="#"
                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-id="<?= $cat['id'] ?>"
                                    data-name="<?= htmlspecialchars($cat['category_name']) ?>"
                                    data-desc="<?= htmlspecialchars($cat['description']) ?>">
                                    <i class="fa-solid fa-pen text-primary me-2"></i>Edit
                                </a>
                            </li>
                            <li>
                                <form action="process.php" method="POST" class="confirm-delete" data-confirm="Hapus kategori <?= htmlspecialchars($cat['category_name']) ?>?">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <button class="dropdown-item text-danger"><i class="fa-solid fa-trash me-2"></i>Hapus</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($cat['category_name']) ?></h5>
                <p class="text-muted small mb-3"><?= htmlspecialchars($cat['description'] ?: 'Tidak ada deskripsi') ?></p>
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted d-block">Subscription</small>
                        <strong><?= $cat['total_sub'] ?></strong>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Pengeluaran Aktif</small>
                        <strong>Rp <?= number_format($cat['total_spend'], 0, ',', '.') ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; else: ?>
        <div class="col-12">
            <div class="empty-state">
                <i class="fa-solid fa-folder-open"></i>
                <p class="mb-3">Belum ada kategori.</p>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addModal">Tambah Kategori</button>
            </div>
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
          <h5 class="modal-title fw-bold">Tambah Kategori</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nama Kategori</label>
                <input type="text" name="category_name" class="form-control" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
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
      <form action="process.php" method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_id">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Edit Kategori</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nama Kategori</label>
                <input type="text" name="category_name" id="edit_name" class="form-control" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" id="edit_desc" class="form-control" rows="3"></textarea>
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
document.querySelectorAll('.edit-cat').forEach(el => {
    el.addEventListener('click', function () {
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('edit_name').value = this.dataset.name;
        document.getElementById('edit_desc').value = this.dataset.desc;
    });
});
</script>

<?php include "../templates/footer.php"; ?>
