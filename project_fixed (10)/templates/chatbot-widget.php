<!-- Chatbot Bantuan (widget mengambang) -->
<div class="chatbot-launcher" id="chatbotLauncher" title="Butuh bantuan?">
    <i class="fa-solid fa-comment-dots"></i>
</div>

<div class="chatbot-panel" id="chatbotPanel">

    <div class="chatbot-header">
        <div class="d-flex align-items-center gap-2">
            <div class="chatbot-avatar"><i class="fa-solid fa-headset"></i></div>
            <div>
                <div class="chatbot-title">Asisten SubsPilot</div>
                <div class="chatbot-subtitle"><span class="chatbot-dot"></span> Online</div>
            </div>
        </div>
        <button type="button" class="chatbot-close" id="chatbotClose" aria-label="Tutup">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="chatbot-body" id="chatbotBody">
        <!-- messages injected by JS -->
    </div>

    <form class="chatbot-input" id="chatbotForm" autocomplete="off">
        <input type="text" id="chatbotInput" placeholder="Ketik pertanyaan Anda..." maxlength="255">
        <button type="submit" aria-label="Kirim">
            <i class="fa-solid fa-paper-plane"></i>
        </button>
    </form>

</div>

<script src="../assets/js/chatbot.js?v=20260710a"></script>
