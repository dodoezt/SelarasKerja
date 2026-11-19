<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth_guard.php';

$existing = auth_user($pdo);
if ($existing !== null) {
    redirect($existing['role'] === 'company' ? '/company/dashboard.php' : '/lowongan.php');
}

$errors = [];
$email  = '';

if (is_post()) {
    $email = trim((string) ($_POST['email'] ?? ''));
    $pass  = (string) ($_POST['password'] ?? '');

    if (!csrf_ok()) {
        $errors[] = 'Sesi kedaluwarsa. Coba kirim ulang.';
    }

    if ($errors === []) {
        $stmt = $pdo->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if ($row && password_verify($pass, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['uid']  = (int) $row['id'];
            $_SESSION['role'] = $row['role'];

            flash('ok', 'Selamat datang, ' . $row['name'] . '.');
            redirect($row['role'] === 'company' ? '/company/dashboard.php' : '/lowongan.php');
        }
        // Pesan sama untuk email tidak dikenal maupun sandi salah.
        $errors[] = 'Email atau kata sandi salah.';
    }
}

$pageTitle = 'Masuk';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="wrap narrow">
    <p class="kicker">Akun</p>
    <h1 class="section__title">Masuk</h1>
    <p class="section__lede">Gunakan email dan kata sandi yang sudah terdaftar.</p>

    <?php foreach ($errors as $error): ?>
      <p class="alert alert--err" role="alert"><?= e($error) ?></p>
    <?php endforeach; ?>

    <form class="form panel" method="post">
      <?= csrf_field() ?>
      <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required value="<?= e($email) ?>" autocomplete="email">
      </div>
      <div class="field">
        <label for="password">Kata sandi</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
      </div>
      <button class="btn" type="submit">Masuk <span class="btn__arrow" aria-hidden="true">↗</span></button>
    </form>

    <p class="form-note">Belum punya akun? <a href="<?= u('/auth/register.php') ?>">Daftar di sini</a>.</p>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>