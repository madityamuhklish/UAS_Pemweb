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
