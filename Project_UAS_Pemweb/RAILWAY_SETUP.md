# Setup Database MySQL di Railway (untuk Deploy ke Vercel)

Vercel tidak punya MySQL sendiri, jadi kita pakai **Railway** sebagai database eksternal. Gratis untuk kebutuhan tugas kuliah (ada trial credit).

## 1. Bikin akun & project

1. Buka **https://railway.app** → Sign up (bisa pakai akun GitHub, lebih cepat)
2. Setelah masuk dashboard, klik **New Project**
3. Pilih **Provision MySQL** (bukan Postgres)
4. Tunggu beberapa detik sampai statusnya jadi hijau/aktif

## 2. Ambil kredensial koneksi

1. Klik service **MySQL** yang baru dibuat
2. Buka tab **Variables** (atau **Connect**)
3. Kamu akan lihat variabel-variabel seperti ini:

   ```
   MYSQLHOST     -> contoh: monorail.proxy.rlwy.net
   MYSQLPORT     -> contoh: 12345
   MYSQLDATABASE -> contoh: railway
   MYSQLUSER     -> contoh: root
   MYSQLPASSWORD -> contoh: xxxxxxxxxxxxxxxx
   ```

4. **Catat semua nilai ini** — nanti dipakai 2 kali: untuk import database dan untuk Environment Variables di Vercel.

## 3. Import database

Railway kasih fitur **Data** tab dengan query editor bawaan (mirip phpMyAdmin sederhana). Ada 2 cara:

### Cara A — Pakai tab "Data" di Railway (paling gampang)
1. Klik service MySQL → tab **Data**
2. Klik **Query** 
3. Buka file `database/subspilot_full_import.sql` (sudah aku gabungkan semua SQL jadi satu file), copy semua isinya
4. Paste ke query editor Railway → Run

### Cara B — Pakai command line (kalau Cara A gagal karena file besar)
Dari komputer kamu (butuh `mysql` client terinstall):
```bash
mysql -h MYSQLHOST -P MYSQLPORT -u MYSQLUSER -p MYSQLDATABASE < database/subspilot_full_import.sql
```
Ganti `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLDATABASE` sesuai punya kamu. Nanti diminta password, masukkan `MYSQLPASSWORD`.

## 4. Cek tabel sudah masuk

Di tab **Data** Railway, pastikan tabel-tabel ini muncul:
`users`, `subscriptions`, `categories`, `payment_methods`, `reminders`, `wishlist`, `sessions`, dan tabel dari fitur support/chatbot.

## 5. Set Environment Variables di Vercel

Buka project Vercel kamu → **Settings** → **Environment Variables**, tambahkan (nilai dari langkah 2):

| Key di Vercel | Isi dengan nilai Railway |
|---|---|
| `DB_HOST` | `MYSQLHOST` |
| `DB_PORT` | `MYSQLPORT` |
| `DB_NAME` | `MYSQLDATABASE` |
| `DB_USER` | `MYSQLUSER` |
| `DB_PASS` | `MYSQLPASSWORD` |

Simpan, lalu **Redeploy** project di Vercel supaya env variable baru terbaca.

## 6. Test

Buka URL Vercel kamu:
- `/auth/login.php` → coba login pakai akun admin dari `admin_seed.sql`
- `/index.php` atau `/dashboard/index.php` → pastikan data muncul (bukti koneksi DB berhasil)
- Coba tambah/edit/hapus langganan → pastikan CRUD tetap jalan

## Troubleshooting cepat

| Gejala | Kemungkinan penyebab |
|---|---|
| 500 error "Koneksi Database Gagal" | Env variable salah ketik, atau lupa redeploy setelah nambah env var |
| Login berhasil tapi begitu pindah halaman ke-logout lagi | Tabel `sessions` belum ke-import — ulangi langkah 3 khusus bagian `vercel_sessions.sql` |
| 404 NOT_FOUND seperti sebelumnya | `vercel.json` tidak ikut ter-push ke GitHub, cek lagi repo-nya |
