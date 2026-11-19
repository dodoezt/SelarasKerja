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
        </ul>
      </div>
      <div>
        <h3>Perusahaan</h3>
        <ul>
          <li><a href="<?= u('/auth/register.php?role=company') ?>">Pasang lowongan</a></li>
          <li><a href="<?= u('/auth/login.php') ?>">Kelola pelamar</a></li>
        </ul>
        <h3 style="margin-top:1rem">Aksesibilitas</h3>
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