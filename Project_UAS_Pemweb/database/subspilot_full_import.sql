-- =====================================================================
-- SubsPilot - Gabungan semua file SQL untuk import ke database eksternal
-- (Railway/Aiven/Clever Cloud) saat deploy ke Vercel.
-- Urutan: subspilot.sql -> admin_seed.sql -> support_migration.sql ->
--         support_migration_extra.sql -> vercel_sessions.sql
-- =====================================================================

-- ===== 1. subspilot.sql (struktur utama) =====
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 09, 2026 at 11:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `subspilot`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `created_at`) VALUES
(1, 1, 'Menambahkan subscription Netflix', '2026-07-09 09:16:14'),
(2, 1, 'Menambahkan subscription Spotify', '2026-07-09 09:16:14'),
(3, 1, 'Mengubah data Canva Pro', '2026-07-09 09:16:14'),
(4, 1, 'Login ke sistem', '2026-07-09 09:16:14');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `description`, `created_at`) VALUES
(1, 'Streaming', 'Layanan hiburan film dan video', '2026-07-09 09:13:52'),
(2, 'Music', 'Layanan musik digital', '2026-07-09 09:13:52'),
(3, 'AI Tools', 'Layanan artificial intelligence', '2026-07-09 09:13:52'),
(4, 'Cloud Storage', 'Penyimpanan cloud', '2026-07-09 09:13:52'),
(5, 'Design', 'Tools desain digital', '2026-07-09 09:13:52');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Paid','Pending','Failed') DEFAULT 'Paid',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `method_name` varchar(100) NOT NULL,
  `provider` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `method_name`, `provider`, `created_at`) VALUES
(1, 'Bank Transfer', 'BCA', '2026-07-09 09:14:21'),
(2, 'E-Wallet', 'DANA', '2026-07-09 09:14:21'),
(3, 'Credit Card', 'Visa', '2026-07-09 09:14:21'),
(4, 'E-Wallet', 'Gopay', '2026-07-09 09:14:21');

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `reminder_date` date DEFAULT NULL,
  `reminder_type` enum('H-7','H-3','H-1','Today') DEFAULT NULL,
  `is_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reminders`
--

INSERT INTO `reminders` (`id`, `subscription_id`, `reminder_date`, `reminder_type`, `is_sent`, `created_at`) VALUES
(1, 1, '2026-07-08', 'H-7', 0, '2026-07-09 09:15:38'),
(2, 2, '2026-07-17', 'H-3', 0, '2026-07-09 09:15:38'),
(3, 3, '2026-07-24', 'H-1', 0, '2026-07-09 09:15:38'),
(4, 1, '2026-07-08', 'H-7', 0, '2026-07-09 09:15:52'),
(5, 2, '2026-07-17', 'H-3', 0, '2026-07-09 09:15:52'),
(6, 3, '2026-07-24', 'H-1', 0, '2026-07-09 09:15:52');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `service_name` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT 'default.png',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'IDR',
  `billing_cycle` enum('Weekly','Monthly','Quarterly','Yearly') DEFAULT 'Monthly',
  `start_date` date DEFAULT NULL,
  `next_payment` date DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 1,
  `status` enum('Active','Cancelled','Paused') DEFAULT 'Active',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `category_id`, `payment_method_id`, `service_name`, `logo`, `amount`, `currency`, `billing_cycle`, `start_date`, `next_payment`, `auto_renew`, `status`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Netflix', 'default.png', 186000.00, 'IDR', 'Monthly', '2026-06-15', '2026-07-15', 1, 'Active', 'Premium streaming', '2026-07-09 09:15:05', '2026-07-09 09:15:05'),
(2, 1, 2, 2, 'Spotify', 'default.png', 54000.00, 'IDR', 'Monthly', '2026-06-20', '2026-07-20', 1, 'Active', 'Music subscription', '2026-07-09 09:15:05', '2026-07-09 09:15:05'),
(3, 1, 3, 1, 'ChatGPT Plus', 'default.png', 300000.00, 'IDR', 'Monthly', '2026-06-25', '2026-07-25', 1, 'Active', 'AI assistant', '2026-07-09 09:15:05', '2026-07-09 09:15:05'),
(4, 1, 4, 2, 'Google Drive', 'default.png', 26000.00, 'IDR', 'Monthly', '2026-06-10', '2026-07-10', 1, 'Active', 'Cloud storage', '2026-07-09 09:15:05', '2026-07-09 09:15:05'),
(5, 1, 5, 3, 'Canva Pro', 'default.png', 95000.00, 'IDR', 'Monthly', '2026-06-30', '2026-07-30', 1, 'Paused', 'Design tool', '2026-07-09 09:15:05', '2026-07-09 09:15:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT 'default.png',
  `role` enum('admin','user') DEFAULT 'user',
  `dark_mode` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `photo`, `role`, `dark_mode`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Kahlil Gibran', 'admin@gmail.com', '$2y$10$FVnbuc09nCm9EnsTf1BDfO4x1hQ4mGz3KxmIfoJMZF669gzacmPLW', 'default.png', 'user', 0, 'active', '2026-07-09 07:22:52', '2026-07-09 07:22:52');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `service_name` varchar(100) DEFAULT NULL,
  `estimated_price` decimal(10,2) DEFAULT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `service_name`, `estimated_price`, `priority`, `note`, `created_at`) VALUES
(1, 1, 'Adobe Creative Cloud', 800000.00, 'High', 'Untuk editing profesional', '2026-07-09 09:15:17'),
(2, 1, 'YouTube Premium', 59000.00, 'Medium', 'Bebas iklan', '2026-07-09 09:15:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_subscription_user` (`user_id`),
  ADD KEY `fk_subscription_category` (`category_id`),
  ADD KEY `fk_subscription_payment` (`payment_method_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `payment_history_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reminders`
--
ALTER TABLE `reminders`
  ADD CONSTRAINT `reminders_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `fk_subscription_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_subscription_payment` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_subscription_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ===== 2. admin_seed.sql (akun admin default) =====

UPDATE `users` SET `role` = 'admin' WHERE `email` = 'admin@gmail.com';




-- ===== 3. support_migration.sql =====
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

-- ===== 4. support_migration_extra.sql =====
-- =====================================================================
-- SubsPilot — Tambahan FAQ (opsional, jalankan SETELAH support_migration.sql)
-- Berisi pertanyaan-pertanyaan tambahan seputar fitur-fitur SubsPilot
-- (wishlist, kategori, laporan, dark mode, export, dsb) supaya
-- chatbot punya lebih banyak variasi jawaban.
-- =====================================================================

INSERT INTO `faqs` (`category_id`, `question`, `answer`, `keywords`, `sort_order`) VALUES
(2, 'Bagaimana cara mengubah kategori sebuah subscription?', 'Buka menu Subscription, klik ikon edit pada subscription yang dituju, lalu pilih kategori baru pada form dan simpan.', 'ubah kategori,ganti kategori subscription,edit kategori', 4),
(2, 'Apakah saya bisa menambah kategori sendiri?', 'Bisa. Buka menu Category, klik "Tambah Kategori", lalu isi nama dan deskripsi kategori sesuai kebutuhan Anda.', 'tambah kategori baru,buat kategori sendiri,kategori custom', 5),
(3, 'Bagaimana cara export data pembayaran atau subscription?', 'Fitur export tersedia melalui panel admin pada menu terkait. Jika Anda pengguna biasa, silakan hubungi CS untuk permintaan data lengkap.', 'export data,unduh data,download laporan', 3),
(3, 'Kenapa jumlah tagihan yang tampil berbeda dengan yang saya bayar?', 'Periksa kembali nominal pada menu Subscription, pastikan sudah sesuai. Jika masih berbeda, silakan laporkan ke CS agar dibantu pengecekan lebih lanjut.', 'jumlah tagihan salah,nominal beda,harga tidak sesuai', 4),
(1, 'Bagaimana cara logout dari akun saya?', 'Klik menu Logout pada bagian bawah sidebar untuk keluar dari akun Anda dengan aman.', 'cara logout,keluar akun,sign out', 4),
(1, 'Apakah saya bisa mengubah email akun?', 'Saat ini perubahan email belum bisa dilakukan mandiri. Silakan hubungi CS untuk dibantu mengubah email akun Anda.', 'ubah email,ganti email,update email akun', 5),
(4, 'Apa bedanya reminder H-7, H-3, H-1, dan Today?', 'Angka tersebut menunjukkan berapa hari sebelum tanggal jatuh tempo reminder akan dikirim. Today berarti reminder pada hari H jatuh tempo.', 'arti h-7,arti h-3,tipe reminder,reminder today', 3),
(4, 'Bisakah satu subscription punya lebih dari satu reminder?', 'Bisa. Anda dapat menambahkan beberapa reminder dengan tipe berbeda (misalnya H-7 dan H-1) untuk satu subscription yang sama.', 'reminder lebih dari satu,banyak reminder,multi reminder', 4),
(5, 'Bagaimana cara melihat laporan pengeluaran subscription saya?', 'Buka menu Reports untuk melihat ringkasan dan grafik pengeluaran subscription Anda per periode.', 'laporan pengeluaran,cek pengeluaran,report subscription', 3),
(5, 'Apa fungsi menu Wishlist?', 'Menu Wishlist digunakan untuk mencatat layanan langganan yang ingin Anda coba atau pertimbangkan di masa depan, sebelum benar-benar berlangganan.', 'fungsi wishlist,kegunaan wishlist,apa itu wishlist', 4),
(5, 'Bagaimana cara mengaktifkan dark mode?', 'Klik ikon bulan/matahari pada navbar di bagian atas halaman untuk beralih antara tampilan terang dan gelap.', 'dark mode,mode gelap,ganti tema', 5),
(5, 'Apakah SubsPilot punya aplikasi mobile?', 'Saat ini SubsPilot berbentuk aplikasi web yang bisa diakses dari browser di perangkat apa pun, termasuk HP. Aplikasi mobile khusus belum tersedia.', 'aplikasi mobile,app hp,download aplikasi', 6);

-- ===== 5. vercel_sessions.sql (wajib untuk Vercel) =====
-- =====================================================================
-- Tabel untuk menyimpan session PHP secara terpusat di database.
-- WAJIB dijalankan kalau kamu deploy ke Vercel (atau hosting serverless
-- lain), karena session file biasa tidak bisa dipakai bersama antar
-- serverless function. Tidak diperlukan untuk hosting biasa (XAMPP,
-- InfinityFree, cPanel).
-- =====================================================================
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(128) NOT NULL,
  `data` text NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
