<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/helpers.php";

$db = new Database();
$conn = $db->connect();

checkLogin($conn);

$categories = $conn->query("SELECT * FROM faq_categories ORDER BY sort_order, name")->fetchAll();

$faqStmt = $conn->query("
    SELECT * FROM faqs
    WHERE is_active = 1
    ORDER BY category_id, sort_order, id
");
$allFaqs = $faqStmt->fetchAll();

$faqsByCategory = [];
foreach ($allFaqs as $f) {
    $faqsByCategory[$f['category_id']][] = $f;
}

$settingsStmt = $conn->query("SELECT * FROM support_settings LIMIT 1");
$settings = $settingsStmt->fetch() ?: [];

$pageTitle = "Bantuan";
include "../templates/header.php";
include "../templates/sidebar.php";
include "../templates/navbar.php";
?>

<div class="main-content">
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="page-heading">
            <div class="page-icon"><i class="fa-solid fa-circle-question"></i></div>
            <div>
                <h2 class="title mb-0">Pusat Bantuan</h2>
                <p class="subtitle mb-0">Cari jawaban, atau chat langsung dengan asisten kami</p>
            </div>
        </div>
        <button class="btn btn-primary px-4 py-2" type="button" onclick="if(window.SubsBot) SubsBot.open();">
            <i class="fa-solid fa-comment-dots me-2"></i>Chat dengan Bot
        </button>
    </div>

    <div class="content-card p-4 mb-4 fade-up">
        <div class="search-box w-100" style="max-width:100%;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="faqSearch" placeholder="Cari pertanyaan, misal: cara batalkan subscription...">
        </div>
    </div>

    <div id="faqEmptyState" class="empty-state d-none">
        <i class="fa-solid fa-magnifying-glass"></i>
        <p class="mb-0">Tidak ada FAQ yang cocok dengan pencarian Anda.</p>
    </div>

    <?php foreach ($categories as $cat): ?>
        <?php $items = $faqsByCategory[$cat['id']] ?? []; if (empty($items)) continue; ?>
        <div class="content-card p-4 mb-3 fade-up faq-category-block" data-cat-name="<?= htmlspecialchars(strtolower($cat['name'])) ?>">
            <h6 class="fw-bold mb-3">
                <i class="fa-solid <?= htmlspecialchars($cat['icon']) ?> me-2" style="color:#6366F1;"></i>
                <?= htmlspecialchars($cat['name']) ?>
            </h6>
            <div class="accordion" id="acc-<?= $cat['id'] ?>">
                <?php foreach ($items as $f): ?>
                <div class="accordion-item faq-item" data-question="<?= htmlspecialchars(strtolower($f['question'])) ?>">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-<?= $f['id'] ?>">
                            <?= htmlspecialchars($f['question']) ?>
                        </button>
                    </h2>
                    <div id="faq-<?= $f['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#acc-<?= $cat['id'] ?>">
                        <div class="accordion-body">
                            <?= nl2br(htmlspecialchars($f['answer'])) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="content-card p-4 fade-up d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h6 class="fw-bold mb-1"><i class="fa-solid fa-headset me-2" style="color:#6366F1;"></i>Masih butuh bantuan?</h6>
            <p class="text-muted mb-0">Tim CS kami siap membantu <?= htmlspecialchars($settings['operational_hours'] ?? '') ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php if (!empty($settings['whatsapp'])): ?>
            <a class="btn btn-success rounded-pill px-4" target="_blank"
               href="https://wa.me/<?= htmlspecialchars(preg_replace('/\D/', '', $settings['whatsapp'])) ?>?text=<?= urlencode('Halo CS SubsPilot, saya butuh bantuan.') ?>">
                <i class="fa-brands fa-whatsapp me-2"></i>WhatsApp
            </a>
            <?php endif; ?>
            <?php if (!empty($settings['email'])): ?>
            <a class="btn btn-outline-primary rounded-pill px-4" href="mailto:<?= htmlspecialchars($settings['email']) ?>">
                <i class="fa-solid fa-envelope me-2"></i>Email
            </a>
            <?php endif; ?>
        </div>
    </div>

</div>
</div>

<script>
(function(){
    const search = document.getElementById('faqSearch');
    if (!search) return;
    search.addEventListener('input', function(){
        const q = this.value.trim().toLowerCase();
        let anyVisible = false;
        document.querySelectorAll('.faq-category-block').forEach(block => {
            let blockHasVisible = false;
            block.querySelectorAll('.faq-item').forEach(item => {
                const match = q === '' || item.dataset.question.includes(q);
                item.classList.toggle('d-none', !match);
                if (match) blockHasVisible = true;
            });
            block.classList.toggle('d-none', !blockHasVisible);
            if (blockHasVisible) anyVisible = true;
        });
        document.getElementById('faqEmptyState').classList.toggle('d-none', anyVisible || q === '');
    });
})();
</script>

<?php include "../templates/footer.php"; ?>
