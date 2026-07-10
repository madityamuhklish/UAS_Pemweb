/* ===========================================
   SubsPilot — Chatbot Bantuan Widget
   Tombol pilihan cepat + input teks bebas,
   dijawab lewat support/chatbot-process.php
=========================================== */
(function () {
    "use strict";

    const launcher = document.getElementById("chatbotLauncher");
    const panel = document.getElementById("chatbotPanel");
    const closeBtn = document.getElementById("chatbotClose");
    const body = document.getElementById("chatbotBody");
    const form = document.getElementById("chatbotForm");
    const input = document.getElementById("chatbotInput");

    if (!launcher || !panel) return;

    const ENDPOINT = "../support/chatbot-process.php";
    let started = false;

    function scrollToBottom() {
        body.scrollTop = body.scrollHeight;
    }

    function addBotMessage(text) {
        const el = document.createElement("div");
        el.className = "chatbot-msg bot";
        el.textContent = text;
        body.appendChild(el);
        scrollToBottom();
        return el;
    }

    function addUserMessage(text) {
        const el = document.createElement("div");
        el.className = "chatbot-msg user";
        el.textContent = text;
        body.appendChild(el);
        scrollToBottom();
    }

    function showTyping() {
        const el = document.createElement("div");
        el.className = "chatbot-typing";
        el.id = "chatbotTypingIndicator";
        el.innerHTML = "<span></span><span></span><span></span>";
        body.appendChild(el);
        scrollToBottom();
        return el;
    }

    function hideTyping() {
        const el = document.getElementById("chatbotTypingIndicator");
        if (el) el.remove();
    }

    function addOptions(options) {
        const wrap = document.createElement("div");
        wrap.className = "chatbot-options";
        options.forEach(opt => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "chatbot-option-btn";
            btn.innerHTML = (opt.icon ? `<i class="fa-solid ${opt.icon}"></i>` : "") + escapeHtml(opt.label);
            btn.addEventListener("click", () => opt.onClick(opt));
            wrap.appendChild(btn);
        });
        body.appendChild(wrap);
        scrollToBottom();
        return wrap;
    }

    function escapeHtml(str) {
        const d = document.createElement("div");
        d.textContent = str;
        return d.innerHTML;
    }

    function clearBody() {
        body.innerHTML = "";
    }

    async function callApi(params) {
        const formData = new URLSearchParams(params);
        const res = await fetch(ENDPOINT, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData.toString(),
        });
        let data = null;
        try {
            data = await res.json();
        } catch (e) {
            throw new Error("Respon server tidak valid.");
        }
        if (!res.ok || data.ok === false) {
            throw new Error(data && data.message ? data.message : "Request gagal.");
        }
        return data;
    }

    function showMainMenuButton() {
        addOptions([
            { label: "Kembali ke menu utama", icon: "fa-list", onClick: () => { clearBody(); startConversation(); } },
        ]);
    }

    async function showContact() {
        showTyping();
        try {
            const data = await callApi({ action: "contact" });
            hideTyping();
            const c = data.contact || {};
            const el = addBotMessage("Anda bisa langsung menghubungi tim CS kami:");
            const linkWrap = document.createElement("div");
            linkWrap.className = "d-flex flex-wrap gap-2";
            if (c.whatsapp) {
                const wa = document.createElement("a");
                wa.href = `https://wa.me/${c.whatsapp.replace(/\D/g, "")}?text=${encodeURIComponent("Halo CS SubsPilot, saya butuh bantuan.")}`;
                wa.target = "_blank";
                wa.className = "chatbot-contact-btn wa";
                wa.innerHTML = '<i class="fa-brands fa-whatsapp"></i> WhatsApp';
                linkWrap.appendChild(wa);
            }
            if (c.email) {
                const mail = document.createElement("a");
                mail.href = `mailto:${c.email}`;
                mail.className = "chatbot-contact-btn mail";
                mail.innerHTML = '<i class="fa-solid fa-envelope"></i> Email';
                linkWrap.appendChild(mail);
            }
            body.appendChild(linkWrap);
            scrollToBottom();
            if (c.operational_hours) {
                addBotMessage("Jam operasional: " + c.operational_hours);
            }
        } catch (e) {
            hideTyping();
            addBotMessage(e.message || "Maaf, gagal memuat info kontak. Coba lagi sebentar lagi.");
        }
        showMainMenuButton();
    }

    async function openCategory(cat) {
        addUserMessage(cat.name);
        showTyping();
        try {
            const data = await callApi({ action: "category", category_id: cat.id });
            hideTyping();
            const questions = data.questions || [];
            if (questions.length === 0) {
                addBotMessage("Belum ada pertanyaan pada topik ini.");
            } else {
                addBotMessage(`Berikut pertanyaan seputar "${cat.name}":`);
                addOptions(questions.map(q => ({
                    label: q.label,
                    icon: "fa-circle-question",
                    onClick: () => openAnswer(q),
                })));
            }
        } catch (e) {
            hideTyping();
            addBotMessage(e.message || "Maaf, terjadi kendala memuat pertanyaan. Coba lagi ya.");
        }
        addOptions([
            { label: "Hubungi CS", icon: "fa-headset", onClick: showContact },
            { label: "Kembali ke menu utama", icon: "fa-list", onClick: () => { clearBody(); startConversation(); } },
        ]);
    }

    async function openAnswer(q) {
        addUserMessage(q.label);
        showTyping();
        try {
            const data = await callApi({ action: "answer", faq_id: q.id });
            hideTyping();
            if (data.found) {
                addBotMessage(data.answer);
            } else {
                addBotMessage("Maaf, jawaban tidak ditemukan.");
            }
        } catch (e) {
            hideTyping();
            addBotMessage(e.message || "Maaf, terjadi kendala. Coba lagi ya.");
        }
        addOptions([
            { label: "Ada pertanyaan lain", icon: "fa-comment", onClick: () => { clearBody(); startConversation(); } },
            { label: "Hubungi CS", icon: "fa-headset", onClick: showContact },
        ]);
    }

    async function handleFreeText(text) {
        addUserMessage(text);
        showTyping();
        try {
            const data = await callApi({ action: "ask", message: text });
            hideTyping();
            if (data.found) {
                addBotMessage(data.answer);
                addOptions([
                    { label: "Ada pertanyaan lain", icon: "fa-comment", onClick: () => { clearBody(); startConversation(); } },
                    { label: "Hubungi CS", icon: "fa-headset", onClick: showContact },
                ]);
            } else {
                addBotMessage("Maaf, saya belum menemukan jawaban yang cocok. Coba pilih topik di bawah, atau hubungi CS kami langsung.");
                showMainMenuButton();
                addOptions([{ label: "Hubungi CS", icon: "fa-headset", onClick: showContact }]);
            }
        } catch (e) {
            hideTyping();
            addBotMessage(e.message || "Maaf, terjadi kendala jaringan. Coba lagi ya.");
        }
    }

    async function startConversation() {
        addBotMessage("Halo! 👋 Saya asisten bantuan SubsPilot. Pilih topik di bawah, atau ketik langsung pertanyaan Anda.");
        showTyping();
        try {
            const data = await callApi({ action: "menu" });
            hideTyping();
            const cats = data.categories || [];
            addOptions(cats.map(c => ({
                label: c.name,
                icon: c.icon || "fa-circle-question",
                onClick: () => openCategory(c),
            })));
        } catch (e) {
            hideTyping();
            addBotMessage(e.message || "Maaf, gagal memuat topik bantuan. Anda tetap bisa mengetik pertanyaan langsung.");
        }
    }

    function openPanel() {
        panel.classList.add("show");
        if (!started) {
            started = true;
            startConversation();
        }
        setTimeout(() => input && input.focus(), 200);
    }

    function closePanel() {
        panel.classList.remove("show");
    }

    launcher.addEventListener("click", () => {
        if (panel.classList.contains("show")) {
            closePanel();
        } else {
            openPanel();
        }
    });

    closeBtn && closeBtn.addEventListener("click", closePanel);

    document.addEventListener("click", function (e) {
        if (!panel.contains(e.target) && !launcher.contains(e.target)) {
            closePanel();
        }
    });
    panel.addEventListener("click", e => e.stopPropagation());
    launcher.addEventListener("click", e => e.stopPropagation());

    form && form.addEventListener("submit", function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        input.value = "";
        handleFreeText(text);
    });

    // Expose a small API so other pages (e.g. support/index.php) can open the widget.
    window.SubsBot = {
        open: openPanel,
        close: closePanel,
    };
})();
