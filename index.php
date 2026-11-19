<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Sudah login: langsung ke dashboard sesuai peran.
$user = auth_user($pdo);
if ($user !== null) {
    redirect($user['role'] === 'company' ? '/company/dashboard.php' : '/seeker/dashboard.php');
}

$stats = [
    'Lowongan aktif'      => (int) $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'open'")->fetchColumn(),
    'Perusahaan'          => (int) $pdo->query('SELECT COUNT(*) FROM companies')->fetchColumn(),
    'Fasilitas akomodasi' => (int) $pdo->query('SELECT COUNT(*) FROM accommodations')->fetchColumn(),
    'Pelamar terdaftar'   => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seeker'")->fetchColumn(),
];

$master     = accommodation_names($pdo);
$latestJobs = array_slice(jobs_with_score($pdo, [], []), 0, 3);
$faceTones  = ['', 'tile__face--slate', 'tile__face--blue'];

$pageTitle = 'Portal lowongan kerja inklusif';
$active    = 'home';
require __DIR__ . '/includes/header.php';
?>
  <section class="hero" aria-labelledby="hero-title">
    <img class="hero__photo" src="<?= u('/public/img/hero-home.webp') ?>" width="2000" height="2067" alt="Seorang perempuan tersenyum lebar sambil memegang bola voli, dikelilingi rekan-rekannya di lapangan terbuka." fetchpriority="high">
    <div class="hero__plate">
      <p class="hero__kicker">Cocok sebelum melamar</p>
      <h1 class="hero__title" id="hero-title">
        Kerja yang cocok<br>
        dengan <em>kebutuhanmu</em>
      </h1>
      <p class="hero__sub">Representasi pelamar disabilitas — mengubah hak atas kerja layak menjadi akses nyata.</p>
      <div class="hero__actions">
        <a class="btn btn--paper" href="<?= u('/auth/register.php') ?>">Cari lowongan <span class="btn__arrow" aria-hidden="true">↗</span></a>
        <a class="btn btn--on-dark" href="<?= u('/auth/register.php?role=company') ?>">Pasang lowongan</a>
      </div>
    </div>
  </section>

  <section class="band band--blue" aria-labelledby="about-title">
    <div class="wrap band__grid">
      <div class="collage">
        <img src="<?= u('/public/img/networks.webp') ?>" width="1320" height="1440" loading="lazy" alt="Sekelompok orang penyandang disabilitas berdiskusi bersama dalam pertemuan komunitas.">
      </div>
      <div class="band__copy">
        <p class="kicker">Tentang platform</p>
        <h2 class="section-title" id="about-title">Kami mencocokkan, bukan menebak</h2>
        <p class="lead">
          SelarasKerja adalah portal lowongan inklusif yang membandingkan
          <span class="hl">kebutuhan akomodasi pelamar</span> dengan
          <span class="hl">fasilitas yang benar-benar disediakan perusahaan</span>,
          lalu menampilkan hasilnya terbuka sebelum kamu melamar.
        </p>
        <a class="btn btn--paper" href="<?= u('/auth/login.php') ?>">Jelajahi lowongan <span class="btn__arrow" aria-hidden="true">↗</span></a>
        <ul class="stats--stack">
          <li class="stat">
            <span class="stat__num"><?= number_format((int) $stats['Fasilitas akomodasi'], 0, ',', '.') ?></span>
            <span class="stat__label">Fasilitas dicocokkan</span>
          </li>
          <li class="stat">
            <span class="stat__num"><?= number_format((int) $stats['Perusahaan'], 0, ',', '.') ?></span>
            <span class="stat__label">Perusahaan terdaftar</span>
          </li>
        </ul>
      </div>
    </div>
  </section>

  <section class="band band--white" aria-labelledby="why-title">
    <div class="wrap">
      <p class="kicker">Kenapa berbeda</p>
      <h2 class="visually-hidden" id="why-title">Alasan platform ini berbeda</h2>
      <p class="lead narrow">
        Sejak awal, pelamar disabilitas menghadapi dua hambatan sekaligus:
        <span class="hl">informasi fasilitas hampir tidak pernah disebut</span>, dan
        <span class="hl">proses seleksi tidak transparan soal kesesuaian</span>.
        Kami menutup celah itu dengan satu angka yang terlihat sejak daftar lowongan:
        <span class="hl">Cocok X dari Y kebutuhanmu</span>.
      </p>
      <ul class="checklist" style="margin-top:1.25rem">
        <li>Centang kebutuhan akomodasi sekali di profil, dipakai untuk semua lowongan.</li>
        <li>Rincian centang hijau dan silang pada halaman detail lowongan.</li>
        <li>Perusahaan melihat kandidat yang kebutuhannya sudah selaras.</li>
      </ul>
      <p style="margin-top:1.25rem">
        <a class="btn" href="<?= u('/auth/register.php') ?>">Daftar sebagai pelamar <span class="btn__arrow" aria-hidden="true">↗</span></a>
      </p>
    </div>
  </section>

  <section class="band band--white" aria-label="Galeri kegiatan">
    <div class="wrap">
      <div class="mosaic">
        <figure><img src="<?= u('/public/img/scroll-gallery-3.webp') ?>" width="1080" height="596" loading="lazy" alt="Peserta pelatihan keterampilan duduk melingkar di ruang kelas."></figure>
        <figure><img src="<?= u('/public/img/AccesstoJustice.webp') ?>" width="1000" height="545" loading="lazy" alt="Seorang advokat disabilitas berbicara di forum publik."></figure>
        <figure><img src="<?= u('/public/img/carrusel-1.webp') ?>" width="1080" height="608" loading="lazy" alt="Dua pekerja disabilitas berdiskusi di depan komputer kantor."></figure>
        <figure><img src="<?= u('/public/img/carrusel-2.webp') ?>" width="1080" height="720" loading="lazy" alt="Pengguna kursi roda bekerja di lantai produksi bersama rekannya."></figure>
      </div>
    </div>
  </section>

  <section class="band band--white" aria-labelledby="figures-title">
    <div class="wrap">
      <h2 class="visually-hidden" id="figures-title">Angka ringkas platform</h2>
      <ul class="figures">
        <li class="stat">
          <span class="stat__num"><?= number_format((int) $stats['Lowongan aktif'], 0, ',', '.') ?></span>
          <span class="stat__label">Lowongan terbuka saat ini</span>
        </li>
        <li class="stat">
          <span class="stat__num"><?= number_format((int) $stats['Fasilitas akomodasi'], 0, ',', '.') ?></span>
          <span class="stat__label">Fasilitas akomodasi pada data master</span>
        </li>
        <li class="stat">
          <span class="stat__num"><?= number_format((int) $stats['Pelamar terdaftar'], 0, ',', '.') ?></span>
          <span class="stat__label">Pelamar dengan profil kebutuhan</span>
        </li>
      </ul>
    </div>
  </section>

  <section class="band band--plum" aria-labelledby="story-title">
    <div class="wrap">
      <div class="head-row">
        <div>
          <p class="kicker">Alur pelamar</p>
          <h2 class="section-title" id="story-title">Cerita kecocokan</h2>
        </div>
        <a class="btn" href="<?= u('/auth/login.php') ?>">Masuk untuk mencoba <span class="btn__arrow" aria-hidden="true">↗</span></a>
      </div>
      <div class="story">
        <figure class="story__figure">
          <img src="<?= u('/public/img/dara-cambodia.webp') ?>" width="800" height="1000" loading="lazy" alt="Seorang pelamar memegang berkas lamaran sambil berdiri di depan papan pengumuman lowongan.">
        </figure>
        <div class="story__body">
          <span class="story__quote" aria-hidden="true">“</span>
          <h3 class="story__title">Rani tahu fasilitasnya sebelum melamar</h3>
          <p class="story__sub">Purwakarta — Lowongan administrasi data</p>
          <p>Rani membutuhkan akses kursi roda, lift atau ramp, dan toilet aksesibel. Dulu ia melamar ke banyak tempat lalu gugur di tahap akhir karena fasilitasnya tidak ada.</p>
          <p>Sekarang ia mencentang tiga kebutuhan itu sekali di profil. Setiap lowongan langsung menampilkan berapa yang terpenuhi, dan daftar diurutkan dari skor tertinggi.</p>
          <p>Kecocokan bukan jaminan diterima. Tapi setidaknya ia tidak lagi menghabiskan waktu pada lowongan yang sejak awal tidak bisa mengakomodasinya.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="band band--white" id="lowongan" aria-labelledby="jobs-title">
    <div class="wrap">
      <div class="head-row">
        <div>
          <p class="kicker">Lowongan terbaru</p>
          <h2 class="section-title" id="jobs-title">Lowongan terbuka</h2>
        </div>
        <a class="btn" href="<?= u('/auth/login.php') ?>">Semua lowongan <span class="btn__arrow" aria-hidden="true">↗</span></a>
      </div>

      <?php if ($latestJobs === []): ?>
        <p class="empty">Belum ada lowongan terbuka.</p>
      <?php else: ?>
        <ul class="tiles">
          <?php foreach ($latestJobs as $i => $job): ?>
            <li class="tile">
              <div class="tile__face <?= e($faceTones[$i % 3]) ?>">
                <div class="tile__top">
                  <span>Lowongan</span>
                  <svg class="tile__mark" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                    <circle cx="20" cy="20" r="6" fill="#f2c94c"/>
                    <path d="M20 1l5 12h-10z" fill="#ff74c3"/>
                    <path d="M39 20l-12 5v-10z" fill="#ffffff"/>
                    <path d="M20 39l-5-12h10z" fill="#f2c94c"/>
                    <path d="M1 20l12-5v10z" fill="#ffffff"/>
                  </svg>
                </div>
                <p class="tile__date"><?= e(date('d M Y', strtotime($job['created_at']))) ?></p>
                <span class="tile__rule" aria-hidden="true"></span>
                <p class="tile__head">
                  <?= e($job['title']) ?> — <mark><?= e($job['company_name']) ?></mark>
                </p>
                <ul class="tile__foot">
                  <?php foreach (array_slice($job['facility_ids'], 0, 4) as $fid): ?>
                    <li><span><?= e($master[$fid]['name'] ?? 'Fasilitas') ?></span></li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <p class="tile__title"><?= e($job['title']) ?></p>
              <p class="tile__meta">
                <span><?= e($job['company_name']) ?></span>
                <span><?= e($job['location'] ?: 'Lokasi tidak disebut') ?></span>
                <span><?= e($job['job_type']) ?></span>
              </p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </section>

  <section class="band band--white" id="cara-kerja" aria-labelledby="steps-title">
    <div class="wrap">
      <div class="head-row">
        <div>
          <p class="kicker">Cara kerja</p>
          <h2 class="section-title" id="steps-title">Tiga langkah</h2>
        </div>
      </div>
      <ol class="steps">
        <li class="step">
          <div class="step__media">
            <img src="<?= u('/public/img/work.webp') ?>" width="800" height="533" loading="lazy" alt="Dua orang penyandang disabilitas bekerja bersama di depan komputer.">
            <span class="step__chip">01 — Profil</span>
          </div>
          <h3 class="step__title">Centang kebutuhanmu</h3>
          <p>Pilih fasilitas yang kamu perlukan dari daftar master. Sekali saja, bisa diubah kapan pun.</p>
        </li>
        <li class="step">
          <div class="step__media">
            <img src="<?= u('/public/img/opd.webp') ?>" width="1080" height="508" loading="lazy" alt="Pertemuan organisasi penyandang disabilitas membahas akses kerja.">
            <span class="step__chip">02 — Hitung</span>
          </div>
          <h3 class="step__title">Sistem hitung kecocokan</h3>
          <p>Setiap lowongan dibandingkan otomatis: berapa fasilitas yang cocok dari total kebutuhanmu.</p>
        </li>
        <li class="step">
          <div class="step__media">
            <img src="<?= u('/public/img/mariam-ghana.webp') ?>" width="800" height="1000" loading="lazy" alt="Seorang pelamar tersenyum setelah proses wawancara kerja.">
            <span class="step__chip">03 — Lamar</span>
          </div>
          <h3 class="step__title">Lamar yang sudah pasti cocok</h3>
          <p>Kirim lamaran beserta CV, lalu pantau statusnya sampai ada keputusan.</p>
        </li>
      </ol>
    </div>
  </section>

  <section class="band band--gold" id="fasilitas" aria-labelledby="fac-title">
    <div class="wrap">
      <p class="kicker" style="color:#000">Data master</p>
      <h2 class="section-title" id="fac-title" style="margin-bottom:1rem">Fasilitas yang dicocokkan</h2>
      <p class="statement">
        <?= number_format((int) $stats['Fasilitas akomodasi'], 0, ',', '.') ?> fasilitas
        dikelompokkan dalam pengaturan kerja, bangunan fisik, pendampingan komunikasi,
        dan kebijakan — <span class="hl">dicentang pelamar, disediakan perusahaan</span>.
      </p>
      <a class="btn" href="<?= u('/auth/register.php') ?>">Mulai dari kebutuhanmu <span class="btn__arrow" aria-hidden="true">↗</span></a>
    </div>
  </section>

<?php require __DIR__ . '/includes/footer.php'; ?>