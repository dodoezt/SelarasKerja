<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$user = require_role($pdo, 'seeker');

if (!is_post() || !csrf_ok()) {
    flash('err', 'Permintaan tidak valid atau sesi kedaluwarsa.');
    redirect('/lowongan.php');
}

$jobId    = get_int('job_id');
$seekerId = (int) $user['id'];

// Pastikan lowongan ada dan masih buka
$stmt = $pdo->prepare('SELECT id FROM jobs WHERE id = ? AND status = ?');
$stmt->execute([$jobId, 'open']);
if (!$stmt->fetch()) {
    flash('err', 'Lowongan tidak ditemukan atau sudah ditutup.');
    redirect('/lowongan.php');
}

// Cek apakah sudah disimpan sebelumnya
$chk = $pdo->prepare('SELECT 1 FROM saved_jobs WHERE seeker_id = ? AND job_id = ?');
$chk->execute([$seekerId, $jobId]);

if ($chk->fetch()) {
    // Hapus dari daftar simpanan
    $pdo->prepare('DELETE FROM saved_jobs WHERE seeker_id = ? AND job_id = ?')
        ->execute([$seekerId, $jobId]);
    flash('ok', 'Lowongan dihapus dari daftar tersimpan.');
} else {
    // Tambahkan ke daftar simpanan
    $pdo->prepare('INSERT INTO saved_jobs (seeker_id, job_id) VALUES (?, ?)')
        ->execute([$seekerId, $jobId]);
    flash('ok', 'Lowongan berhasil disimpan.');
}

// Kembali ke halaman asal (lowongan.php, lowongan-detail.php, atau saved_jobs.php)
$redirectTo = (string) ($_POST['redirect_to'] ?? '/lowongan.php');
if (!str_starts_with($redirectTo, '/')) {
    $redirectTo = '/lowongan.php';
}
redirect($redirectTo);