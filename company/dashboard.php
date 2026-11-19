<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$user    = require_role($pdo, 'company');
$company = company_row($pdo, (int) $user['id']);

if ($company === null) {
    flash('err', 'Lengkapi profil perusahaan dulu sebelum memasang lowongan.');
    redirect('/company/profile.php');
}

$stmt = $pdo->prepare(
    'SELECT j.*,
            (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS applicants,
            (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.status = ?) AS new_applicants
     FROM jobs j
     WHERE j.company_id = ?
     ORDER BY j.created_at DESC'
);
$stmt->execute(['pending', (int) $company['id']]);
$jobs = $stmt->fetchAll();

$pageTitle = 'Lowongan saya';
$active    = 'dash';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="wrap">
    <div class="section__head">
      <div>
        <p class="kicker"><?= e($company['company_name']) ?></p>
        <h1 class="section__title">Lowongan saya</h1>
      </div>
      <a class="btn" href="<?= u('/company/job_form.php') ?>">Pasang lowongan <span class="btn__arrow" aria-hidden="true">↗</span></a>
    </div>

    <?php if ($jobs === []): ?>
      <p class="empty">Belum ada lowongan. <a href="<?= u('/company/job_form.php') ?>">Pasang lowongan pertama</a>.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <caption class="visually-hidden">Daftar lowongan milik perusahaan</caption>
          <thead>
            <tr>
              <th scope="col">Lowongan</th>
              <th scope="col">Jenis</th>
              <th scope="col">Status</th>
              <th scope="col">Pelamar</th>
              <th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($jobs as $job): ?>
              <tr>
                <td>
                  <strong><?= e($job['title']) ?></strong>
                  <span class="table__sub">Dibuat <?= e(date('d M Y', strtotime($job['created_at']))) ?></span>
                </td>
                <td><?= e($job['job_type']) ?></td>
                <td>
                  <span class="status <?= $job['status'] === 'open' ? 'status--accepted' : 'status--rejected' ?>">
                    <?= $job['status'] === 'open' ? 'Dibuka' : 'Ditutup' ?>
                  </span>
                </td>
                <td>
                  <a href="<?= u('/company/applicants.php?job_id=' . (int) $job['id']) ?>">
                    <?= (int) $job['applicants'] ?> pelamar
                  </a>
                  <?php if ((int) $job['new_applicants'] > 0): ?>
                    <span class="table__sub"><?= (int) $job['new_applicants'] ?> baru</span>
                  <?php endif; ?>
                </td>
                <td class="table__actions">
                  <a class="btn btn--ghost" href="<?= u('/company/job_form.php?id=' . (int) $job['id']) ?>">Edit</a>
                  <form method="post" action="<?= u('/company/job_delete.php') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $job['id'] ?>">
                    <button class="btn btn--danger" type="submit">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>