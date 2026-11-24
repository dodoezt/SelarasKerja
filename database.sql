-- SelarasKerja — skema + data awal
-- Import: C:/xampp/mysql/bin/mysql.exe -u root < database.sql
-- Akun demo (semua password: rahasia123)
--   pelamar@demo.test  -> seeker
--   perusahaan@demo.test -> company

CREATE DATABASE IF NOT EXISTS selaraskerja_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE selaraskerja_db;

DROP TABLE IF EXISTS applications, job_accommodations, seeker_accommodations,
  jobs, companies, seeker_profiles, accommodations, users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('seeker','company') NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE accommodations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  category VARCHAR(60) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE seeker_profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  phone VARCHAR(25) DEFAULT NULL,
  bio TEXT DEFAULT NULL,
  skills VARCHAR(255) DEFAULT NULL,
  cv_file VARCHAR(255) DEFAULT NULL,
  other_needs VARCHAR(255) DEFAULT NULL,
  CONSTRAINT fk_sp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE seeker_accommodations (
  seeker_id INT NOT NULL,
  accommodation_id INT NOT NULL,
  PRIMARY KEY (seeker_id, accommodation_id),
  CONSTRAINT fk_sa_seeker FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_sa_acc FOREIGN KEY (accommodation_id) REFERENCES accommodations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE companies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  company_name VARCHAR(120) NOT NULL,
  location VARCHAR(120) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  CONSTRAINT fk_co_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE jobs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  salary VARCHAR(60) DEFAULT NULL,
  job_type ENUM('Penuh waktu','Paruh waktu','Kontrak','Magang','Remote') NOT NULL DEFAULT 'Penuh waktu',
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_job_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  INDEX idx_jobs_status (status)
) ENGINE=InnoDB;

CREATE TABLE job_accommodations (
  job_id INT NOT NULL,
  accommodation_id INT NOT NULL,
  PRIMARY KEY (job_id, accommodation_id),
  CONSTRAINT fk_ja_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  CONSTRAINT fk_ja_acc FOREIGN KEY (accommodation_id) REFERENCES accommodations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  job_id INT NOT NULL,
  seeker_id INT NOT NULL,
  cover_note TEXT DEFAULT NULL,
  status ENUM('pending','reviewed','accepted','rejected') NOT NULL DEFAULT 'pending',
  applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_application (job_id, seeker_id),
  CONSTRAINT fk_ap_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  CONSTRAINT fk_ap_seeker FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  token VARCHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pr_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS saved_jobs (
  seeker_id INT NOT NULL,
  job_id INT NOT NULL,
  saved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (seeker_id, job_id),
  CONSTRAINT fk_sj_seeker FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_sj_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Data master fasilitas ----------
INSERT INTO accommodations (name, category) VALUES
  ('Remote Work', 'Pengaturan kerja'),
  ('Jam Kerja Fleksibel', 'Pengaturan kerja'),
  ('Akses Kursi Roda', 'Bangunan fisik'),
  ('Toilet Aksesibel', 'Bangunan fisik'),
  ('Juru Bahasa Isyarat', 'Pendampingan komunikasi'),
  ('Screen Reader Friendly', 'Sistem kerja'),
  ('Teks Besar & Kontras Tinggi', 'Materi kerja'),
  ('Materi Braille', 'Materi kerja');

-- ---------- Akun demo (password: rahasia123) ----------
INSERT INTO users (name, email, password, role) VALUES
  ('Rani Prasetya', 'pelamar@demo.test', '$2y$10$W4ACRN39sVdiiQlTjdqxrewt2IV5Mj.IHwag7vhlNNWlfq.rcNgJW', 'seeker'),
  ('Bagas Saputra', 'pelamar2@demo.test', '$2y$10$W4ACRN39sVdiiQlTjdqxrewt2IV5Mj.IHwag7vhlNNWlfq.rcNgJW', 'seeker'),
  ('PT Cahaya Nusantara', 'perusahaan@demo.test', '$2y$10$W4ACRN39sVdiiQlTjdqxrewt2IV5Mj.IHwag7vhlNNWlfq.rcNgJW', 'company');

INSERT INTO seeker_profiles (user_id, phone, bio, skills, other_needs) VALUES
  (1, '081200000001', 'Lulusan Administrasi Perkantoran, pengguna kursi roda.', 'Administrasi, data entry, Excel', 'Butuh meja kerja yang dapat disesuaikan ketinggiannya'),
  (2, '081200000002', 'Desainer grafis lepas, pengguna screen reader.', 'Desain, Illustrator, HTML/CSS', NULL);

INSERT INTO seeker_accommodations (seeker_id, accommodation_id)
SELECT u.id, a.id FROM users u JOIN accommodations a
WHERE u.email = 'pelamar@demo.test'
  AND a.name IN ('Akses Kursi Roda', 'Toilet Aksesibel', 'Jam Kerja Fleksibel');

INSERT INTO seeker_accommodations (seeker_id, accommodation_id)
SELECT u.id, a.id FROM users u JOIN accommodations a
WHERE u.email = 'pelamar2@demo.test'
  AND a.name IN ('Remote Work', 'Screen Reader Friendly', 'Teks Besar & Kontras Tinggi');

INSERT INTO companies (user_id, company_name, location, description) VALUES
  (3, 'PT Cahaya Nusantara', 'Purwokerto', 'Perusahaan distribusi barang konsumsi dengan tim administrasi 40 orang.');

INSERT INTO jobs (company_id, title, description, salary, job_type, status) VALUES
  (1, 'Staf Administrasi Data', 'Input dan verifikasi data pesanan harian. Kantor sudah memiliki ramp, lift, dan toilet aksesibel.', 'Rp4.500.000', 'Penuh waktu', 'open'),
  (1, 'Analis Gudang', 'Memantau stok dan menyusun laporan mingguan. Sebagian kerja dapat dilakukan dari rumah.', 'Rp5.200.000', 'Penuh waktu', 'open'),
  (1, 'Customer Support Online', 'Menjawab pertanyaan pelanggan melalui chat dan email. Sepenuhnya remote.', 'Rp3.800.000', 'Remote', 'open'),
  (1, 'Desainer Grafis Junior', 'Membuat materi promosi digital. Materi kerja tersedia dalam teks besar dan kontras tinggi.', 'Rp4.000.000', 'Kontrak', 'open'),
  (1, 'Staf Rekrutmen', 'Menyaring berkas pelamar dan menjadwalkan wawancara. Jam kerja fleksibel.', 'Rp4.700.000', 'Penuh waktu', 'open'),
  (1, 'Operator Telemarketing (Ditutup)', 'Lowongan lama yang sudah ditutup.', 'Rp3.500.000', 'Paruh waktu', 'closed');

INSERT INTO job_accommodations (job_id, accommodation_id)
SELECT j.id, a.id FROM jobs j JOIN accommodations a
WHERE j.title = 'Staf Administrasi Data'
  AND a.name IN ('Akses Kursi Roda', 'Toilet Aksesibel', 'Jam Kerja Fleksibel');

INSERT INTO job_accommodations (job_id, accommodation_id)
SELECT j.id, a.id FROM jobs j JOIN accommodations a
WHERE j.title = 'Analis Gudang'
  AND a.name IN ('Akses Kursi Roda', 'Remote Work');

INSERT INTO job_accommodations (job_id, accommodation_id)
SELECT j.id, a.id FROM jobs j JOIN accommodations a
WHERE j.title = 'Customer Support Online'
  AND a.name IN ('Remote Work', 'Jam Kerja Fleksibel', 'Screen Reader Friendly');

INSERT INTO job_accommodations (job_id, accommodation_id)
SELECT j.id, a.id FROM jobs j JOIN accommodations a
WHERE j.title = 'Desainer Grafis Junior'
  AND a.name IN ('Teks Besar & Kontras Tinggi', 'Screen Reader Friendly', 'Remote Work');

INSERT INTO job_accommodations (job_id, accommodation_id)
SELECT j.id, a.id FROM jobs j JOIN accommodations a
WHERE j.title = 'Staf Rekrutmen'
  AND a.name IN ('Akses Kursi Roda', 'Jam Kerja Fleksibel');

INSERT INTO applications (job_id, seeker_id, cover_note, status) VALUES
  (1, 1, 'Saya terbiasa bekerja dengan data dan memakai kursi roda, kantor ini sudah mendukung.', 'reviewed'),
  (3, 2, 'Saya berpengalaman kerja remote dengan screen reader.', 'pending');