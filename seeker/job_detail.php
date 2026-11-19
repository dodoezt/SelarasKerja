<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$user  = require_role($pdo, 'seeker');
$id    = get_int('id');
$needs = seeker_needs($pdo, (int) $user['id']);

$stmt = $pdo->prepare(
    'SELECT j.*, c.company_name, c.location, c.description AS company_description
     FROM jobs j JOIN companies c ON c.id = j.company_id
     WHERE j.id = ? AND j.status = ?'
);
$stmt->execute([$id, 'open']);
$job = $stmt->fetch();

if (!$job) {
    http_response_code(404);
    $pageTitle = 'Lowongan tidak ditemukan';
    $active    = 'dash';
    require __DIR__ . '/../includes/header.php';
    echo '<section class="section"><div class="wrap"><h1 class="section__title">Lowongan tidak ditemukan</h1>'
        . '<p class="section__lede">Lowongan mungkin sudah ditutup. <a href="' . u('/seeker/dashboard.php') . '">Kembali ke daftar</a>.</p></div></section>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$master      = accommodation_names($pdo);
$facilities  = job_facilities_map($pdo, [$id])[$id] ?? [];
$stats       = match_stats($needs, $facilities);
$errors      = [];

$stmt = $pdo->prepare('SELECT status, applied_at FROM applications WHERE job_id = ? AND seeker_id = ?');
$stmt->execute([$id, (int) $user['id']]);
$application = $stmt->fetch();

if (is_post() && !$application) {
    $note = trim((string) ($_POST['cover_note'] ?? ''));

    if (!csrf_ok()) {
        $errors[] = 'Sesi kedaluwarsa. Coba kirim ulang.';
    }
    if (mb_strlen($note) > 1000) {
        $errors[] = 'Catatan lamaran maksimal 1000 karakter.';
    }

    if ($errors === []) {
        try {
            $pdo->prepare('INSERT INTO applications (job_id, seeker_id, cover_note) VALUES (?, ?, ?)')
                ->execute([$id, (int) $user['id'], $note]);
            flash('ok', 'Lamaran terkirim. Pantau statusnya di halaman Lamaran Saya.');
            redirect('/seeker/applications.php');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'Kamu sudah pernah melamar lowongan ini.';
            } else {
                throw $e;
            }
        }
    }
}

$pageTitle = $job['title'];
$active    = 'dash';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="wrap">
    <p class="kicker"><a href="<?= u('/seeker/dashboard.php') ?>">Lowongan</a> / detail</p>
    <h1 class="section__title"><?= e($job['title']) ?></h1>
    <p class="job__meta">
      <span><?= e($job['company_name']) ?></span>
      <span><?= e($job['location'] ?: 'Lokasi tidak disebut') ?></span>
      <span><?= e($job['job_type']) ?></span>
      <?php if ($job['salary']): ?><span><?= e($job['salary']) ?></span><?php endif; ?>
    </p>

    <?php foreach ($errors as $error): ?>
      <p class="alert alert--err" role="alert"><?= e($error) ?></p>
    <?php endforeach; ?>

    <div class="detail">
      <div class="detail__main panel">
        <h2 class="detail__title">Deskripsi pekerjaan</h2>
        <p><?= nl2br(e($job['description'])) ?></p>
        <?php if ($job['company_description']): ?>
          <h2 class="detail__title">Tentang perusahaan</h2>
          <p><?= nl2br(e($job['company_description'])) ?></p>
        <?php endif; ?>
      </div>

      <aside class="detail__side panel" aria-labelledby="match-title">
        <h2 class="detail__title" id="match-title">Kecocokan fasilitas</h2>

        <?php if ($stats['total'] === 0): ?>
          <p class="alert alert--warn">Isi <a href="<?= u('/seeker/profile.php') ?>">kebutuhan fasilitasmu</a> untuk melihat kecocokan.</p>
        <?php else: ?>
          <p class="match__score">Cocok <?= (int) $stats['count'] ?> dari <?= (int) $stats['total'] ?> kebutuhanmu (<?= (int) $stats['percent'] ?>%)</p>

          <h3 class="detail__sub">Terpenuhi</h3>
          <ul class="fac-list">
            <?php if ($stats['matched'] === []): ?>
              <li class="fac-list__empty">Belum ada fasilitas yang cocok.</li>
            <?php endif; ?>
            <?php foreach ($stats['matched'] as $fid): ?>
              <li class="fac fac--yes"><span aria-hidden="true">✓</span> <?= e($master[$fid]['name'] ?? '') ?></li>
            <?php endforeach; ?>
          </ul>

          <h3 class="detail__sub">Belum terpenuhi</h3>
          <ul class="fac-list">
            <?php $missing = array_diff($needs, $facilities); ?>
            <?php if ($missing === []): ?>
              <li class="fac-list__empty">Semua kebutuhanmu tersedia.</li>
            <?php endif; ?>
            <?php foreach ($missing as $fid): ?>
              <li class="fac fac--no"><span aria-hidden="true">✕</span> <?= e($master[$fid]['name'] ?? '') ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <h3 class="detail__sub">Fasilitas yang disediakan</h3>
        <ul class="chips">
          <?php foreach ($facilities as $fid): ?>
            <li class="chip"><?= e($master[$fid]['name'] ?? '') ?></li>
          <?php endforeach; ?>
          <?php if ($facilities === []): ?>
            <li class="chip chip--muted">Belum dicantumkan</li>
          <?php endif; ?>
        </ul>
      </aside>
    </div>

    <div class="panel panel--wide apply">
      <?php if ($application): ?>
        <h2 class="detail__title">Status lamaranmu</h2>
        <p class="status <?= e(status_class($application['status'])) ?>"><?= e(status_label($application['status'])) ?></p>
        <p class="form-note">Dikirim <?= e(date('d M Y H:i', strtotime($application['applied_at']))) ?>.</p>
      <?php else: ?>
        <h2 class="detail__title">Lamar lowongan ini</h2>
        <form class="form" method="post">
          <?= csrf_field() ?>
          <div class="field">
            <label for="cover_note">Catatan lamaran (opsional)</label>
            <textarea id="cover_note" name="cover_note" rows="4" maxlength="1000"></textarea>
          </div>
          <p class="form-note">CV dari profilmu ikut dikirim ke perusahaan. Pastikan sudah diunggah.</p>
          <button class="btn" type="submit">Kirim lamaran <span class="btn__arrow" aria-hidden="true">↗</span></button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>