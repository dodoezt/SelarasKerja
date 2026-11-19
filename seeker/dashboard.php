<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$user  = require_role($pdo, 'seeker');
$needs = seeker_needs($pdo, (int) $user['id']);

$q      = trim((string) ($_GET['q'] ?? ''));
$type   = (string) ($_GET['type'] ?? '');
$accId  = get_int('acc');
$types  = ['Penuh waktu', 'Paruh waktu', 'Kontrak', 'Magang', 'Remote'];

$jobs  = jobs_with_score($pdo, $needs, ['q' => $q, 'type' => $type, 'acc' => $accId]);
$per   = 6;
$pages = max(1, (int) ceil(count($jobs) / $per));
$page  = min(max(1, get_int('page', 1)), $pages);
$shown = array_slice($jobs, ($page - 1) * $per, $per);

$master = accommodation_names($pdo);

// Buang parameter page saat menyusun link paginasi.
$query = array_filter(['q' => $q, 'type' => $type, 'acc' => $accId ?: ''], fn ($v) => $v !== '');
function page_url(array $query, int $page): string
{
    return u('/seeker/dashboard.php?' . http_build_query($query + ['page' => $page]));
}

$pageTitle = 'Lowongan';
$active    = 'dash';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="wrap">
    <div class="section__head">
      <div>
        <p class="kicker">Untukmu</p>
        <h1 class="section__title">Lowongan terbuka</h1>
      </div>
      <p class="section__lede">Diurutkan dari kecocokan tertinggi dengan kebutuhanmu.</p>
    </div>

    <?php if ($needs === []): ?>
      <p class="alert alert--warn" role="status">
        Lengkapi kebutuhan fasilitas di <a href="<?= u('/seeker/profile.php') ?>">profilmu</a> untuk melihat tingkat kecocokan.
      </p>
    <?php endif; ?>

    <form class="filters panel" method="get" action="<?= u('/seeker/dashboard.php') ?>">
      <div class="field">
        <label for="q">Kata kunci</label>
        <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="mis. administrasi">
      </div>
      <div class="field">
        <label for="type">Jenis pekerjaan</label>
        <select id="type" name="type">
          <option value="">Semua jenis</option>
          <?php foreach ($types as $option): ?>
            <option value="<?= e($option) ?>" <?= $type === $option ? 'selected' : '' ?>><?= e($option) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="acc">Fasilitas</label>
        <select id="acc" name="acc">
          <option value="">Semua fasilitas</option>
          <?php foreach ($master as $id => $row): ?>
            <option value="<?= $id ?>" <?= $accId === $id ? 'selected' : '' ?>><?= e($row['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn" type="submit">Terapkan</button>
    </form>

    <?php if ($shown === []): ?>
      <p class="empty">Belum ada lowongan yang cocok dengan filter ini. Coba longgarkan filter.</p>
    <?php else: ?>
      <ul class="jobs">
        <?php foreach ($shown as $job): ?>
          <li class="job">
            <?php if ($job['match_total'] > 0): ?>
              <p class="badge">Cocok <?= (int) $job['match_count'] ?> dari <?= (int) $job['match_total'] ?> kebutuhanmu (<?= (int) $job['match_percent'] ?>%)</p>
            <?php else: ?>
              <p class="badge badge--muted">Kebutuhanmu belum diisi</p>
            <?php endif; ?>

            <h2 class="job__title">
              <a href="<?= u('/seeker/job_detail.php?id=' . (int) $job['id']) ?>"><?= e($job['title']) ?></a>
            </h2>
            <p class="job__meta">
              <span><?= e($job['company_name']) ?></span>
              <span><?= e($job['location'] ?: 'Lokasi tidak disebut') ?></span>
              <span><?= e($job['job_type']) ?></span>
            </p>
            <ul class="chips">
              <?php foreach ($job['facility_ids'] as $fid): ?>
                <?php if (in_array($fid, $needs, true)): ?>
                  <li class="chip chip--on"><?= e($master[$fid]['name'] ?? 'Fasilitas') ?></li>
                <?php endif; ?>
              <?php endforeach; ?>
            </ul>
          </li>
        <?php endforeach; ?>
      </ul>

      <?php if ($pages > 1): ?>
        <nav class="pager" aria-label="Navigasi halaman">
          <?php if ($page > 1): ?>
            <a class="btn btn--ghost" href="<?= e(page_url($query, $page - 1)) ?>">Sebelumnya</a>
          <?php endif; ?>
          <span class="pager__info">Halaman <?= $page ?> dari <?= $pages ?> · <?= count($jobs) ?> lowongan</span>
          <?php if ($page < $pages): ?>
            <a class="btn btn--ghost" href="<?= e(page_url($query, $page + 1)) ?>">Berikutnya</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>