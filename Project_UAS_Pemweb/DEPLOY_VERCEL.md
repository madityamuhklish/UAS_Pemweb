# Panduan Deploy SubsPilot ke Vercel

Project ini PHP + MySQL, sedangkan Vercel adalah platform serverless.
Agar bisa jalan di Vercel, sudah ditambahkan:

- `vercel.json` — runtime PHP community (`vercel-php`)
- `config/database.php` — sekarang baca kredensial dari Environment Variables
- `config/db_session_handler.php` + update `config/session.php` — session login disimpan di database (wajib, karena tiap file .php jadi function terpisah di Vercel)
- `database/vercel_sessions.sql` — tabel tambahan untuk session

## Langkah-langkah

### 1. Siapkan MySQL eksternal (WAJIB)
Vercel tidak menyediakan MySQL sendiri. Pilih salah satu:
- **Railway** (railway.app) — paling gampang, ada free trial
- **Aiven** (aiven.io) — ada free tier MySQL
- **Clever Cloud**

Setelah dibuat, kamu akan dapat: host, port, nama database, username, password.

### 2. Import database
Jalankan/import file berikut ke database eksternal tadi (lewat phpMyAdmin/Adminer yang disediakan provider, atau via `mysql` CLI):
1. `database/subspilot.sql`
2. `database/admin_seed.sql`
3. `database/support_migration.sql`
4. `database/support_migration_extra.sql`
5. `database/vercel_sessions.sql` ⚠️ **wajib untuk Vercel**

### 3. Push project ke GitHub
Pastikan `vercel.json` dan semua perubahan di atas ikut ter-push.

### 4. Import project di Vercel
1. New Project → Import dari GitHub repo kamu
2. Framework Preset: pilih **Other**
3. Jangan ubah Build Command / Output Directory (biarkan kosong, `vercel-php` yang menangani)

### 5. Set Environment Variables di Vercel
Di Project Settings → Environment Variables, tambahkan:

| Key | Value |
|---|---|
| `DB_HOST` | host MySQL eksternal kamu |
| `DB_NAME` | nama database |
| `DB_USER` | username database |
| `DB_PASS` | password database |
| `DB_PORT` | biasanya `3306` |

### 6. Deploy
Klik Deploy. Setelah selesai, coba akses `index.php`, `auth/login.php`, dan test login.

## Keterbatasan yang perlu kamu tahu (untuk dijelaskan di video)

- **Upload foto profil tidak permanen** — filesystem Vercel read-only & sementara, jadi file yang di-upload user akan hilang saat function restart. Untuk demo, upload foto tetap bisa jalan sesaat, tapi jangan dijadikan andalan.
- **Cold start** — request pertama ke tiap PHP file bisa terasa agak lambat karena serverless function baru "bangun".
- Kalau ingin upload foto permanen, solusinya pakai cloud storage terpisah (Cloudinary/S3) — di luar scope perubahan ini kecuali diminta.
