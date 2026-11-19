<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth_guard.php';

if (auth_user($pdo) !== null) {
    redirect('/index.php');
}

$errors = [];
$name   = '';
$email  = '';
$role   = $_POST['role'] ?? $_GET['role'] ?? 'seeker';
$role   = in_array($role, ['seeker', 'company'], true) ? $role : 'seeker';

if (is_post()) {
    $name    = trim((string) ($_POST['name'] ?? ''));
    $email   = trim((string) ($_POST['email'] ?? ''));
    $pass    = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');

    if (!csrf_ok()) {
        $errors[] = 'Sesi kedaluwarsa. Coba kirim ulang formulir.';
    }
    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = 'Nama wajib diisi, maksimal 100 karakter.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (strlen($pass) < 8) {
        $errors[] = 'Kata sandi minimal 8 karakter.';
    }
    if ($pass !== $confirm) {
        $errors[] = 'Konfirmasi kata sandi tidak sama.';
    }

    if ($errors === []) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'Email sudah terdaftar. Silakan masuk.';
        }
    }

    if ($errors === []) {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $role]);
        $uid = (int) $pdo->lastInsertId();

        if ($role === 'seeker') {
            $pdo->prepare('INSERT INTO seeker_profiles (user_id) VALUES (?)')->execute([$uid]);
        } else {
            $pdo->prepare('INSERT INTO companies (user_id, company_name) VALUES (?, ?)')->execute([$uid, $name]);
        }
        $pdo->commit();

        session_regenerate_id(true);
        $_SESSION['uid']  = $uid;
        $_SESSION['role'] = $role;

        flash('ok', 'Akun dibuat. Lengkapi profilmu dulu supaya kecocokan lowongan bisa dihitung.');
        redirect($role === 'seeker' ? '/seeker/profile.php' : '/company/profile.php');
    }
}

$pageTitle = 'Daftar';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="wrap narrow">
    <p class="kicker">Buat akun</p>
    <h1 class="section__title">Daftar</h1>
    <p class="section__lede">Pilih peran sesuai kebutuhanmu. Satu email untuk satu akun.</p>

    <?php foreach ($errors as $error): ?>
      <p class="alert alert--err" role="alert"><?= e($error) ?></p>
    <?php endforeach; ?>

    <form class="form panel" method="post" novalidate>
      <?= csrf_field() ?>
      <div class="field">
        <label for="role">Daftar sebagai</label>
        <select id="role" name="role">
          <option value="seeker" <?= $role === 'seeker' ? 'selected' : '' ?>>Pelamar (penyandang disabilitas)</option>
          <option value="company" <?= $role === 'company' ? 'selected' : '' ?>>Perusahaan</option>
        </select>
      </div>
      <div class="field">
        <label for="name">Nama lengkap / nama perusahaan</label>
        <input id="name" name="name" type="text" maxlength="100" required value="<?= e($name) ?>" autocomplete="name">
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" maxlength="150" required value="<?= e($email) ?>" autocomplete="email">
      </div>
      <div class="grid-form">
        <div class="field">
          <label for="password">Kata sandi (min. 8 karakter)</label>
          <input id="password" name="password" type="password" minlength="8" required autocomplete="new-password">
        </div>
        <div class="field">
          <label for="password_confirm">Ulangi kata sandi</label>
          <input id="password_confirm" name="password_confirm" type="password" minlength="8" required autocomplete="new-password">
        </div>
      </div>
      <button class="btn" type="submit">Buat akun <span class="btn__arrow" aria-hidden="true">↗</span></button>
    </form>

    <p class="form-note">Sudah punya akun? <a href="<?= u('/auth/login.php') ?>">Masuk di sini</a>.</p>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>