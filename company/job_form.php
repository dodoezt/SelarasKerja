<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$user    = require_role($pdo, 'company');
$company = company_row($pdo, (int) $user['id']);

if ($company === null) {
    flash('err', 'Lengkapi profil perusahaan dulu.');
    redirect('/company/profile.php');
}

$id     = get_int('id');
$types  = ['Penuh waktu', 'Paruh waktu', 'Kontrak', 'Magang', 'Remote'];
$errors = [];

$form = [
    'title'       => '',
    'description' => '',
    'salary'      => '',
    'job_type'    => 'Penuh waktu',
    'status'      => 'open',
];
$selected = [];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM jobs WHERE id = ? AND company_id = ?');
    $stmt->execute([$id, (int) $company['id']]);
    $job = $stmt->fetch();

    if (!$job) {
        flash('err', 'Lowongan tidak ditemukan.');
        redirect('/company/dashboard.php');
    }
    $form     = $job;
    $selected = job_facilities_map($pdo, [$id])[$id] ?? [];
}

if (is_post()) {
    $id = get_int('id');

    $form = [
        'title'       => trim((string) ($_POST['title'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
        'salary'      => trim((string) ($_POST['salary'] ?? '')),
        'job_type'    => (string) ($_POST['job_type'] ?? 'Penuh waktu'),
        'status'      => (string) ($_POST['status'] ?? 'open'),
    ];
    $selected = array_map('intval', (array) ($_POST['facilities'] ?? []));

    if (!csrf_ok()) {
        $errors[] = 'Sesi kedaluwarsa. Coba simpan ulang.';
    }
    if ($form['title'] === '' || mb_strlen($form['title']) > 150) {
        $errors[] = 'Judul lowongan wajib diisi, maksimal 150 karakter.';
    }
    if ($form['description'] === '') {
        $errors[] = 'Deskripsi pekerjaan wajib diisi.';
    }
    if (!in_array($form['job_type'], $types, true)) {
        $errors[] = 'Jenis pekerjaan tidak dikenal.';
    }
    if (!in_array($form['status'], ['open', 'closed'], true)) {
        $errors[] = 'Status tidak dikenal.';
    }
    if (mb_strlen($form['salary']) > 60) {
        $errors[] = 'Keterangan gaji maksimal 60 karakter.';
    }

    if ($id > 0) {
        $own = $pdo->prepare('SELECT id FROM jobs WHERE id = ? AND company_id = ?');
        $own->execute([$id, (int) $company['id']]);
        if (!$own->fetch()) {
            $errors[] = 'Lowongan bukan milik akun ini.';
        }
    }

    if ($errors === []) {
        $pdo->beginTransaction();

        if ($id > 0) {
            $pdo->prepare(
                'UPDATE jobs SET title = ?, description = ?, salary = ?, job_type = ?, status = ?
                 WHERE id = ? AND company_id = ?'
            )->execute([$form['title'], $form['description'], $form['salary'], $form['job_type'], $form['status'], $id, (int) $company['id']]);
        } else {
            $pdo->prepare(
                'INSERT INTO jobs (company_id, title, description, salary, job_type, status)
                 VALUES (?, ?, ?, ?, ?, ?)'
            )->execute([(int) $company['id'], $form['title'], $form['description'], $form['salary'], $form['job_type'], $form['status']]);
            $id = (int) $pdo->lastInsertId();
        }

        $pdo->prepare('DELETE FROM job_accommodations WHERE job_id = ?')->execute([$id]);
        $insert = $pdo->prepare('INSERT INTO job_accommodations (job_id, accommodation_id) VALUES (?, ?)');
        foreach (array_unique($selected) as $accId) {
            $insert->execute([$id, $accId]);
        }

        $pdo->commit();
        flash('ok', $form['status'] === 'open' ? 'Lowongan disimpan dan tampil untuk pelamar.' : 'Lowongan disimpan sebagai ditutup.');
        redirect('/company/dashboard.php');
    }
}

$master  = accommodation_names($pdo);

$pageTitle = $id > 0 ? 'Edit lowongan' : 'Pasang lowongan';
$active    = $id > 0 ? 'dash' : 'new';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="wrap">
    <p class="kicker"><?= e($company['company_name']) ?></p>
    <h1 class="section__title"><?= $id > 0 ? 'Edit lowongan' : 'Pasang lowongan' ?></h1>
    <p class="section__lede">Centang fasilitas yang benar-benar tersedia. Ini yang dicocokkan dengan kebutuhan pelamar.</p>

    <?php foreach ($errors as $error): ?>
      <p class="alert alert--err" role="alert"><?= e($error) ?></p>
    <?php endforeach; ?>

    <form class="form panel panel--wide" method="post">
      <?= csrf_field() ?>
      <?php if ($id > 0): ?>
        <input type="hidden" name="id" value="<?= $id ?>">
      <?php endif; ?>

      <div class="field">
        <label for="title">Judul lowongan</label>
        <input id="title" name="title" type="text" maxlength="150" required value="<?= e($form['title']) ?>">
      </div>

      <div class="grid-form">
        <div class="field">
          <label for="job_type">Jenis pekerjaan</label>
          <select id="job_type" name="job_type">
            <?php foreach ($types as $type): ?>
              <option value="<?= e($type) ?>" <?= $form['job_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="salary">Gaji / rentang gaji (opsional)</label>
          <input id="salary" name="salary" type="text" maxlength="60" value="<?= e($form['salary']) ?>" placeholder="Rp4.500.000">
        </div>
        <div class="field">
          <label for="status">Status lowongan</label>
          <select id="status" name="status">
            <option value="open" <?= $form['status'] === 'open' ? 'selected' : '' ?>>Dibuka</option>
            <option value="closed" <?= $form['status'] === 'closed' ? 'selected' : '' ?>>Ditutup</option>
          </select>
        </div>
      </div>

      <div class="field">
        <label for="description">Deskripsi pekerjaan</label>
        <textarea id="description" name="description" rows="8" required><?= e($form['description']) ?></textarea>
      </div>

      <fieldset class="fieldset">
        <legend>Fasilitas akomodasi yang disediakan</legend>
        <p class="form-note">Centang fasilitas yang benar-benar tersedia di perusahaanmu.</p>
        <ul class="checks">
          <?php foreach ($master as $accId => $row): ?>
            <li>
              <label class="check">
                <input type="checkbox" name="facilities[]" value="<?= $accId ?>" <?= in_array($accId, $selected, true) ? 'checked' : '' ?>>
                <span><?= e($row['name']) ?></span>
              </label>
            </li>
          <?php endforeach; ?>
        </ul>
      </fieldset>

      <button class="btn" type="submit">Simpan lowongan <span class="btn__arrow" aria-hidden="true">↗</span></button>
    </form>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>