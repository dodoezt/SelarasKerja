<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$user     = require_role($pdo, 'seeker');
$seekerId = (int) $user['id'];
$needs    = seeker_needs($pdo, $seekerId);
$master   = accommodation_names($pdo);

$stmt = $pdo->prepare(
    'SELECT j.*, c.company_name, c.location, sj.saved_at
     FROM saved_jobs sj
     JOIN jobs j ON j.id = sj.job_id
     JOIN companies c ON c.id = j.company_id
     WHERE sj.seeker_id = ? AND j.status = ?
     ORDER BY sj.saved_at DESC'
);
$stmt->execute([$seekerId, 'open']);
$savedJobs = $stmt->fetchAll();

$jobIds = array_map(fn ($r) => (int) $r['id'], $savedJobs);
$facMap = $jobIds !== [] ? job_facilities_map($pdo, $jobIds) : [];

$pageTitle = 'Lowongan tersimpan';
$active    = 'saved';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="wrap">
    <p class="kicker">Daftar Simpanan</p>
    <h1 class="section__title">Lowongan tersimpan</h1>
    <p class="section__lede">Lowongan menarik yang kamu simpan untuk dilamar nanti.</p>

    <?php if ($savedJobs === []): ?>
      <p class="empty">Belum ada lowongan yang disimpan. <a href="<?= u('/lowongan.php') ?>">Jelajahi lowongan terbuka</a>.</p>
    <?php else: ?>
      <ul class="jobs">
        <?php foreach ($savedJobs as $job): ?>
          <?php
            $jid   = (int) $job['id'];
            $facs  = $facMap[$jid] ?? [];
            $stats = match_stats($needs, $facs);
          ?>
          <li class="job">
            <?php if ($stats['total'] > 0): ?>
              <p class="badge">Cocok <?= (int) $stats['count'] ?> dari <?= (int) $stats['total'] ?> kebutuhanmu (<?= (int) $stats['percent'] ?>%)</p>
            <?php else: ?>
              <p class="badge badge--muted">Kebutuhanmu belum diisi</p>
            <?php endif; ?>

            <h2 class="job__title">
              <a href="<?= u('/lowongan-detail.php?id=' . $jid) ?>"><?= e($job['title']) ?></a>
            </h2>
            <p class="job__meta">
              <span><?= e($job['company_name']) ?></span>
              <span><?= e($job['location'] ?: 'Lokasi tidak disebut') ?></span>
              <span><?= e($job['job_type']) ?></span>
              <span>Disimpan <?= e(date('d M Y', strtotime($job['saved_at']))) ?></span>
            </p>

            <ul class="chips">
              <?php foreach (array_slice($facs, 0, 4) as $fid): ?>
                <li class="chip <?= in_array($fid, $needs, true) ? 'chip--on' : '' ?>">
                  <?= e($master[$fid]['name'] ?? 'Fasilitas') ?>
                </li>
              <?php endforeach; ?>
            </ul>

            <div style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
              <a class="btn" href="<?= u('/lowongan-detail.php?id=' . $jid) ?>">Lihat &amp; Lamar <span class="btn__arrow" aria-hidden="true">↗</span></a>
              <form method="post" action="<?= u('/seeker/bookmark_toggle.php') ?>" style="margin: 0;">
                <?= csrf_field() ?>
                <input type="hidden" name="job_id" value="<?= $jid ?>">
                <input type="hidden" name="redirect_to" value="/seeker/saved_jobs.php">
                <button class="btn btn--ghost" type="submit" aria-label="Hapus <?= e($job['title']) ?> dari tersimpan">Hapus Simpanan</button>
              </form>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>