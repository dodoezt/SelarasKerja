<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth_guard.php';

$existing = auth_user($pdo);
if ($existing !== null) {
    redirect($existing['role'] === 'company' ? '/company/dashboard.php' : '/lowongan.php');
}

$errors    = [];
$email     = '';
$resetLink = null;

if (is_post()) {
    $email = trim((string) ($_POST['email'] ?? ''));

    if (!csrf_ok()) {
        $errors[] = 'Sesi kedaluwarsa. Coba kirim ulang.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Masukkan alamat email yang valid.';
    }

    if ($errors === []) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Hapus token lama milik email ini agar tidak menumpuk
            $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);

            // Buat token baru (berlaku 30 menit)
            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 1800);

            $ins = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)');
            $ins->execute([$email, $token, $expiresAt]);

            // URL untuk simulasi pengiriman link reset di localhost
            $resetLink = u('/auth/reset_password.php?token=' . urlencode($token));
        } else {
            $errors[] = 'Email tidak terdaftar di sistem kami.';
        }
    }
}

$pageTitle = 'Lupa Kata Sandi';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="wrap narrow">
    <p class="kicker">Akun</p>
    <h1 class="section__title">Lupa kata sandi</h1>
    <p class="section__lede">Masukkan email akun Anda untuk membuat tautan pengaturan ulang kata sandi.</p>

    <?php foreach ($errors as $error): ?>
      <p class="alert alert--err" role="alert"><?= e($error) ?></p>
    <?php endforeach; ?>

    <?php if ($resetLink !== null): ?>
      <div class="alert alert--ok" role="status">
        <p><strong>Tautan pemulihan berhasil dibuat!</strong></p>
        <p>Karena aplikasi berjalan di lingkungan lokal (tanpa server email), silakan gunakan tautan berikut untuk mengatur ulang kata sandi Anda (berlaku 30 menit):</p>
        <p style="margin-top: 0.75rem;">
          <a class="btn" href="<?= e($resetLink) ?>">Atur Ulang Kata Sandi Sekarang <span class="btn__arrow" aria-hidden="true">↗</span></a>
        </p>
      </div>
    <?php else: ?>
      <form class="form panel" method="post">
        <?= csrf_field() ?>
        <div class="field">
          <label for="email">Email terdaftar</label>
          <input id="email" name="email" type="email" required value="<?= e($email) ?>" autocomplete="email" placeholder="nama@contoh.com">
        </div>
        <button class="btn" type="submit">Kirim Tautan Pemulihan <span class="btn__arrow" aria-hidden="true">↗</span></button>
      </form>
    <?php endif; ?>

    <p class="form-note">Ingat kata sandi Anda? <a href="<?= u('/auth/login.php') ?>">Kembali ke halaman masuk</a>.</p>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>