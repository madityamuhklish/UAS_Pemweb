
</div><!-- /.admin-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($needsChart)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function setSubmitLoading(form) {
        const btn = form.querySelector('button[type="submit"]');
        if (!btn || btn.disabled) return;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memproses...';
    }

    document.querySelectorAll('.confirm-delete').forEach(form => {
        form.addEventListener('submit', function (e) {
            const msg = form.getAttribute('data-confirm') || 'Yakin ingin melakukan aksi ini?';
            if (!confirm(msg)) {
                e.preventDefault();
                return;
            }
            setSubmitLoading(form);
        });
    });

    document.querySelectorAll('form:not(.confirm-delete)').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;
            setSubmitLoading(form);
        });
    });

    const adminThemeToggle = document.getElementById('adminThemeToggle');
    const adminThemeIcon = document.getElementById('adminThemeIcon');
    if (adminThemeToggle) {
        adminThemeToggle.addEventListener('click', function () {
            const isLight = document.body.classList.toggle('admin-light');
            adminThemeIcon.classList.toggle('fa-sun', !isLight);
            adminThemeIcon.classList.toggle('fa-moon', isLight);

            fetch('toggle-theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'theme=' + (isLight ? 'light' : 'dark')
            }).catch(() => {});
        });
    }

    document.querySelectorAll('.toast-notif').forEach(toastEl => {
        const dismiss = () => {
            toastEl.classList.add('toast-out');
            setTimeout(() => toastEl.remove(), 350);
        };
        const timer = setTimeout(dismiss, 3500);
        const closeBtn = toastEl.querySelector('.toast-notif-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                clearTimeout(timer);
                dismiss();
            });
        }
    });
});
</script>

</body>
</html>
