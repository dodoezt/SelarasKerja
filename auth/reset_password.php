<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth_guard.php';

$existing = auth_user($pdo);
if ($existing !== null) {
    redirect($existing['role'] === 'company' ? '/company/dashboard.php' : '/lowongan.php');
}

$token  = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$errors = [];
$validReset = null;

if ($token === '') {
    $errors[] = 'Token pemulihan tidak ditemukan.';
} else {
    $stmt = $pdo->prepare('SELECT id, email, expires_at FROM password_resets WHERE token = ?');
    $stmt->execute([$token]);
    $row = $stmt->fetch();

    if (!$row) {
        $errors[] = 'Tautan pemulihan tidak valid atau sudah digunakan.';
    } elseif (strtotime($row['expires_at']) < time()) {
        $pdo->prepare('DELETE FROM password_resets WHERE id = ?')->execute([$row['id']]);
        $errors[] = 'Tautan pemulihan sudah kedaluwarsa. Silakan ajukan ulang.';
    } else {
        $validReset = $row;
    }
}

if (is_post() && $validReset !== null) {
    $pass    = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');

    if (!csrf_ok()) {
        $errors[] = 'Sesi kedaluwarsa. Coba kirim ulang.';
    } elseif (strlen($pass) < 8) {
        $errors[] = 'Kata sandi baru minimal 8 karakter.';
    } elseif ($pass !== $confirm) {
        $errors[] = 'Konfirmasi kata sandi tidak cocok.';
    }

    if ($errors === []) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // Update kata sandi user
        $upd = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
        $upd->execute([$hash, $validReset['email']]);

        // Hapus token yang sudah terpakai
        $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$validReset['email']]);

        flash('ok', 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru Anda.');
        redirect('/auth/login.php');
    }
}

$pageTitle = 'Atur Ulang Kata Sandi';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="wrap narrow">
    <p class="kicker">Akun</p>
    <h1 class="section__title">Atur ulang kata sandi</h1>
    <p class="section__lede">Buat kata sandi baru untuk akun Anda.</p>

    <?php foreach ($errors as $error): ?>
      <p class="alert alert--err" role="alert"><?= e($error) ?></p>
    <?php endforeach; ?>

    <?php if ($validReset !== null): ?>
      <form class="form panel" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <div class="field">
          <label for="email_display">Email akun</label>
          <input id="email_display" type="email" value="<?= e($validReset['email']) ?>" disabled>
        </div>

        <div class="field">
          <label for="password">Kata sandi baru (minimal 8 karakter)</label>
          <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
        </div>

        <div class="field">
          <label for="password_confirm">Ulangi kata sandi baru</label>
          <input id="password_confirm" name="password_confirm" type="password" required minlength="8" autocomplete="new-password">
        </div>

        <button class="btn" type="submit">Simpan Kata Sandi Baru <span class="btn__arrow" aria-hidden="true">↗</span></button>
      </form>
    <?php else: ?>
      <p class="form-note"><a href="<?= u('/auth/forgot_password.php') ?>">Ajukan tautan pemulihan baru</a> atau <a href="<?= u('/auth/login.php') ?>">kembali ke halaman masuk</a>.</p>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>