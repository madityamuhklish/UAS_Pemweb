-- =====================================================================
-- SubsPilot — Migrasi Fitur Bantuan & Dukungan (FAQ + Chatbot)
-- Jalankan file ini setelah subspilot.sql pada database yang sama.
-- =====================================================================

-- --------------------------------------------------------
-- Table: faq_categories
-- Topik-topik yang tampil sebagai tombol pilihan cepat pada chatbot
-- dan sebagai grup pada halaman Pusat Bantuan.
-- --------------------------------------------------------
CREATE TABLE `faq_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'fa-circle-question',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `faq_categories` (`id`, `name`, `icon`, `sort_order`) VALUES
(1, 'Akun & Login', 'fa-user-lock', 1),
(2, 'Subscription', 'fa-credit-card', 2),
(3, 'Pembayaran', 'fa-money-check-dollar', 3),
(4, 'Reminder & Notifikasi', 'fa-bell', 4),
(5, 'Lainnya', 'fa-circle-question', 5);

-- --------------------------------------------------------
-- Table: faqs
-- Daftar pertanyaan & jawaban. `keywords` dipakai untuk pencocokan
-- saat pengguna mengetik bebas di chatbot (dipisah koma).
-- --------------------------------------------------------
CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `faqs` (`id`, `category_id`, `question`, `answer`, `keywords`, `sort_order`) VALUES
(1, 1, 'Bagaimana cara reset password?', 'Untuk saat ini reset password mandiri belum tersedia. Silakan hubungi CS kami melalui WhatsApp atau email agar dibantu mengganti password akun Anda.', 'lupa password,reset password,ganti password,gak bisa login', 1),
(2, 1, 'Kenapa saya tidak bisa login?', 'Pastikan email dan password yang dimasukkan sudah benar. Jika masih gagal, coba periksa Caps Lock atau hubungi CS untuk pengecekan akun.', 'tidak bisa login,gagal login,login error', 2),
(3, 1, 'Bagaimana cara mengubah foto profil?', 'Buka menu Profile, klik foto Anda, lalu unggah foto baru dan simpan perubahan.', 'ganti foto,ubah profil,edit profil', 3),
(4, 2, 'Bagaimana cara menambah subscription baru?', 'Buka menu Subscription, klik tombol "Tambah Subscription", lalu isi nama layanan, kategori, harga, dan tanggal pembayaran berikutnya.', 'tambah subscription,tambah langganan,buat subscription baru', 1),
(5, 2, 'Bagaimana cara membatalkan atau menghentikan subscription?', 'Buka menu Subscription, pilih subscription yang ingin dihentikan, lalu ubah statusnya menjadi "Cancelled" atau "Paused".', 'batalkan subscription,berhenti langganan,cancel subscription,stop langganan', 2),
(6, 2, 'Apa bedanya status Active, Paused, dan Cancelled?', 'Active berarti subscription masih berjalan dan akan ditagih. Paused berarti sementara dihentikan tanpa dihapus datanya. Cancelled berarti subscription sudah tidak digunakan lagi.', 'status active,status paused,status cancelled,arti status', 3),
(7, 3, 'Bagaimana cara melihat riwayat pembayaran?', 'Buka menu Payment untuk melihat seluruh riwayat pembayaran dari semua subscription Anda beserta statusnya.', 'riwayat pembayaran,history payment,cek pembayaran', 1),
(8, 3, 'Metode pembayaran apa saja yang bisa dicatat?', 'Anda bisa mencatat metode pembayaran apa pun (kartu, e-wallet, transfer bank) melalui menu Payment sesuai kebutuhan pencatatan Anda.', 'metode pembayaran,cara bayar,payment method', 2),
(9, 4, 'Bagaimana cara mengatur reminder pembayaran?', 'Buka menu Reminder, klik "Tambah Reminder", pilih subscription terkait, tentukan tanggal dan tipe reminder (H-7, H-3, H-1, atau Today).', 'atur reminder,tambah reminder,pengingat pembayaran', 1),
(10, 4, 'Kenapa saya tidak menerima notifikasi?', 'Notifikasi tagihan mendatang tampil otomatis di ikon lonceng pada navbar untuk pembayaran dalam 7 hari ke depan. Pastikan reminder sudah dibuat pada subscription terkait.', 'tidak ada notifikasi,notif tidak muncul,pengingat tidak jalan', 2),
(11, 5, 'Bagaimana cara menghubungi customer service?', 'Anda bisa menghubungi tim CS kami melalui WhatsApp atau email yang tertera pada menu Bantuan, atau melalui chatbot ini dengan menekan tombol "Hubungi CS".', 'hubungi cs,kontak cs,customer service,bantuan lainnya', 1),
(12, 5, 'Apakah data saya aman?', 'Data Anda disimpan dengan aman di server kami dan hanya dapat diakses oleh akun Anda sendiri serta admin untuk keperluan dukungan teknis.', 'keamanan data,data aman,privasi', 2);

-- --------------------------------------------------------
-- Table: support_settings
-- Satu baris konfigurasi kontak CS, dapat diubah admin.
-- --------------------------------------------------------
CREATE TABLE `support_settings` (
  `id` int(11) NOT NULL,
  `whatsapp` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `operational_hours` varchar(150) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `support_settings` (`id`, `whatsapp`, `email`, `operational_hours`) VALUES
(1, '628123456789', 'cs@subspilot.app', 'Setiap hari, 09:00 - 21:00 WIB');

--
-- Indexes / primary keys
--
ALTER TABLE `faq_categories` ADD PRIMARY KEY (`id`);
ALTER TABLE `faq_categories` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);
ALTER TABLE `faqs` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
ALTER TABLE `faqs`
  ADD CONSTRAINT `faqs_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `faq_categories` (`id`) ON DELETE CASCADE;

ALTER TABLE `support_settings` ADD PRIMARY KEY (`id`);
ALTER TABLE `support_settings` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
