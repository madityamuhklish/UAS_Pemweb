<?php
/* Fetch upcoming reminders (next 7 days) for the notification dropdown */
$__notifItems = [];
if (isset($_SESSION['user_id'])) {
    try {
        if (!isset($conn)) {
            require_once __DIR__ . "/../config/database.php";
            $__db = new Database();
            $conn = $__db->connect();
        }
        $__uid = $_SESSION['user_id'];
        $__stmt = $conn->prepare("
            SELECT service_name, amount, next_payment,
                   DATEDIFF(next_payment, CURDATE()) AS days_left
            FROM subscriptions
            WHERE user_id = ?
              AND status = 'Active'
              AND next_payment BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ORDER BY next_payment ASC
            LIMIT 6
        ");
        $__stmt->execute([$__uid]);
        $__notifItems = $__stmt->fetchAll();
    } catch (Throwable $e) {
        $__notifItems = [];
    }
}
?>
<nav class="navbar-custom">

    <div class="navbar-left">

        <button class="menu-btn" type="button">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Cari Subscription...">
        </div>

    </div>

    <div class="navbar-right">

        <button class="theme-toggle" id="themeToggle" type="button" title="Ganti Tema">
            <i class="fa-solid <?= !empty($_SESSION['dark_mode']) ? 'fa-sun' : 'fa-moon' ?>" id="themeIcon"></i>
        </button>

        <button class="icon-btn" type="button" title="Notifikasi" id="notifBtn">
            <i class="fa-regular fa-bell"></i>
            <?php if (count($__notifItems) > 0): ?>
                <span class="notif-dot"><?= count($__notifItems) ?></span>
            <?php endif; ?>
        </button>

        <div class="notif-panel" id="notifPanel">
            <h6>Pembayaran Mendatang (7 hari)</h6>
            <?php if (count($__notifItems) > 0): foreach ($__notifItems as $n): ?>
                <a href="../subscription/index.php" class="notif-item">
                    <div class="notif-icon"><i class="fa-solid fa-bell"></i></div>
                    <div>
                        <strong style="font-size:14px;"><?= htmlspecialchars($n['service_name']) ?></strong>
                        <div class="text-muted" style="font-size:12.5px;">
                            Rp <?= number_format($n['amount'], 0, ',', '.') ?> &middot;
                            <?= $n['days_left'] == 0 ? 'Hari ini' : ($n['days_left'] == 1 ? 'Besok' : $n['days_left'] . ' hari lagi') ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; else: ?>
                <div class="notif-empty">
                    <i class="fa-regular fa-circle-check mb-2 d-block" style="font-size:22px;"></i>
                    Tidak ada tagihan dalam 7 hari ke depan.
                </div>
            <?php endif; ?>
        </div>

        <a href="../profile/index.php" class="profile text-decoration-none">
            <img src="../assets/uploads/<?= htmlspecialchars($_SESSION['photo'] ?? 'default.png') ?>"
                 alt="Foto profil <?= htmlspecialchars($_SESSION['fullname'] ?? '') ?>"
                 onerror="this.onerror=null;this.src='../assets/img/default.png'">
            <div>
                <h6><?= htmlspecialchars($_SESSION['fullname'] ?? '') ?></h6>
                <small><?= ucfirst($_SESSION['role'] ?? 'user') ?></small>
            </div>
        </a>

    </div>

</nav>

<script>
(function(){
    const notifBtn = document.getElementById('notifBtn');
    const notifPanel = document.getElementById('notifPanel');
    if (notifBtn && notifPanel) {
        notifBtn.addEventListener('click', function(e){
            e.stopPropagation();
            notifPanel.classList.toggle('show');
        });
        document.addEventListener('click', function(e){
            if (!notifPanel.contains(e.target) && e.target !== notifBtn) {
                notifPanel.classList.remove('show');
            }
        });
    }

    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    if (themeToggle) {
        themeToggle.addEventListener('click', function(){
            const isDark = document.body.classList.toggle('dark-mode');
            themeIcon.classList.toggle('fa-moon', !isDark);
            themeIcon.classList.toggle('fa-sun', isDark);

            fetch('../config/toggle-theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'dark_mode=' + (isDark ? '1' : '0')
            }).catch(() => {});
        });
    }
})();
</script>
