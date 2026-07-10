<nav class="admin-topbar">
    <h4><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?></h4>

    <div class="admin-user">
        <button class="theme-toggle" id="adminThemeToggle" type="button" title="Ganti Tema">
            <i class="fa-solid <?= (($_SESSION['admin_theme'] ?? 'dark') === 'light') ? 'fa-moon' : 'fa-sun' ?>" id="adminThemeIcon"></i>
        </button>
        <span class="admin-badge-role"><i class="fa-solid fa-user-shield me-1"></i> Administrator</span>
        <div class="admin-avatar">
            <?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?>
        </div>
        <div>
            <div class="text-a-white-strong" style="font-size:14px;"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></div>
            <div style="font-size:12px;color:var(--a-muted);"><?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?></div>
        </div>
    </div>
</nav>
