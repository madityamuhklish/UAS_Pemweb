<?php
require_once "inc/auth.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

checkAdminLogin();

$db = new Database();
$conn = $db->connect();

$stmt = $conn->query("
    SELECT c.*, (SELECT COUNT(*) FROM subscriptions s WHERE s.category_id = c.id) AS total_sub
    FROM categories c
    ORDER BY c.category_name
");
$categories = $stmt->fetchAll();

$pageTitle = "Kategori";
include "templates/header.php";
include "templates/sidebar.php";
include "templates/navbar.php";
?>

<div class="admin-content">

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-1">
        <div>
            <div class="admin-page-title">Kelola Kategori</div>
            <p class="admin-page-sub mb-0">Kategori ini digunakan oleh seluruh pengguna saat menambahkan subscription.</p>
        </div>
        <button class="admin-btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus me-2"></i>Tambah Kategori
        </button>
    </div>

    <?php renderFlash(); ?>

    <div class="admin-card">
        <?php if (count($categories) > 0): ?>
        <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Nama</th><th>Deskripsi</th><th>Total Subscription</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['category_name']) ?></strong></td>
                    <td><?= htmlspecialchars($c['description'] ?: '-') ?></td>
                    <td><?= $c['total_sub'] ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm admin-btn-outline edit-cat"
                                data-bs-toggle="modal" data-bs-target="#editModal"
                                data-id="<?= $c['id'] ?>" data-name="<?= htmlspecialchars($c['category_name']) ?>"
                                data-desc="<?= htmlspecialchars($c['description']) ?>">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="categories-process.php" method="POST" class="confirm-delete"
                                data-confirm="Hapus kategori <?= htmlspecialchars($c['category_name']) ?>?">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
            <div class="admin-empty"><i class="fa-solid fa-folder-open"></i>Belum ada kategori.</div>
        <?php endif; ?>
    </div>

</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="categories-process.php" method="POST">
        <input type="hidden" name="action" value="add">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Tambah Kategori</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Nama Kategori</label>
                <input type="text" name="category_name" class="form-control" required>
            </div>
            <div class="mb-2">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn admin-btn-outline" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="admin-btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="categories-process.php" method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_id">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Edit Kategori</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Nama Kategori</label>
                <input type="text" name="category_name" id="edit_name" class="form-control" required>
            </div>
            <div class="mb-2">
                <label>Deskripsi</label>
                <textarea name="description" id="edit_desc" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn admin-btn-outline" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="admin-btn-primary">Update</button>
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

<?php include "templates/footer.php"; ?>
