<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$user    = require_role($pdo, 'company');
$company = company_row($pdo, (int) $user['id']);
$errors  = [];

$form = [
    'company_name' => $company['company_name'] ?? $user['name'],
    'location'     => $company['location'] ?? '',
    'description'  => $company['description'] ?? '',
];

if (is_post()) {
    $form['company_name'] = trim((string) ($_POST['company_name'] ?? ''));
    $form['location']     = trim((string) ($_POST['location'] ?? ''));
    $form['description']  = trim((string) ($_POST['description'] ?? ''));

    if (!csrf_ok()) {
        $errors[] = 'Sesi kedaluwarsa. Coba simpan ulang.';
    }
    if ($form['company_name'] === '' || mb_strlen($form['company_name']) > 120) {
        $errors[] = 'Nama perusahaan wajib diisi, maksimal 120 karakter.';
    }
    if (mb_strlen($form['location']) > 120) {
        $errors[] = 'Lokasi maksimal 120 karakter.';
    }

    if ($errors === []) {
        $pdo->prepare(
            'INSERT INTO companies (user_id, company_name, location, description)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE company_name = VALUES(company_name),
             location = VALUES(location), description = VALUES(description)'
        )->execute([(int) $user['id'], $form['company_name'], $form['location'], $form['description']]);

        flash('ok', 'Profil perusahaan tersimpan.');
        redirect('/company/profile.php');
    }
}

$pageTitle = 'Profil perusahaan';
$active    = 'profile';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="wrap narrow">
    <p class="kicker">Akun perusahaan</p>
    <h1 class="section__title">Profil perusahaan</h1>
    <p class="section__lede">Data ini tampil pada setiap lowongan yang kamu pasang.</p>

    <?php foreach ($errors as $error): ?>
      <p class="alert alert--err" role="alert"><?= e($error) ?></p>
    <?php endforeach; ?>

    <form class="form panel" method="post">
      <?= csrf_field() ?>
      <div class="field">
        <label for="company_name">Nama perusahaan</label>
        <input id="company_name" name="company_name" type="text" maxlength="120" required value="<?= e($form['company_name']) ?>">
      </div>
      <div class="field">
        <label for="location">Lokasi</label>
        <input id="location" name="location" type="text" maxlength="120" value="<?= e($form['location']) ?>">
      </div>
      <div class="field">
        <label for="description">Deskripsi singkat</label>
        <textarea id="description" name="description" rows="5"><?= e($form['description']) ?></textarea>
      </div>
      <button class="btn" type="submit">Simpan profil <span class="btn__arrow" aria-hidden="true">↗</span></button>
    </form>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>