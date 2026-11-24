<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Detail lowongan terbuka dapat dilihat publik. Perusahaan diarahkan ke dashboard.
$user = auth_user($pdo);
if ($user !== null && $user['role'] === 'company') {
    redirect('/company/dashboard.php');
}

$jobId = get_int('id');
$stmt = $pdo->prepare(
    'SELECT j.*, c.company_name, c.location AS company_location, c.description AS company_description
     FROM jobs j
     JOIN companies c ON c.id = j.company_id
     WHERE j.id = ? AND j.status = ?'
);
$stmt->execute([$jobId, 'open']);
$job = $stmt->fetch();

if (!$job) {
    flash('err', 'Lowongan tidak ditemukan atau sudah ditutup.');
    redirect('/lowongan.php');
}

$needs = $user !== null ? seeker_needs($pdo, (int) $user['id']) : [];
$facilityIds = job_facilities_map($pdo, [$jobId])[$jobId] ?? [];
$master = accommodation_names($pdo);
$stats = match_stats($needs, $facilityIds);
$saved = false;
$application = null;

if ($user !== null) {
    $savedStmt = $pdo->prepare('SELECT 1 FROM saved_jobs WHERE seeker_id = ? AND job_id = ?');
    $savedStmt->execute([(int) $user['id'], $jobId]);
    $saved = (bool) $savedStmt->fetchColumn();

    $applicationStmt = $pdo->prepare('SELECT status FROM applications WHERE seeker_id = ? AND job_id = ?');
    $applicationStmt->execute([(int) $user['id'], $jobId]);
    $application = $applicationStmt->fetchColumn();
}

if (is_post()) {
    if ($user === null) {
        flash('err', 'Masuk sebagai pelamar untuk mengirim lamaran.');
        redirect('/auth/login.php');
    }

    if (!csrf_ok()) {
        flash('err', 'Sesi kedaluwarsa. Silakan coba lagi.');
        redirect('/lowongan-detail.php?id=' . $jobId);
    }

    if ($application !== false && $application !== null) {
        flash('err', 'Kamu sudah melamar lowongan ini.');
    } else {
        $coverNote = trim((string) ($_POST['cover_note'] ?? ''));
        $insert = $pdo->prepare('INSERT INTO applications (job_id, seeker_id, cover_note) VALUES (?, ?, ?)');
        $insert->execute([$jobId, (int) $user['id'], $coverNote !== '' ? $coverNote : null]);
        flash('ok', 'Lamaran berhasil dikirim. Kamu bisa memantau statusnya di Lamaran Saya.');
    }
    redirect('/lowongan-detail.php?id=' . $jobId);
}

$pageTitle = $job['title'];
$active = 'jobs';
require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="wrap">
    <p class="kicker"><a href="<?= u('/lowongan.php') ?>">Jelajah lowongan</a> / detail</p>
    <div class="section__head">
      <div>
        <p class="kicker"><?= e($job['company_name']) ?></p>
        <h1 class="section__title"><?= e($job['title']) ?></h1>
      </div>
      <p class="section__lede"><?= e($job['company_location'] ?: 'Lokasi tidak disebut') ?> · <?= e($job['job_type']) ?></p>
    </div>

    <div class="detail">
      <article class="panel detail__main">
        <p class="detail__sub">Tentang pekerjaan</p>
        <div><?= nl2br(e($job['description'])) ?></div>

        <?php if (!empty($job['salary'])): ?>
          <h2 class="detail__title">Gaji</h2>
          <p><?= e($job['salary']) ?></p>
        <?php endif; ?>

        <?php if (!empty($job['company_description'])): ?>
          <h2 class="detail__title">Tentang perusahaan</h2>
          <div><?= nl2br(e($job['company_description'])) ?></div>
        <?php endif; ?>
      </article>

      <aside class="panel detail__side" aria-label="Kecocokan dan fasilitas">
        <h2 class="detail__title">Fasilitas akomodasi</h2>
        <?php if ($facilityIds === []): ?>
          <p class="fac-list__empty">Belum ada fasilitas yang dicantumkan.</p>
        <?php else: ?>
          <ul class="fac-list">
            <?php foreach ($facilityIds as $facilityId): ?>
              <?php $matched = $user !== null && in_array($facilityId, $needs, true); ?>
              <li class="fac <?= $user !== null ? ($matched ? 'fac--yes' : 'fac--no') : '' ?>">
                <?php if ($user !== null): ?><span aria-hidden="true"><?= $matched ? '✓' : '×' ?></span><?php endif; ?>
                <?= e($master[$facilityId]['name'] ?? 'Fasilitas') ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php if ($user === null): ?>
          <p class="form-note">Masuk untuk melihat kecocokan fasilitas dengan kebutuhanmu.</p>
        <?php elseif ($needs === []): ?>
          <p class="badge badge--muted">Kebutuhanmu belum diisi</p>
          <p class="form-note"><a href="<?= u('/seeker/profile.php') ?>">Lengkapi kebutuhan di profil</a> untuk melihat fasilitas yang cocok.</p>
        <?php else: ?>
          <p class="match__score">Cocok <?= (int) $stats['count'] ?> dari <?= (int) $stats['total'] ?> kebutuhanmu (<?= (int) $stats['percent'] ?>%)</p>
        <?php endif; ?>

        <?php if ($user !== null): ?>
          <form method="post" action="<?= u('/seeker/bookmark_toggle.php') ?>" class="apply">
            <?= csrf_field() ?>
            <input type="hidden" name="job_id" value="<?= $jobId ?>">
            <input type="hidden" name="redirect_to" value="/lowongan-detail.php?id=<?= $jobId ?>">
            <button class="btn <?= $saved ? '' : 'btn--ghost' ?>" type="submit" aria-pressed="<?= $saved ? 'true' : 'false' ?>">
              <?= $saved ? '★ Tersimpan' : '☆ Simpan lowongan' ?>
            </button>
          </form>
        <?php endif; ?>

        <div class="apply">
          <?php if ($user === null): ?>
            <a class="btn" href="<?= u('/auth/login.php') ?>">Masuk untuk melamar</a>
          <?php elseif ($application !== false && $application !== null): ?>
            <p class="badge">Lamaran: <?= e(status_label((string) $application)) ?></p>
            <p><a href="<?= u('/seeker/applications.php') ?>">Lihat Lamaran Saya</a></p>
          <?php else: ?>
            <form method="post" class="form">
              <?= csrf_field() ?>
              <div class="field">
                <label for="cover_note">Catatan lamaran (opsional)</label>
                <textarea id="cover_note" name="cover_note" rows="4"></textarea>
              </div>
              <button class="btn" type="submit">Kirim lamaran</button>
            </form>
          <?php endif; ?>
        </div>
      </aside>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
