<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$user    = require_role($pdo, 'company');
$company = company_row($pdo, (int) $user['id']);

if ($company === null) {
    redirect('/company/profile.php');
}

$jobId = get_int('job_id');
$stmt  = $pdo->prepare('SELECT * FROM jobs WHERE id = ? AND company_id = ?');
$stmt->execute([$jobId, (int) $company['id']]);
$job = $stmt->fetch();

if (!$job) {
    flash('err', 'Lowongan tidak ditemukan.');
    redirect('/company/dashboard.php');
}

$statuses = ['pending', 'reviewed', 'accepted', 'rejected'];

// Ubah status lamaran (hanya untuk lamaran pada lowongan ini).
if (is_post()) {
    $appId  = get_int('application_id');
    $status = (string) ($_POST['status'] ?? '');

    if (!csrf_ok()) {
        flash('err', 'Sesi kedaluwarsa.');
    } elseif (!in_array($status, $statuses, true)) {
        flash('err', 'Status tidak dikenal.');
    } else {
        $upd = $pdo->prepare(
            'UPDATE applications a JOIN jobs j ON j.id = a.job_id
             SET a.status = ? WHERE a.id = ? AND j.company_id = ?'
        );
        $upd->execute([$status, $appId, (int) $company['id']]);
        flash($upd->rowCount() > 0 ? 'ok' : 'err', $upd->rowCount() > 0 ? 'Status lamaran diperbarui.' : 'Lamaran tidak ditemukan.');
    }
    redirect('/company/applicants.php?job_id=' . $jobId);
}

$stmt = $pdo->prepare(
    'SELECT a.id, a.status, a.applied_at, a.cover_note,
            u.id AS seeker_id, u.name, u.email,
            p.skills, p.cv_file, p.phone
     FROM applications a
     JOIN users u ON u.id = a.seeker_id
     LEFT JOIN seeker_profiles p ON p.user_id = u.id
     WHERE a.job_id = ?
     ORDER BY a.applied_at DESC'
);
$stmt->execute([$jobId]);
$applications = $stmt->fetchAll();

$jobFacilities = job_facilities_map($pdo, [$jobId])[$jobId] ?? [];

// Kebutuhan tiap pelamar dalam satu query.
$needsBySeeker = [];
if ($applications !== []) {
    $ids  = array_map(fn ($a) => (int) $a['seeker_id'], $applications);
    $in   = implode(',', array_fill(0, count($ids), '?'));
    $rows = $pdo->prepare("SELECT seeker_id, accommodation_id FROM seeker_accommodations WHERE seeker_id IN ($in)");
    $rows->execute($ids);
    foreach ($rows->fetchAll() as $row) {
        $needsBySeeker[(int) $row['seeker_id']][] = (int) $row['accommodation_id'];
    }
}

$pageTitle = 'Pelamar';
$active    = 'dash';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="wrap">
    <p class="kicker"><a href="<?= u('/company/dashboard.php') ?>">Lowongan saya</a> / pelamar</p>
    <h1 class="section__title"><?= e($job['title']) ?></h1>
    <p class="section__lede">Skor dihitung dari kebutuhan pelamar yang terpenuhi oleh fasilitas lowongan ini.</p>

    <?php if ($applications === []): ?>
      <p class="empty">Belum ada pelamar untuk lowongan ini.</p>
    <?php else: ?>
      <ul class="apps">
        <?php foreach ($applications as $app): ?>
          <?php
          $stats = match_stats($needsBySeeker[(int) $app['seeker_id']] ?? [], $jobFacilities);
          ?>
          <li class="app panel">
            <div class="app__body">
              <h2 class="job__title"><?= e($app['name']) ?></h2>
              <p class="job__meta">
                <span><?= e($app['email']) ?></span>
                <?php if ($app['phone']): ?><span><?= e($app['phone']) ?></span><?php endif; ?>
                <span>Melamar <?= e(date('d M Y', strtotime($app['applied_at']))) ?></span>
              </p>
              <?php if ($app['skills']): ?>
                <p class="form-note">Keahlian: <?= e($app['skills']) ?></p>
              <?php endif; ?>
              <?php if ($app['cover_note']): ?>
                <p class="form-note">Catatan: <?= e($app['cover_note']) ?></p>
              <?php endif; ?>
              <p class="badge">Cocok <?= (int) $stats['count'] ?> dari <?= (int) $stats['total'] ?> kebutuhan pelamar (<?= (int) $stats['percent'] ?>%)</p>
              <?php if ($app['cv_file']): ?>
                <p><a href="<?= e(UPLOAD_URL . '/' . $app['cv_file']) ?>" target="_blank" rel="noopener">Unduh CV (PDF)</a></p>
              <?php else: ?>
                <p class="form-note">Pelamar belum mengunggah CV.</p>
              <?php endif; ?>
            </div>

            <form class="app__status" method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="application_id" value="<?= (int) $app['id'] ?>">
              <label for="status-<?= (int) $app['id'] ?>">Status</label>
              <select id="status-<?= (int) $app['id'] ?>" name="status">
                <?php foreach ($statuses as $status): ?>
                  <option value="<?= e($status) ?>" <?= $app['status'] === $status ? 'selected' : '' ?>><?= e(status_label($status)) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn--ghost" type="submit">Perbarui</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>