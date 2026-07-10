<?php
require_once "inc/auth.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

checkAdminLogin();

$db = new Database();
$conn = $db->connect();

$categories = $conn->query("SELECT * FROM faq_categories ORDER BY sort_order, name")->fetchAll();

$faqs = $conn->query("
    SELECT f.*, c.name AS category_name
    FROM faqs f
    JOIN faq_categories c ON c.id = f.category_id
    ORDER BY c.sort_order, f.sort_order, f.id
")->fetchAll();

$settings = $conn->query("SELECT * FROM support_settings LIMIT 1")->fetch() ?: [];

$pageTitle = "FAQ & Bantuan";
include "templates/header.php";
include "templates/sidebar.php";
include "templates/navbar.php";
?>

<div class="admin-content">

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-1">
        <div>
            <div class="admin-page-title">FAQ &amp; Bantuan</div>
            <p class="admin-page-sub mb-0">Kelola topik, pertanyaan, dan kontak CS yang digunakan chatbot bantuan.</p>
        </div>
    </div>

    <?php renderFlash(); ?>

    <!-- Kontak CS -->
    <div class="admin-card mb-4">
        <h6 class="fw-bold mb-3 text-a-white"><i class="fa-solid fa-headset me-2"></i>Kontak Customer Service</h6>
        <form action="faqs-process.php" method="POST" class="row g-3">
            <input type="hidden" name="action" value="update_settings">
            <div class="col-md-4">
                <label class="mb-1 text-a-muted" style="font-size:13px;">Nomor WhatsApp (format 62xxxx)</label>
                <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($settings['whatsapp'] ?? '') ?>" placeholder="628123456789">
            </div>
            <div class="col-md-4">
                <label class="mb-1 text-a-muted" style="font-size:13px;">Email CS</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($settings['email'] ?? '') ?>" placeholder="cs@subspilot.app">
            </div>
            <div class="col-md-4">
                <label class="mb-1 text-a-muted" style="font-size:13px;">Jam Operasional</label>
                <input type="text" name="operational_hours" class="form-control" value="<?= htmlspecialchars($settings['operational_hours'] ?? '') ?>" placeholder="Setiap hari, 09:00 - 21:00 WIB">
            </div>
            <div class="col-12">
                <button type="submit" class="admin-btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan Kontak</button>
            </div>
        </form>
    </div>

    <!-- Kategori -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0 text-a-white"><i class="fa-solid fa-folder-tree me-2"></i>Kategori Topik</h6>
        <button class="admin-btn-primary" data-bs-toggle="modal" data-bs-target="#addCatModal">
            <i class="fa-solid fa-plus me-2"></i>Tambah Kategori
        </button>
    </div>
    <div class="admin-card mb-4">
        <?php if (count($categories) > 0): ?>
        <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Nama</th><th>Icon</th><th>Urutan</th><th>Jumlah FAQ</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <?php $total = count(array_filter($faqs, fn($f) => $f['category_id'] == $c['id'])); ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                    <td><i class="fa-solid <?= htmlspecialchars($c['icon']) ?>"></i> <?= htmlspecialchars($c['icon']) ?></td>
                    <td><?= (int) $c['sort_order'] ?></td>
                    <td><?= $total ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm admin-btn-outline edit-cat"
                                data-bs-toggle="modal" data-bs-target="#editCatModal"
                                data-id="<?= $c['id'] ?>" data-name="<?= htmlspecialchars($c['name']) ?>"
                                data-icon="<?= htmlspecialchars($c['icon']) ?>" data-order="<?= (int) $c['sort_order'] ?>">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="faqs-process.php" method="POST" class="confirm-delete"
                                data-confirm="Hapus kategori <?= htmlspecialchars($c['name']) ?> beserta seluruh FAQ di dalamnya?">
                                <input type="hidden" name="action" value="delete_category">
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

    <!-- FAQ -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0 text-a-white"><i class="fa-solid fa-circle-question me-2"></i>Daftar FAQ</h6>
        <button class="admin-btn-primary" data-bs-toggle="modal" data-bs-target="#addFaqModal" <?= count($categories) === 0 ? 'disabled' : '' ?>>
            <i class="fa-solid fa-plus me-2"></i>Tambah FAQ
        </button>
    </div>
    <div class="admin-card">
        <?php if (count($faqs) > 0): ?>
        <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Kategori</th><th>Pertanyaan</th><th>Kata Kunci</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($faqs as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['category_name']) ?></td>
                    <td><strong><?= htmlspecialchars($f['question']) ?></strong></td>
                    <td><span class="text-a-muted" style="font-size:12.5px;"><?= htmlspecialchars($f['keywords'] ?: '-') ?></span></td>
                    <td>
                        <form action="faqs-process.php" method="POST">
                            <input type="hidden" name="action" value="toggle_faq">
                            <input type="hidden" name="id" value="<?= $f['id'] ?>">
                            <input type="hidden" name="is_active" value="<?= $f['is_active'] ? 0 : 1 ?>">
                            <button class="btn btn-sm <?= $f['is_active'] ? 'btn-success' : 'btn-outline-secondary' ?> rounded-pill">
                                <?= $f['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm admin-btn-outline edit-faq"
                                data-bs-toggle="modal" data-bs-target="#editFaqModal"
                                data-id="<?= $f['id'] ?>"
                                data-category="<?= $f['category_id'] ?>"
                                data-question="<?= htmlspecialchars($f['question']) ?>"
                                data-answer="<?= htmlspecialchars($f['answer']) ?>"
                                data-keywords="<?= htmlspecialchars($f['keywords']) ?>"
                                data-order="<?= (int) $f['sort_order'] ?>">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="faqs-process.php" method="POST" class="confirm-delete" data-confirm="Hapus FAQ ini?">
                                <input type="hidden" name="action" value="delete_faq">
                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
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
            <div class="admin-empty"><i class="fa-solid fa-circle-question"></i>Belum ada FAQ. Tambahkan kategori terlebih dahulu.</div>
        <?php endif; ?>
    </div>

</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCatModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="faqs-process.php" method="POST">
        <input type="hidden" name="action" value="add_category">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Tambah Kategori</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Nama Kategori</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Icon (Font Awesome, cth: fa-user-lock)</label>
                <input type="text" name="icon" class="form-control" value="fa-circle-question">
            </div>
            <div class="mb-2">
                <label>Urutan</label>
                <input type="number" name="sort_order" class="form-control" value="0">
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

<!-- Edit Category Modal -->
<div class="modal fade" id="editCatModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="faqs-process.php" method="POST">
        <input type="hidden" name="action" value="edit_category">
        <input type="hidden" name="id" id="editCat_id">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Edit Kategori</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Nama Kategori</label>
                <input type="text" name="name" id="editCat_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Icon (Font Awesome)</label>
                <input type="text" name="icon" id="editCat_icon" class="form-control">
            </div>
            <div class="mb-2">
                <label>Urutan</label>
                <input type="number" name="sort_order" id="editCat_order" class="form-control">
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

<!-- Add FAQ Modal -->
<div class="modal fade" id="addFaqModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="faqs-process.php" method="POST">
        <input type="hidden" name="action" value="add_faq">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Tambah FAQ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Kategori</label>
                <select name="category_id" class="form-select" required>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Pertanyaan</label>
                <input type="text" name="question" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Jawaban</label>
                <textarea name="answer" class="form-control" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label>Kata Kunci (pisahkan dengan koma, untuk pencarian bebas di chatbot)</label>
                <input type="text" name="keywords" class="form-control" placeholder="lupa password, reset password, ganti password">
            </div>
            <div class="mb-2">
                <label>Urutan</label>
                <input type="number" name="sort_order" class="form-control" value="0">
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

<!-- Edit FAQ Modal -->
<div class="modal fade" id="editFaqModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="faqs-process.php" method="POST">
        <input type="hidden" name="action" value="edit_faq">
        <input type="hidden" name="id" id="editFaq_id">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Edit FAQ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Kategori</label>
                <select name="category_id" id="editFaq_category" class="form-select" required>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Pertanyaan</label>
                <input type="text" name="question" id="editFaq_question" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Jawaban</label>
                <textarea name="answer" id="editFaq_answer" class="form-control" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label>Kata Kunci</label>
                <input type="text" name="keywords" id="editFaq_keywords" class="form-control">
            </div>
            <div class="mb-2">
                <label>Urutan</label>
                <input type="number" name="sort_order" id="editFaq_order" class="form-control">
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
        document.getElementById('editCat_id').value = this.dataset.id;
        document.getElementById('editCat_name').value = this.dataset.name;
        document.getElementById('editCat_icon').value = this.dataset.icon;
        document.getElementById('editCat_order').value = this.dataset.order;
    });
});
document.querySelectorAll('.edit-faq').forEach(el => {
    el.addEventListener('click', function () {
        document.getElementById('editFaq_id').value = this.dataset.id;
        document.getElementById('editFaq_category').value = this.dataset.category;
        document.getElementById('editFaq_question').value = this.dataset.question;
        document.getElementById('editFaq_answer').value = this.dataset.answer;
        document.getElementById('editFaq_keywords').value = this.dataset.keywords;
        document.getElementById('editFaq_order').value = this.dataset.order;
    });
});
</script>

<?php include "templates/footer.php"; ?>
