<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$user   = auth_user($pdo);
$active = $active ?? '';
$flash  = take_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'SelarasKerja') ?> — SelarasKerja</title>
<meta name="description" content="Portal lowongan kerja inklusif yang mencocokkan kebutuhan akomodasi penyandang disabilitas dengan fasilitas perusahaan.">
<link rel="preload" href="<?= u('/public/fonts/Archivo-ExtraBold.woff2') ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= u('/public/css/style.css') ?>">
</head>
<body>
<a class="skip-link" href="#main">Langsung ke konten</a>

<div class="util">
  <div class="wrap util__inner">
    <p class="util__note">Portal kerja inklusif — SDG 8 &amp; SDG 10</p>
    <div class="util__tools">
      <span class="util__label" id="textsize-label">Ukuran teks</span>
      <button type="button" data-text-size="normal" aria-pressed="true" aria-describedby="textsize-label">Normal</button>
      <button type="button" data-text-size="lg" aria-pressed="false" aria-describedby="textsize-label">Besar</button>
    </div>
  </div>
</div>

<header class="site-head">
  <div class="wrap site-head__inner">
    <a class="brand" href="<?= u('/index.php') ?>">
      <svg class="brand__mark" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
        <circle cx="20" cy="20" r="6" fill="#1226aa"/>
        <path d="M20 1l5 12h-10z" fill="#de007e"/>
        <path d="M39 20l-12 5v-10z" fill="#f2c94c"/>
        <path d="M20 39l-5-12h10z" fill="#1226aa"/>
        <path d="M1 20l12-5v10z" fill="#ff74c3"/>
      </svg>
      <span>
        <span class="brand__name">SelarasKerja</span>
        <span class="brand__tag">Kerja inklusif</span>
      </span>
    </a>

    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Buka menu" hidden>
      <span class="nav-toggle__bars" aria-hidden="true"></span>
    </button>

    <nav class="nav" id="site-nav" aria-label="Navigasi utama">
      <?php if ($user === null): ?>
        <a href="<?= u('/index.php') ?>" <?= $active === 'home' ? 'aria-current="page"' : '' ?>>Beranda</a>
        <a href="<?= u('/lowongan.php') ?>" <?= ($active ?? '') === 'jobs' ? 'aria-current="page"' : '' ?>>Lowongan</a>
        <a href="<?= u('/index.php') ?>#fasilitas">Fasilitas</a>
        <a href="<?= u('/index.php') ?>#cara-kerja">Cara Kerja</a>
        <div class="nav__actions">
          <a href="<?= u('/auth/login.php') ?>">Masuk</a>
          <a href="<?= u('/auth/register.php') ?>">Daftar <span aria-hidden="true">↗</span></a>
        </div>
      <?php elseif ($user['role'] === 'seeker'): ?>
        <a href="<?= u('/index.php') ?>" <?= $active === 'home' ? 'aria-current="page"' : '' ?>>Beranda</a>
        <a href="<?= u('/lowongan.php') ?>" <?= $active === 'jobs' ? 'aria-current="page"' : '' ?>>Lowongan</a>
        <a href="<?= u('/seeker/applications.php') ?>" <?= $active === 'apps' ? 'aria-current="page"' : '' ?>>Lamaran Saya</a>
        <a href="<?= u('/seeker/profile.php') ?>" <?= $active === 'profile' ? 'aria-current="page"' : '' ?>>Profil</a>
        <div class="nav__actions">
          <span class="nav__user"><?= e($user['name']) ?></span>
          <a href="<?= u('/auth/logout.php') ?>">Keluar <span aria-hidden="true">↗</span></a>
        </div>
      <?php else: ?>
        <a href="<?= u('/index.php') ?>" <?= $active === 'home' ? 'aria-current="page"' : '' ?>>Beranda</a>
        <a href="<?= u('/company/dashboard.php') ?>" <?= $active === 'dash' ? 'aria-current="page"' : '' ?>>Lowongan Saya</a>
        <a href="<?= u('/company/job_form.php') ?>" <?= $active === 'new' ? 'aria-current="page"' : '' ?>>Pasang Lowongan</a>
        <a href="<?= u('/company/profile.php') ?>" <?= $active === 'profile' ? 'aria-current="page"' : '' ?>>Profil</a>
        <div class="nav__actions">
          <span class="nav__user"><?= e($user['name']) ?></span>
          <a href="<?= u('/auth/logout.php') ?>">Keluar <span aria-hidden="true">↗</span></a>
        </div>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main id="main">
<?php if ($flash): ?>
  <div class="wrap">
    <p class="alert alert--<?= $flash['type'] === 'ok' ? 'ok' : 'err' ?>" role="status"><?= e($flash['message']) ?></p>
  </div>
<?php endif; ?>
