<?php
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
function adminNavActive($page, $currentPage) {
    return $page === $currentPage ? 'active' : '';
}
?>
<div class="admin-sidebar" id="adminSidebar">

    <div class="brand">
        <i class="fa-solid fa-shield-halved"></i>
        <div>
            SubsPilot
            <small>ADMIN PANEL</small>
        </div>
    </div>

    <ul class="admin-menu">
        <li>
            <a href="index.php" class="<?= adminNavActive('index', $currentPage) ?>">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="users.php" class="<?= adminNavActive('users', $currentPage) ?>">
                <i class="fa-solid fa-users"></i> Kelola Pengguna
            </a>
        </li>
        <li>
            <a href="subscriptions.php" class="<?= adminNavActive('subscriptions', $currentPage) ?>">
                <i class="fa-solid fa-credit-card"></i> Semua Subscription
            </a>
        </li>
        <li>
            <a href="categories.php" class="<?= adminNavActive('categories', $currentPage) ?>">
                <i class="fa-solid fa-folder"></i> Kategori
            </a>
        </li>
        <li>
            <a href="payment-methods.php" class="<?= adminNavActive('payment-methods', $currentPage) ?>">
                <i class="fa-solid fa-money-check-dollar"></i> Metode Pembayaran
            </a>
        </li>
        <li>
            <a href="activity-log.php" class="<?= adminNavActive('activity-log', $currentPage) ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> Activity Log
            </a>
        </li>
        <li>
            <a href="faqs.php" class="<?= adminNavActive('faqs', $currentPage) ?>">
                <i class="fa-solid fa-circle-question"></i> FAQ &amp; Bantuan
            </a>
        </li>
    </ul>

    <div class="admin-logout">
        <a href="logout.php">
            <i class="fa-solid fa-right-from-bracket"></i> Logout Admin
        </a>
    </div>

</div>
