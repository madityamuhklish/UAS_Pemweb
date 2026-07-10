# Fitur Bantuan & Dukungan (QnA + Chatbot)

## 1. Instalasi Database
Jalankan **berurutan**:
1. `database/support_migration.sql` — wajib, membuat 3 tabel inti (`faq_categories`, `faqs`, `support_settings`) beserta data contoh.
2. `database/support_migration_extra.sql` — opsional, menambah lebih banyak variasi FAQ.

> **Troubleshooting:** jika widget chatbot menampilkan "Maaf, terjadi kendala jaringan" atau "gagal memuat topik bantuan" pada semua aksi (baik saat membuka menu maupun mengetik bebas), penyebab paling umum adalah tabel di atas **belum ter-import**. Setelah perbaikan terbaru, endpoint `support/chatbot-process.php` sekarang mendeteksi hal ini dan akan menampilkan pesan yang jelas: *"Tabel FAQ belum tersedia di database. Silakan import file database/support_migration.sql terlebih dahulu."*

## 2. File Baru
- `support/index.php` — Halaman "Pusat Bantuan" (daftar FAQ per kategori + pencarian), link ditambahkan di sidebar user.
- `support/chatbot-process.php` — Endpoint AJAX chatbot (menu kategori, pertanyaan per kategori, jawaban, pencarian bebas, kontak CS).
- `templates/chatbot-widget.php` — Widget bubble chat mengambang, otomatis muncul di semua halaman untuk user yang sudah login (di-include dari `templates/footer.php`).
- `assets/css/chatbot.css`, `assets/js/chatbot.js` — Tampilan & logika widget chatbot.
- `admin/faqs.php`, `admin/faqs-process.php` — Panel admin untuk CRUD kategori & FAQ, serta mengubah kontak CS. Link ditambahkan di sidebar admin ("FAQ & Bantuan").

## 3. Cara Kerja Chatbot
1. User klik ikon chat di pojok kanan bawah.
2. Bot menampilkan menu kategori sebagai tombol pilihan cepat (mis. Akun & Login, Subscription, Pembayaran, dst).
3. User bisa **klik tombol** untuk menelusuri pertanyaan per kategori sampai menemukan jawaban, **atau ketik bebas** — sistem akan mencocokkan teks dengan kolom `keywords`/`question` pada tabel `faqs` dan menampilkan jawaban terbaik.
4. Jika tidak ada yang cocok, bot menampilkan tombol "Hubungi CS" yang mengarahkan ke WhatsApp/email sesuai data di `support_settings`.

## 4. Kelola Konten
Login sebagai admin → menu **FAQ & Bantuan**:
- Ubah nomor WhatsApp / email / jam operasional CS.
- Tambah/edit/hapus kategori topik.
- Tambah/edit/hapus/nonaktifkan pertanyaan & jawaban, termasuk kata kunci pencocokan.

Tidak ada sistem tiket — jika chatbot tidak menemukan jawaban, user diarahkan langsung ke kontak CS (WhatsApp/email).
