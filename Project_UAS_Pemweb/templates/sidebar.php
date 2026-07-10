<?php
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
function navActive($dir, $currentDir){
    return $dir === $currentDir ? 'active' : '';
}
?>
<div class="sidebar-overlay"></div>
<div class="sidebar">

    <div class="brand">
        <i class="fa-solid fa-wallet"></i>
        <span>SubsPilot</span>
    </div>

    <ul class="menu">

        <li>
            <a href="../dashboard/index.php" class="<?= navActive('dashboard', $currentDir) ?>">
                <i class="fa-solid fa-house"></i>
                Dashboard
            </a>
        </li>

        <li>
            <a href="../subscription/index.php" class="<?= navActive('subscription', $currentDir) ?>">
                <i class="fa-solid fa-credit-card"></i>
                Subscription
            </a>
        </li>

        <li>
            <a href="../category/index.php" class="<?= navActive('category', $currentDir) ?>">
                <i class="fa-solid fa-folder"></i>
                Category
            </a>
        </li>

        <li>
            <a href="../payment/index.php" class="<?= navActive('payment', $currentDir) ?>">
                <i class="fa-solid fa-money-check-dollar"></i>
                Payment
            </a>
        </li>

        <li>
            <a href="../reminder/index.php" class="<?= navActive('reminder', $currentDir) ?>">
                <i class="fa-solid fa-bell"></i>
                Reminder
            </a>
        </li>

        <li>
            <a href="../wishlist/index.php" class="<?= navActive('wishlist', $currentDir) ?>">
                <i class="fa-solid fa-heart"></i>
                Wishlist
            </a>
        </li>

        <li>
            <a href="../report/index.php" class="<?= navActive('report', $currentDir) ?>">
                <i class="fa-solid fa-chart-column"></i>
                Reports
            </a>
        </li>

        <li>
            <a href="../profile/index.php" class="<?= navActive('profile', $currentDir) ?>">
                <i class="fa-solid fa-user"></i>
                Profile
            </a>
        </li>

        <li>
            <a href="../support/index.php" class="<?= navActive('support', $currentDir) ?>">
                <i class="fa-solid fa-circle-question"></i>
                Bantuan
            </a>
        </li>

    </ul>

    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
    <div class="px-2 pb-2">
        <a href="../admin/login.php" class="btn btn-sm w-100" style="background:#EEF2FF;color:#4F46E5;border-radius:12px;font-weight:600;">
            <i class="fa-solid fa-shield-halved me-1"></i> Buka Admin Panel
        </a>
    </div>
    <?php endif; ?>

    <div class="logout">
        <a href="../auth/logout.php">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </a>
    </div>

</div>
