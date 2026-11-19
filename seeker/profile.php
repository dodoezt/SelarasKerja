<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$user    = require_role($pdo, 'seeker');
$profile = seeker_profile($pdo, (int) $user['id']);
$needs   = seeker_needs($pdo, (int) $user['id']);
$errors  = [];

if (is_post()) {
    $phone  = trim((string) ($_POST['phone'] ?? ''));
    $bio    = trim((string) ($_POST['bio'] ?? ''));
    $skills = trim((string) ($_POST['skills'] ?? ''));
    $other  = trim((string) ($_POST['other_needs'] ?? ''));
    $needs  = array_map('intval', (array) ($_POST['needs'] ?? []));

    if (!csrf_ok()) {
        $errors[] = 'Sesi kedaluwarsa. Coba simpan ulang.';
    }
    if (mb_strlen($phone) > 25) {
        $errors[] = 'Nomor telepon maksimal 25 karakter.';
    }
    if (mb_strlen($skills) > 255) {
        $errors[] = 'Keahlian maksimal 255 karakter.';
    }
    if (mb_strlen($other) > 255) {
        $errors[] = 'Catatan kebutuhan lain maksimal 255 karakter.';
    }

    $cvFile = $profile['cv_file'];

    if (!empty($_FILES['cv']['name'])) {
        $file = $_FILES['cv'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Berkas CV gagal diunggah. Coba lagi.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran CV maksimal 2 MB.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if ($finfo->file($file['tmp_name']) !== 'application/pdf') {
                $errors[] = 'CV harus berformat PDF.';
            } else {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0775, true);
                }
                $name = sprintf('cv-%d-%s.pdf', (int) $user['id'], bin2hex(random_bytes(6)));
                if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $name)) {
                    if ($cvFile && is_file(UPLOAD_DIR . '/' . $cvFile)) {
                        unlink(UPLOAD_DIR . '/' . $cvFile);
                    }
                    $cvFile = $name;
                } else {
                    $errors[] = 'CV gagal disimpan di server.';
                }
            }
        }
    }

    if ($errors === []) {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO seeker_profiles (user_id, phone, bio, skills, cv_file, other_needs)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE phone = VALUES(phone), bio = VALUES(bio),
             skills = VALUES(skills), cv_file = VALUES(cv_file), other_needs = VALUES(other_needs)'
        );
        $stmt->execute([(int) $user['id'], $phone, $bio, $skills, $cvFile, $other]);

        $pdo->prepare('DELETE FROM seeker_accommodations WHERE seeker_id = ?')->execute([(int) $user['id']]);
        if ($needs !== []) {
            $insert = $pdo->prepare('INSERT INTO seeker_accommodations (seeker_id, accommodation_id) VALUES (?, ?)');
            foreach (array_unique($needs) as $accId) {
                $insert->execute([(int) $user['id'], $accId]);
            }
        }

        $pdo->commit();
        flash('ok', 'Profil dan kebutuhan akomodasi tersimpan.');
        redirect('/seeker/profile.php');
    }

    // Kembalikan isi form kalau validasi gagal.
    $profile = array_merge($profile, ['phone' => $phone, 'bio' => $bio, 'skills' => $skills, 'other_needs' => $other]);
}

$master = accommodation_names($pdo);
$grouped = [];
foreach ($master as $id => $row) {
    $grouped[$row['category']][$id] = $row['name'];
}

$pageTitle = 'Profil pelamar';
$active    = 'profile';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="wrap">
    <p class="kicker">Akun pelamar</p>
    <h1 class="section__title">Profil &amp; kebutuhan akomodasi</h1>
    <p class="section__lede">Bagian yang kamu centang di sini dipakai untuk menghitung skor kecocokan setiap lowongan.</p>

    <?php foreach ($errors as $error): ?>
      <p class="alert alert--err" role="alert"><?= e($error) ?></p>
    <?php endforeach; ?>

    <form class="form panel panel--wide" method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <div class="grid-form">
        <div class="field">
          <label for="phone">Nomor telepon</label>
          <input id="phone" name="phone" type="tel" maxlength="25" value="<?= e($profile['phone']) ?>" autocomplete="tel">
        </div>
        <div class="field">
          <label for="skills">Keahlian (pisahkan dengan koma)</label>
          <input id="skills" name="skills" type="text" maxlength="255" value="<?= e($profile['skills']) ?>">
        </div>
      </div>

      <div class="field">
        <label for="bio">Tentang saya</label>
        <textarea id="bio" name="bio" rows="5"><?= e($profile['bio']) ?></textarea>
      </div>

      <div class="field">
        <label for="cv">CV (PDF, maksimal 2 MB)</label>
        <input id="cv" name="cv" type="file" accept="application/pdf">
        <?php if (!empty($profile['cv_file'])): ?>
          <p class="form-note">CV tersimpan: <a href="<?= e(UPLOAD_URL . '/' . $profile['cv_file']) ?>" target="_blank" rel="noopener">lihat berkas</a>. Unggah baru untuk mengganti.</p>
        <?php endif; ?>
      </div>

      <fieldset class="fieldset">
        <legend>Kebutuhan fasilitas</legend>
        <p class="form-note">Centang fasilitas yang kamu perlukan. Kosongkan kalau tidak ada.</p>
        <?php foreach ($grouped as $category => $items): ?>
          <div class="fieldset__group">
            <p class="fieldset__label"><?= e($category) ?></p>
            <ul class="checks">
              <?php foreach ($items as $id => $label): ?>
                <li>
                  <label class="check">
                    <input type="checkbox" name="needs[]" value="<?= $id ?>" <?= in_array($id, $needs, true) ? 'checked' : '' ?>>
                    <span><?= e($label) ?></span>
                  </label>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endforeach; ?>
      </fieldset>

      <div class="field">
        <label for="other_needs">Kebutuhan lain (opsional)</label>
        <input id="other_needs" name="other_needs" type="text" maxlength="255" value="<?= e($profile['other_needs']) ?>">
      </div>

      <button class="btn" type="submit">Simpan profil <span class="btn__arrow" aria-hidden="true">↗</span></button>
    </form>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>