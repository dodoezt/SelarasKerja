# SelarasKerja

Portal lowongan kerja inklusif yang menyelaraskan kebutuhan akomodasi penyandang disabilitas dengan fasilitas yang disediakan perusahaan. Setiap lowongan menampilkan **"Cocok X dari Y kebutuhanmu"** sebelum pelamar melamar.

## Latar belakang dan tujuan

Informasi lowongan hampir tidak pernah menyebut fasilitas akomodasi yang tersedia, sehingga pelamar menebak, melamar ke banyak tempat, lalu gugur di tahap akhir karena fasilitas tidak ada. Sebaliknya, perusahaan yang sudah menyediakan fasilitas ramah disabilitas tidak punya kanal untuk menunjukkannya.

Tujuan: menyediakan portal lowongan yang mencocokkan kebutuhan akomodasi pelamar dengan fasilitas perusahaan, dengan skor kecocokan terbuka sejak awal.

Kaitan SDGs:
- **SDG 8 — Pekerjaan Layak dan Pertumbuhan Ekonomi** (target 8.5): memperluas akses kerja bagi penyandang disabilitas.
- **SDG 10 — Berkurangnya Kesenjangan** (target 10.2): mendorong inklusi sosial dan ekonomi tanpa memandang disabilitas.

## Fitur dan modul

| Kode | Modul | Keterangan |
| --- | --- | --- |
| US-01 | Registrasi multi-peran | Daftar sebagai pelamar atau perusahaan, validasi server-side |
| US-02 | Login & session | `password_verify()`, `session_regenerate_id()`, redirect per peran |
| US-03 | Proteksi halaman | `require_login()` / `require_role()` di setiap halaman |
| US-04 | Profil pelamar & kebutuhan akomodasi | Biodata, keahlian, unggah CV PDF, centang fasilitas (tabel pivot) |
| US-05 | Eksplorasi lowongan & skor kecocokan | `array_intersect` + `usort`, badge "Cocok X dari Y" |
| US-06 | Detail lowongan & rincian fasilitas | Daftar terpenuhi (✓) vs belum (✕), form lamar |
| US-07 | Lamaran & riwayat status | Kirim lamaran, pantau Menunggu/Ditinjau/Diterima/Ditolak |
| US-08 | Profil perusahaan | Nama, lokasi, deskripsi |
| US-09 | Kelola lowongan (CRUD) | Tambah/edit/hapus, buka/tutup, centang fasilitas |
| US-10 | Manajemen pelamar | Daftar pelamar per lowongan, skor, unduh CV, ubah status |
| US-11 | Pencarian, filter & paginasi | `LIKE`, filter jenis + fasilitas, `LIMIT/OFFSET` |

Pemetaan fitur praktikum ke modul aplikasi ada di `docs/PRD.md` bagian 2E.

## Teknologi

- PHP 8 native, PDO dengan prepared statements
- MariaDB / MySQL (`selaraskerja_db`, 8 tabel)
- HTML5 semantik, CSS vanilla (custom properties, grid/flex, kontras WCAG AA), JavaScript vanilla (pengatur ukuran teks)
- Font Archivo self-hosted, aset gambar di `public/img/`

## Instalasi dan konfigurasi

1. Salin folder proyek ke `htdocs` (contoh: `C:\xampp\htdocs\SelarasKerja`).
2. Jalankan Apache dan MySQL dari XAMPP.
3. Impor database:

   ```
   C:/xampp/mysql/bin/mysql.exe -u root < database.sql
   ```

   Perintah ini membuat database `selaraskerja_db`, 8 tabel, 12 data master fasilitas, dan data demo dasar (3 akun, 6 lowongan).
4. Tambah data demo lanjutan (opsional, aman dijalankan berulang):

   ```
   C:/xampp/mysql/bin/mysql.exe -u root < seed-extra.sql
   ```

   Hasil akhir: 9 akun, 4 perusahaan, 10 lowongan, 5 pelamar, 8 lamaran.
5. Sesuaikan kredensial di `config/database.php` bila user/password MySQL berbeda (default: `root` tanpa password).
6. Pastikan folder `public/uploads/cv/` dapat ditulis oleh web server.

## Menjalankan aplikasi

Buka `http://localhost/SelarasKerja/`.

Akun demo (password semua: `rahasia123`):

| Peran | Email | Keterangan |
| --- | --- | --- |
| Pelamar | `pelamar@demo.test` | Rani — kursi roda, butuh 4 fasilitas |
| Pelamar | `pelamar2@demo.test` | Bagas — screen reader, butuh 3 fasilitas |
| Pelamar | `dewi@demo.test` | Dewi — tuli, butuh juru bahasa isyarat |
| Pelamar | `fajar@demo.test` | Fajar — kursi roda, lulusan informatika |
| Pelamar | `sri@demo.test` | Sri — low vision, butuh teks besar |
| Perusahaan | `perusahaan@demo.test` | PT Cahaya Nusantara (Purwokerto), 6 lowongan |
| Perusahaan | `sinar@demo.test` | PT Sinar Mandiri (Semarang), 2 lowongan |
| Perusahaan | `aksara@demo.test` | CV Aksara Digital (Yogyakarta), 1 lowongan |
| Perusahaan | `bumi@demo.test` | Koperasi Bumi Tani (Solo), 1 lowongan |

Alur uji cepat: masuk sebagai pelamar → lihat skor di daftar lowongan → buka detail → lamar. Lalu masuk sebagai perusahaan → Lowongan Saya → Pelamar → ubah status.

## Struktur folder

```
config/database.php       koneksi PDO + APP_URL
helpers/functions.php     escape, CSRF, flash, redirect
helpers/auth_guard.php    cek login & peran
helpers/matcher.php       irisan kebutuhan vs fasilitas, skor
includes/bootstrap.php    pemuatan awal
includes/header.php       nav dinamis per peran + pengatur teks
includes/footer.php
auth/                     register, login, logout
seeker/                   dashboard, job_detail, profile, applications
company/                  dashboard, profile, job_form, job_delete, applicants
public/                   css, js, img, fonts, uploads/cv
database.sql              skema + data awal
docs/                     PRD dan panduan responsi
```

## Keamanan

- Kata sandi `password_hash()` / `password_verify()`
- Seluruh query memakai prepared statement
- Semua output teks pengguna dilewatkan `htmlspecialchars()`
- Token CSRF pada setiap form POST
- Unggah CV: validasi MIME `application/pdf`, maksimal 2 MB, nama berkas diacak
- Perubahan data selalu dicek kepemilikannya (`company_id` / `seeker_id`)

## Pembagian tugas

| Anggota | Peran |
| --- | --- |
| (nama 1) | Ketua, modul autentikasi & database |
| (nama 2) | Modul pelamar & algoritma matching |
| (nama 3) | Modul perusahaan & tampilan/aksesibilitas |