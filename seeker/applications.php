<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$user = require_role($pdo, 'seeker');

$stmt = $pdo->prepare(
    'SELECT a.status, a.applied_at, a.cover_note, j.title, j.job_type, c.company_name
     FROM applications a
     JOIN jobs j ON j.id = a.job_id
     JOIN companies c ON c.id = j.company_id
     WHERE a.seeker_id = ?
     ORDER BY a.applied_at DESC'
);
$stmt->execute([(int) $user['id']]);
$applications = $stmt->fetchAll();

$pageTitle = 'Lamaran saya';
$active    = 'apps';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="wrap">
    <p class="kicker">Riwayat</p>
    <h1 class="section__title">Lamaran saya</h1>

    <?php if ($applications === []): ?>
      <p class="empty">Belum ada lamaran. <a href="<?= u('/seeker/dashboard.php') ?>">Lihat lowongan terbuka</a>.</p>
    <?php else: ?>
      <ul class="apps">
        <?php foreach ($applications as $row): ?>
          <li class="app panel">
            <div>
              <h2 class="job__title"><?= e($row['title']) ?></h2>
              <p class="job__meta">
                <span><?= e($row['company_name']) ?></span>
                <span><?= e($row['job_type']) ?></span>
                <span>Dikirim <?= e(date('d M Y', strtotime($row['applied_at']))) ?></span>
              </p>
              <?php if ($row['cover_note']): ?>
                <p class="form-note">Catatan: <?= e($row['cover_note']) ?></p>
              <?php endif; ?>
            </div>
            <p class="status <?= e(status_class($row['status'])) ?>"><?= e(status_label($row['status'])) ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>