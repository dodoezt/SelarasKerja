# Product Requirements Document (PRD) — SelarasKerja

## 1. Informasi Umum Projek

- **Nama Produk:** SelarasKerja
- **Deskripsi Singkat:** Portal informasi lowongan kerja inklusif yang menyelaraskan kebutuhan akomodasi penyandang disabilitas dengan fasilitas yang disediakan oleh perusahaan melalui sistem _matching_ otomatis (**"Cocok X dari Y kebutuhanmu"**).
- **Tech Stack:**
- **Backend:** PHP Native (PHP 8.x, PDO / MySQLi dengan _Prepared Statements_)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5 Semantik, Vanilla CSS (CSS Variables, Flexbox/Grid, High-Contrast Accessible UI), Vanilla JavaScript (opsional untuk interaksi ringan seperti _text-resizer_ & filter)

- **Target Pengguna:**

1. **Pelamar (Seeker):** Penyandang disabilitas yang mencari pekerjaan sesuai kebutuhan aksesibilitasnya.
2. **Perusahaan (Company):** Penyedia kerja yang membuka lowongan dan mencantumkan fasilitas ramah disabilitas yang tersedia.

### Latar Belakang & Kaitan SDGs

Penyandang disabilitas menghadapi dua hambatan sekaligus saat mencari kerja: informasi lowongan hampir tidak pernah menyebut fasilitas akomodasi yang tersedia, dan proses seleksi tidak transparan soal kesesuaian kebutuhan. Pelamar akhirnya menebak, melamar ke banyak tempat, lalu gugur di tahap akhir karena fasilitas tidak ada. Sebaliknya, perusahaan yang sudah menyediakan fasilitas ramah disabilitas tidak punya kanal untuk menunjukkannya.

SelarasKerja menjawab masalah itu dengan mencocokkan kebutuhan akomodasi pelamar terhadap fasilitas yang ditawarkan lowongan, lalu menampilkannya terbuka sebagai **"Cocok X dari Y kebutuhanmu"** sebelum pelamar melamar.

- **SDG 8 — Pekerjaan Layak dan Pertumbuhan Ekonomi** (target 8.5): memperluas akses kerja penuh dan produktif bagi penyandang disabilitas.
- **SDG 10 — Berkurangnya Kesenjangan** (target 10.2): mendorong inklusi sosial dan ekonomi tanpa memandang disabilitas.

**Tujuan:** menyediakan portal lowongan inklusif yang menyelaraskan kebutuhan akomodasi pelamar dengan fasilitas perusahaan, dengan skor kecocokan yang terlihat sejak awal.

**Manfaat:** pelamar menghemat waktu karena tahu tingkat kecocokan sebelum melamar; perusahaan memperoleh kandidat yang relevan sekaligus alat bukti praktik ketenagakerjaan inklusif.

---

## 2. Tujuan & Kebutuhan Fungsional (User Stories)

### A. Autentikasi & Manajemen Akun (Multi-Role)

- **US-01 (Registrasi):** Pengguna dapat mendaftar akun baru dengan mengisi Nama, Email, Password, serta memilih peran (**Pelamar** atau **Perusahaan**).
- **US-02 (Login & Session):** Pengguna dapat masuk ke sistem dan diarahkan ke _dashboard_ sesuai perannya menggunakan `$_SESSION`.
- **US-03 (Proteksi Halaman & Akses Publik):** Halaman profil, lamaran, dan kelola lowongan hanya dapat diakses oleh pemilik perannya masing-masing (`seeker`/`company`); pengunjung tanpa login diarahkan ke halaman masuk. Daftar dan detail lowongan sengaja dibiarkan publik (tamu melihat fasilitas tanpa skor kecocokan), dan perusahaan yang membukanya diarahkan ke dashboard-nya.

### B. Fitur Pelamar (Seeker)

- **US-04 (Profil & Kebutuhan Akomodasi):** Pelamar dapat mengisi/memperbarui biodata, keahlian (_skills_), mengunggah file CV (`.pdf`), serta mencentang kebutuhan fasilitas dari daftar master akomodasi.
- **US-05 (Eksplorasi Lowongan & Skor Kecocokan):** Pelamar dapat melihat daftar lowongan aktif yang otomatis menampilkan indikator **"Cocok X dari Y kebutuhanmu"** dan diurutkan dari skor kecocokan tertinggi. Daftar yang sama juga dapat dibuka tamu tanpa login (badge diganti ajakan masuk, urutan default tanggal terbaru).
- **US-06 (Detail Lowongan & Rincian Fasilitas):** Pelamar dapat melihat detail lowongan beserta perbandingan fasilitas mana saja yang terpenuhi (centang hijau) dan yang belum terpenuhi (silang/abu-abu). Halaman detail juga dapat dibuka tamu; rincian cocok/belum terpenuhi hanya muncul untuk pelamar yang sudah mengisi kebutuhan.
- **US-07 (Melamar & Riwayat Lamaran):** Pelamar dapat mengirim lamaran pada lowongan dan memantau status lamarannya (_Menunggu_, _Ditinjau_, _Diterima_, _Ditolak_).

### C. Fitur Perusahaan (Company)

- **US-08 (Profil Perusahaan):** Perusahaan dapat mengisi informasi nama perusahaan, deskripsi, dan lokasi.
- **US-09 (Kelola Lowongan - CRUD):** Perusahaan dapat membuat, melihat, mengedit, dan menghapus/menonaktifkan lowongan kerja beserta mencentang fasilitas akomodasi yang disediakan.
- **US-10 (Manajemen Pelamar):** Perusahaan dapat melihat daftar pelamar di setiap lowongan, melihat skor kecocokan pelamar, mengunduh CV, dan mengubah status lamaran.

### D. Pencarian, Filter & Paginasi

- **US-11 (Cari & Filter Lowongan):** Pelamar dapat mencari lowongan berdasarkan kata kunci judul, memfilter berdasarkan jenis pekerjaan dan fasilitas akomodasi tertentu, serta menelusuri hasil per halaman (paginasi 6 lowongan per halaman) tanpa kehilangan urutan skor kecocokan. Filter dijalankan lewat query `LIKE`/`=` di SQL; pemotongan per halaman memakai `array_slice` setelah skor dihitung, karena pengurutan skor dilakukan di PHP.

### E. Pemetaan Modul Praktikum → Modul Aplikasi

Setiap modul aplikasi wajib memakai fitur yang dipelajari selama praktikum. Pemetaan berikut dipakai untuk menunjukkan pemenuhan tersebut saat presentasi.

| Fitur Praktikum | Diimplementasikan pada | Modul Aplikasi |
| --- | --- | --- |
| HTML5 semantik, CSS Variables/Grid, kontras tinggi | semua halaman, `public/css/style.css` | — (dasar tampilan) |
| Form & validasi server-side | `auth/register.php`, `seeker/profile.php`, `company/job_form.php` | US-01, US-04, US-09 |
| Session & cookie, guard peran | `helpers/auth_guard.php`, `auth/login.php`, `auth/logout.php` | US-02, US-03 |
| Upload file (validasi `.pdf`, maks 2MB) | `seeker/profile.php` | US-04 |
| Koneksi database, CRUD, prepared statement | `config/database.php`, `company/job_form.php`, `company/job_delete.php` | US-09 |
| Relasi tabel / JOIN antar tabel pivot | `helpers/matcher.php`, `company/applicants.php` | US-05, US-06, US-10 |
| Searching (query `LIKE`) & paginasi (`array_slice`, 6 per halaman) | `lowongan.php`, `helpers/matcher.php` | US-11 |
| Sorting & array PHP (`array_intersect`, `usort`) | `helpers/matcher.php` | US-05 |
| JavaScript ringan | `public/js/accessibility.js` | pengatur ukuran teks, menu mobile, aksesibilitas |

> Catatan: nama modul praktikum disesuaikan dengan modul resmi asisten. Jumlah modul aplikasi = 11 user story, di atas batas minimal 9.

---

## 3. Kebutuhan Non-Fungsional & Aksesibilitas

1. **Aksesibilitas Web (Nilai Utama Projek):**

- Menggunakan tag HTML semantik (`<header>`, `<nav>`, `<main>`, `<article>`, `<label for="...">`).
- Kontras warna teks dan latar belakang memenuhi standar keterbacaan tinggi (WCAG AA).
- Indikator `:focus-visible` yang jelas untuk navigasi menggunakan _keyboard_ (tombol `Tab`).
- Fitur **Pengatur Ukuran Teks** (Normal / Besar) berbasis Vanilla CSS + JS ringan.

2. **Keamanan Dasar:**

- Password di-hash menggunakan `password_hash()` dan diverifikasi dengan `password_verify()`.
- Semua query database wajib menggunakan _Prepared Statements_ untuk mencegah SQL Injection.
- Output teks dari user wajib dibersihkan dengan `htmlspecialchars()` untuk mencegah XSS.
- Validasi tipe file (`.pdf`) dan ukuran maksimal (mis. 2MB) saat unggah CV.

---

## 4. Desain Skema Database (`selaraskerja_db`)

| Nama Tabel              | Kolom Utama                                                                                                                              | Keterangan                                                                                                                             |
| ----------------------- | ---------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| `users`                 | `id` (PK), `name`, `email` (UNIQUE), `password`, `role` (`'seeker'`, `'company'`), `created_at`                                          | Data akun utama & hak akses                                                                                                            |
| `accommodations`        | `id` (PK), `name`, `category`                                                                                                            | Master data fasilitas (mis. _Remote Work_, _Juru Bahasa Isyarat_, _Akses Kursi Roda_, _Screen Reader Friendly_, _Jam Kerja Fleksibel_) |
| `seeker_profiles`       | `id` (PK), `user_id` (FK), `phone`, `bio`, `skills`, `cv_file`, `other_needs`                                                            | Detail profil pelamar & file CV                                                                                                        |
| `seeker_accommodations` | `seeker_id` (FK), `accommodation_id` (FK)                                                                                                | Pivot kebutuhan fasilitas yang dicentang pelamar                                                                                       |
| `companies`             | `id` (PK), `user_id` (FK), `company_name`, `location`, `description`                                                                     | Detail profil perusahaan                                                                                                               |
| `jobs`                  | `id` (PK), `company_id` (FK), `title`, `description`, `salary`, `job_type`, `status` (`'open'`, `'closed'`), `created_at`                | Daftar lowongan pekerjaan                                                                                                              |
| `job_accommodations`    | `job_id` (FK), `accommodation_id` (FK)                                                                                                   | Pivot fasilitas yang disediakan pada lowongan                                                                                          |
| `applications`          | `id` (PK), `job_id` (FK), `seeker_id` (FK), `cover_note`, `status` (`'pending'`, `'reviewed'`, `'accepted'`, `'rejected'`), `applied_at` | Data lamaran kerja                                                                                                                     |

---

## 5. Logika Algoritma Matching ("Cocok X dari Y")

1. Ambil semua `accommodation_id` milik pelamar yang sedang login → simpan dalam array `$seekerNeeds` (jumlah = `$totalNeeds`).
2. Ambil semua `accommodation_id` milik lowongan → simpan dalam array `$jobFacilities`.
3. Hitung irisan keduanya menggunakan fungsi bawaan PHP:

```php
$matched = array_intersect($seekerNeeds, $jobFacilities);
$matchCount = count($matched);
$totalNeeds = count($seekerNeeds);
$scorePercent = ($totalNeeds > 0) ? round(($matchCount / $totalNeeds) * 100) : 0;

```

4. **Tampilan UI:**

- Jika `$totalNeeds > 0`: Tampilkan badge **"Cocok {$matchCount} dari {$totalNeeds} kebutuhanmu ({$scorePercent}%)"** dan urutkan daftar lowongan berdasarkan `$scorePercent` tertinggi (`usort`).
- Jika `$totalNeeds == 0`: Tampilkan ajakan _"Lengkapi kebutuhan fasilitas di profilmu untuk melihat tingkat kecocokan"_.

---

## 6. Struktur Folder Projek (PHP Native + Vanilla CSS)

```text
SelarasKerja/
├── config/
│   └── database.php          # Koneksi PDO, APP_URL, session_start
├── helpers/
│   ├── functions.php         # Escape output, CSRF, flash, redirect
│   ├── auth_guard.php        # Cek session login & role
│   └── matcher.php           # Fungsi hitung skor kecocokan
├── includes/
│   ├── bootstrap.php         # Pemuatan awal (database + helpers)
│   ├── header.php            # Navbar dinamis sesuai role + tombol aksesibilitas
│   └── footer.php
├── public/
│   ├── css/
│   │   └── style.css         # Desain Vanilla CSS (high contrast & responsive)
│   ├── js/
│   │   └── accessibility.js  # Ukuran teks (localStorage) + menu mobile
│   ├── fonts/                # Font Archivo self-hosted
│   ├── img/                  # Aset gambar .webp
│   └── uploads/
│       └── cv/               # Penyimpanan file CV pelamar
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── seeker/
│   ├── profile.php           # Edit biodata, upload CV, centang kebutuhan fasilitas
│   └── applications.php      # Riwayat & status lamaran
├── company/
│   ├── dashboard.php         # Ringkasan lowongan perusahaan
│   ├── profile.php           # Edit profil perusahaan
│   ├── job_form.php          # Tambah & Edit lowongan + centang fasilitas
│   ├── job_delete.php        # Hapus lowongan
│   └── applicants.php        # Lihat pelamar per lowongan & ubah status lamaran
├── database.sql              # Skema + data awal tabel accommodations
├── seed-extra.sql            # Data demo tambahan (opsional, aman diulang)
├── docs/                     # PRD dan panduan responsi
├── index.php                 # Landing page publik
├── lowongan.php              # Daftar lowongan + filter & paginasi (publik)
└── lowongan-detail.php       # Detail lowongan + rincian fasilitas + form lamar (publik)

```

> Catatan: daftar dan detail lowongan berada di root, bukan di dalam `seeker/`, karena halaman ini publik (tamu boleh melihat fasilitas tanpa skor kecocokan).

---

## 7. Prioritas Pengerjaan (Milestone Responsi)

1. **Tahap 1 — Fondasi:** Buat `database.sql` (lengkap dengan 6–8 _dummy_ data `accommodations`), koneksi database, sistem Register, Login, dan proteksi role.
2. **Tahap 2 — Inti Matching:** Buat halaman `seeker/profile.php` (simpan centangan ke pivot table) dan `company/job_form.php` (CRUD lowongan + centangan fasilitas).
3. **Tahap 3 — Daftar & Detail:** Terapkan `helpers/matcher.php` pada `lowongan.php` dan `lowongan-detail.php`, plus pencarian, filter, dan paginasi (US-11).
4. **Tahap 4 — Alur Lamaran:** Buat fitur kirim lamaran (`applications`) dan kelola status pelamar di sisi perusahaan.
5. **Tahap 5 — Poles UI & Aksesibilitas:** Rapikan `style.css` dan uji seluruh alur akun Pelamar serta Perusahaan sebelum demo responsi.
