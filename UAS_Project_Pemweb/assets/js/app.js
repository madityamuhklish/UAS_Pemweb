/* ===========================================
   SubsPilot — Global App Script
=========================================== */
document.addEventListener("DOMContentLoaded", function () {

    /* ---------- Page loader ---------- */
    const loader = document.querySelector(".page-loader");
    if (loader) {
        // Hide as soon as the DOM/content is ready instead of waiting for
        // window "load" (which blocks on every image/font finishing), so
        // page-to-page navigation doesn't feel artificially slow.
        loader.classList.add("hide");
    }

    /* ---------- Sidebar toggle (desktop collapse / mobile overlay) ---------- */
    const menuBtn = document.querySelector(".menu-btn");
    const overlay = document.querySelector(".sidebar-overlay");

    function toggleSidebar() {
        document.body.classList.toggle("sidebar-toggled");
    }

    function closeSidebar() {
        document.body.classList.remove("sidebar-toggled");
    }

    if (menuBtn) menuBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        toggleSidebar();
    });
    if (overlay) overlay.addEventListener("click", closeSidebar);

    /* ---------- Active nav link (auto highlight) ---------- */
    const currentPath = window.location.pathname.split("/").filter(Boolean);
    const currentFolder = currentPath[currentPath.length - 2] || "dashboard";
    document.querySelectorAll(".menu a").forEach(link => {
        link.classList.remove("active");
        const href = link.getAttribute("href") || "";
        if (href.includes("/" + currentFolder + "/")) {
            link.classList.add("active");
        }
    });

    /* ---------- Scroll reveal ---------- */
    const revealEls = document.querySelectorAll(".reveal");
    if ("IntersectionObserver" in window && revealEls.length) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("in-view");
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        revealEls.forEach(el => io.observe(el));
    } else {
        revealEls.forEach(el => el.classList.add("in-view"));
    }

    /* ---------- Counter animation ---------- */
    document.querySelectorAll("[data-counter]").forEach(el => {
        const target = parseFloat(el.getAttribute("data-counter")) || 0;
        const isMoney = el.hasAttribute("data-money");
        const duration = 900;
        const start = performance.now();

        function tick(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = target * eased;
            el.textContent = isMoney
                ? "Rp " + Math.round(value).toLocaleString("id-ID")
                : Math.round(value).toLocaleString("id-ID");
            if (progress < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    });

    /* ---------- Ripple effect on buttons ---------- */
    document.querySelectorAll(".btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            const rect = btn.getBoundingClientRect();
            const ripple = document.createElement("span");
            const size = Math.max(rect.width, rect.height);
            ripple.className = "ripple";
            ripple.style.width = ripple.style.height = size + "px";
            ripple.style.left = (e.clientX - rect.left - size / 2) + "px";
            ripple.style.top = (e.clientY - rect.top - size / 2) + "px";
            btn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });

    /* ---------- Auto-dismiss toast notification ---------- */
    document.querySelectorAll(".toast-notif").forEach(toastEl => {
        const dismiss = () => {
            toastEl.classList.add("toast-out");
            setTimeout(() => toastEl.remove(), 350);
        };
        const timer = setTimeout(dismiss, 3500);
        const closeBtn = toastEl.querySelector(".toast-notif-close");
        if (closeBtn) {
            closeBtn.addEventListener("click", () => {
                clearTimeout(timer);
                dismiss();
            });
        }
    });

    /* ---------- Delete confirmation ---------- */
    document.querySelectorAll(".confirm-delete").forEach(form => {
        form.addEventListener("submit", function (e) {
            const msg = form.getAttribute("data-confirm") || "Yakin ingin menghapus data ini?";
            if (!confirm(msg)) {
                e.preventDefault();
                return;
            }
            setSubmitLoading(form);
        });
    });

    /* ---------- Submit loading state (all other forms: add/edit/convert, etc.) ---------- */
    document.querySelectorAll("form:not(.confirm-delete)").forEach(form => {
        form.addEventListener("submit", function (e) {
            if (e.defaultPrevented) return;
            setSubmitLoading(form);
        });
    });

    function setSubmitLoading(form) {
        const btn = form.querySelector('button[type="submit"]');
        if (!btn || btn.disabled) return;
        btn.dataset.originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memproses...';
    }

    /* ---------- Modal backdrop safety net ----------
       Fixes: modal appears (screen darkens) but nothing is clickable
       (Cancel/Simpan/X unresponsive). Root cause: a leftover/duplicate
       .modal-backdrop element (or a stuck "modal-open" state on <body>)
       ends up stacked above the modal, silently swallowing all clicks. */
    document.addEventListener('show.bs.modal', function () {
        // Remove any stray backdrops from a previous modal that failed
        // to clean up before opening a new one.
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    });

    document.addEventListener('shown.bs.modal', function () {
        // If more than one backdrop ever exists, keep only the last one
        // (the one actually paired with the currently open modal).
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 1) {
            backdrops.forEach((el, i) => { if (i < backdrops.length - 1) el.remove(); });
        }
    });

    document.addEventListener('hidden.bs.modal', function () {
        // Only fully reset if no other modal is still open.
        if (!document.querySelector('.modal.show')) {
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        }
    });

    /* ---------- Live search filter (client side) ---------- */
    const searchInput = document.querySelector(".search-box input");
    const searchTarget = document.querySelector("[data-search-target]");
    if (searchInput && searchTarget) {
        searchInput.addEventListener("input", function () {
            const q = this.value.trim().toLowerCase();
            const rows = searchTarget.querySelectorAll("[data-search-row]");
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? "" : "none";
            });
        });
    }
});

/* ---------- Simple toast helper (available globally) ---------- */
function showToast(message, type = "success") {
    const el = document.createElement("div");
    el.className = "toast-custom " + type;
    const icon = type === "success" ? "fa-circle-check" : "fa-circle-exclamation";
    el.innerHTML = `<i class="fa-solid ${icon}"></i><span>${message}</span>`;
    document.body.appendChild(el);
    setTimeout(() => {
        el.style.opacity = "0";
        el.style.transform = "translateX(20px)";
        el.style.transition = ".35s ease";
        setTimeout(() => el.remove(), 350);
    }, 2800);
}
