</main>

<footer class="site-foot">
  <div class="wrap">
    <div class="site-foot__top">
      <div>
        <p class="kicker">Gratis, terbuka untuk semua</p>
        <h2 class="foot-title">Siap mulai <em>kerja</em><br>yang selaras?</h2>
        <p class="site-foot__desc">Centang kebutuhan akomodasimu sekali, lalu lihat kecocokan setiap lowongan sebelum melamar.</p>
      </div>
      <?php if (empty($user)): ?>
        <div class="site-foot__links">
          <a class="btn btn--paper" href="<?= u('/auth/register.php') ?>">Daftar pelamar <span class="btn__arrow" aria-hidden="true">↗</span></a>
          <a class="btn btn--on-dark" href="<?= u('/auth/register.php?role=company') ?>">Daftar perusahaan</a>
        </div>
      <?php endif; ?>
    </div>

    <div class="site-foot__cols">
      <div>
        <h3>Pelamar</h3>
        <ul>
          <li><a href="<?= u('/index.php') ?>#lowongan">Lowongan terbuka</a></li>
          <li><a href="<?= u('/index.php') ?>#fasilitas">Fasilitas akomodasi</a></li>
          <li><a href="<?= u('/index.php') ?>#cara-kerja">Cara kerja pencocokan</a></li>
          <?php if (($user['role'] ?? '') === 'seeker'): ?>
            <li><a href="<?= u('/seeker/applications.php') ?>">Lamaran Saya</a></li>
            <li><a href="<?= u('/seeker/profile.php') ?>">Profil Saya</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <div>
        <?php if (($user['role'] ?? '') !== 'seeker'): ?>
          <h3>Perusahaan</h3>
          <ul>
            <?php if (($user['role'] ?? '') === 'company'): ?>
              <li><a href="<?= u('/company/job_form.php') ?>">Pasang lowongan</a></li>
              <li><a href="<?= u('/company/dashboard.php') ?>">Kelola pelamar</a></li>
              <li><a href="<?= u('/company/profile.php') ?>">Profil Saya</a></li>
            <?php else: ?>
              <li><a href="<?= u('/auth/register.php?role=company') ?>">Pasang lowongan</a></li>
              <li><a href="<?= u('/auth/login.php') ?>">Kelola pelamar</a></li>
            <?php endif; ?>
          </ul>
          <h3 style="margin-top:1rem">Aksesibilitas</h3>
        <?php else: ?>
          <h3>Aksesibilitas</h3>
        <?php endif; ?>
        <p>Ukuran teks dapat diubah lewat tombol Normal / Besar di atas dan di bawah halaman.</p>
      </div>
    </div>

    <div class="site-foot__bar">
      <p><strong>SelarasKerja</strong> — Proyek Responsi 2 Praktikum Pemrograman Web I, Informatika UNSOED. &copy; <?= date('Y') ?></p>
      <div class="util__tools">
        <span class="util__label">Ukuran teks</span>
        <button type="button" data-text-size="normal" aria-pressed="true">Normal</button>
        <button type="button" data-text-size="lg" aria-pressed="false">Besar</button>
      </div>
    </div>
  </div>
</footer>

<script src="<?= u('/public/js/accessibility.js') ?>" defer></script>
</body>
</html>
