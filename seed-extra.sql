-- SelarasKerja — data tambahan untuk demo (aman dijalankan berulang)
-- Import: C:/xampp/mysql/bin/mysql.exe -u root < seed-extra.sql
-- Semua akun baru memakai kata sandi: rahasia123
-- Setelah dijalankan: 4 perusahaan, 10 lowongan, 5 pelamar, 8 lamaran.

USE selaraskerja_db;

-- ---------- 3 perusahaan baru ----------
INSERT IGNORE INTO users (name, email, password, role) VALUES
  ('PT Sinar Mandiri', 'sinar@demo.test', '$2y$10$W4ACRN39sVdiiQlTjdqxrewt2IV5Mj.IHwag7vhlNNWlfq.rcNgJW', 'company'),
  ('CV Aksara Digital', 'aksara@demo.test', '$2y$10$W4ACRN39sVdiiQlTjdqxrewt2IV5Mj.IHwag7vhlNNWlfq.rcNgJW', 'company'),
  ('Koperasi Bumi Tani', 'bumi@demo.test', '$2y$10$W4ACRN39sVdiiQlTjdqxrewt2IV5Mj.IHwag7vhlNNWlfq.rcNgJW', 'company');

INSERT IGNORE INTO companies (user_id, company_name, location, description)
SELECT id, 'PT Sinar Mandiri', 'Semarang',
       'Distributor alat tulis kantor dengan tim layanan pelanggan dan gudang.'
FROM users WHERE email = 'sinar@demo.test';

INSERT IGNORE INTO companies (user_id, company_name, location, description)
SELECT id, 'CV Aksara Digital', 'Yogyakarta',
       'Studio konten digital kecil, sebagian besar tim bekerja jarak jauh.'
FROM users WHERE email = 'aksara@demo.test';

INSERT IGNORE INTO companies (user_id, company_name, location, description)
SELECT id, 'Koperasi Bumi Tani', 'Solo',
       'Koperasi pertanian yang mengelola administrasi anggota dan penjualan hasil panen.'
FROM users WHERE email = 'bumi@demo.test';

-- ---------- 3 pelamar baru ----------
INSERT IGNORE INTO users (name, email, password, role) VALUES
  ('Dewi Lestari', 'dewi@demo.test', '$2y$10$W4ACRN39sVdiiQlTjdqxrewt2IV5Mj.IHwag7vhlNNWlfq.rcNgJW', 'seeker'),
  ('Fajar Nugroho', 'fajar@demo.test', '$2y$10$W4ACRN39sVdiiQlTjdqxrewt2IV5Mj.IHwag7vhlNNWlfq.rcNgJW', 'seeker'),
  ('Sri Wahyuni', 'sri@demo.test', '$2y$10$W4ACRN39sVdiiQlTjdqxrewt2IV5Mj.IHwag7vhlNNWlfq.rcNgJW', 'seeker');

INSERT IGNORE INTO seeker_profiles (user_id, phone, bio, skills, other_needs)
SELECT id, '081200000003', 'Tuli, berkomunikasi dengan bahasa isyarat dan tulisan.',
       'Administrasi, komunikasi tertulis, media sosial', 'Butuh juru bahasa isyarat saat rapat'
FROM users WHERE email = 'dewi@demo.test';

INSERT IGNORE INTO seeker_profiles (user_id, phone, bio, skills, other_needs)
SELECT id, '081200000004', 'Pengguna kursi roda, lulusan Teknik Informatika.',
       'PHP, MySQL, HTML/CSS', 'Butuh akses lift dan meja kerja rendah'
FROM users WHERE email = 'fajar@demo.test';

INSERT IGNORE INTO seeker_profiles (user_id, phone, bio, skills, other_needs)
SELECT id, '081200000005', 'Low vision, memakai pembaca layar dengan perbesaran.',
       'Data entry, akuntansi dasar, Excel', 'Butuh materi kerja teks besar'
FROM users WHERE email = 'sri@demo.test';

-- Kebutuhan fasilitas (1 Remote, 2 Jam fleksibel, 6 Juru isyarat, 9 Teks besar, dst.)
INSERT IGNORE INTO seeker_accommodations (seeker_id, accommodation_id)
SELECT u.id, a.id FROM users u JOIN accommodations a
WHERE u.email = 'dewi@demo.test' AND a.name IN ('Remote Work', 'Juru Bahasa Isyarat', 'Jam Kerja Fleksibel');

INSERT IGNORE INTO seeker_accommodations (seeker_id, accommodation_id)
SELECT u.id, a.id FROM users u JOIN accommodations a
WHERE u.email = 'fajar@demo.test' AND a.name IN ('Akses Kursi Roda', 'Remote Work', 'Toilet Aksesibel');

INSERT IGNORE INTO seeker_accommodations (seeker_id, accommodation_id)
SELECT u.id, a.id FROM users u JOIN accommodations a
WHERE u.email = 'sri@demo.test' AND a.name IN ('Teks Besar & Kontras Tinggi', 'Screen Reader Friendly', 'Jam Kerja Fleksibel');

-- ---------- 4 lowongan baru (total 10 lowongan) ----------
INSERT IGNORE INTO jobs (company_id, title, description, salary, job_type, status)
SELECT c.id, 'Operator Data Entry', 'Memasukkan data pesanan dan mencocokkan faktur. Bisa dikerjakan dari rumah.',
       'Rp4.200.000', 'Remote', 'open'
FROM companies c WHERE c.company_name = 'PT Sinar Mandiri'
  AND NOT EXISTS (SELECT 1 FROM jobs j WHERE j.title = 'Operator Data Entry');

INSERT IGNORE INTO jobs (company_id, title, description, salary, job_type, status)
SELECT c.id, 'Staf Customer Relation', 'Menangani keluhan pelanggan lewat chat dan tatap muka. Kantor sudah aksesibel.',
       'Rp4.800.000', 'Penuh waktu', 'open'
FROM companies c WHERE c.company_name = 'PT Sinar Mandiri'
  AND NOT EXISTS (SELECT 1 FROM jobs j WHERE j.title = 'Staf Customer Relation');

INSERT IGNORE INTO jobs (company_id, title, description, salary, job_type, status)
SELECT c.id, 'Content Writer Lepas', 'Menulis artikel dan caption media sosial. Sepenuhnya jarak jauh.',
       'Rp3.600.000', 'Remote', 'open'
FROM companies c WHERE c.company_name = 'CV Aksara Digital'
  AND NOT EXISTS (SELECT 1 FROM jobs j WHERE j.title = 'Content Writer Lepas');

INSERT IGNORE INTO jobs (company_id, title, description, salary, job_type, status)
SELECT c.id, 'Admin Koperasi', 'Mengelola data anggota, simpanan, dan laporan bulanan.',
       'Rp4.100.000', 'Kontrak', 'open'
FROM companies c WHERE c.company_name = 'Koperasi Bumi Tani'
  AND NOT EXISTS (SELECT 1 FROM jobs j WHERE j.title = 'Admin Koperasi');

-- Fasilitas tiap lowongan baru
INSERT IGNORE INTO job_accommodations (job_id, accommodation_id)
SELECT j.id, a.id FROM jobs j JOIN accommodations a
WHERE j.title = 'Operator Data Entry'
  AND a.name IN ('Remote Work', 'Jam Kerja Fleksibel', 'Screen Reader Friendly', 'Toilet Aksesibel');

INSERT IGNORE INTO job_accommodations (job_id, accommodation_id)
SELECT j.id, a.id FROM jobs j JOIN accommodations a
WHERE j.title = 'Staf Customer Relation'
  AND a.name IN ('Akses Kursi Roda', 'Toilet Aksesibel', 'Jam Kerja Fleksibel');

INSERT IGNORE INTO job_accommodations (job_id, accommodation_id)
SELECT j.id, a.id FROM jobs j JOIN accommodations a
WHERE j.title = 'Content Writer Lepas'
  AND a.name IN ('Remote Work', 'Jam Kerja Fleksibel', 'Teks Besar & Kontras Tinggi', 'Screen Reader Friendly');

INSERT IGNORE INTO job_accommodations (job_id, accommodation_id)
SELECT j.id, a.id FROM jobs j JOIN accommodations a
WHERE j.title = 'Admin Koperasi'
  AND a.name IN ('Akses Kursi Roda', 'Jam Kerja Fleksibel', 'Toilet Aksesibel');

-- ---------- 6 lamaran baru (total 8) ----------
INSERT IGNORE INTO applications (job_id, seeker_id, cover_note, status)
SELECT j.id, u.id, 'Saya terbiasa melayani pelanggan secara tertulis.', 'pending'
FROM jobs j JOIN users u
WHERE j.title = 'Staf Customer Relation' AND u.email = 'dewi@demo.test';

INSERT IGNORE INTO applications (job_id, seeker_id, cover_note, status)
SELECT j.id, u.id, 'Bisa bekerja mandiri dengan target harian.', 'reviewed'
FROM jobs j JOIN users u
WHERE j.title = 'Operator Data Entry' AND u.email = 'fajar@demo.test';

INSERT IGNORE INTO applications (job_id, seeker_id, cover_note, status)
SELECT j.id, u.id, 'Sudah terbiasa menyusun laporan koperasi.', 'pending'
FROM jobs j JOIN users u
WHERE j.title = 'Admin Koperasi' AND u.email = 'sri@demo.test';

INSERT IGNORE INTO applications (job_id, seeker_id, cover_note, status)
SELECT j.id, u.id, 'Saya menulis dengan bahasa isyarat sebagai bahasa pertama.', 'rejected'
FROM jobs j JOIN users u
WHERE j.title = 'Content Writer Lepas' AND u.email = 'dewi@demo.test';

INSERT IGNORE INTO applications (job_id, seeker_id, cover_note, status)
SELECT j.id, u.id, 'Siap bekerja hybrid bila diperlukan.', 'pending'
FROM jobs j JOIN users u
WHERE j.title = 'Staf Administrasi Data' AND u.email = 'fajar@demo.test';

INSERT IGNORE INTO applications (job_id, seeker_id, cover_note, status)
SELECT j.id, u.id, 'Bisa mulai kerja remote segera.', 'accepted'
FROM jobs j JOIN users u
WHERE j.title = 'Customer Support Online' AND u.email = 'sri@demo.test';