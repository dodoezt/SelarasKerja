<?php
declare(strict_types=1);

/** Ambil data user yang sedang login (null kalau belum). */
function auth_user(PDO $pdo): ?array
{
    static $cache = null;
    if (empty($_SESSION['uid'])) {
        return null;
    }
    if ($cache === null) {
        $stmt = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['uid']]);
        $cache = $stmt->fetch() ?: null;
    }
    return $cache;
}

/** Wajib login; kembalikan baris user. */
function require_login(PDO $pdo): array
{
    $user = auth_user($pdo);
    if ($user === null) {
        flash('err', 'Silakan masuk terlebih dahulu.');
        redirect('/auth/login.php');
    }
    return $user;
}

/** Wajib login dengan peran tertentu ('seeker' atau 'company'). */
function require_role(PDO $pdo, string $role): array
{
    $user = require_login($pdo);
    if ($user['role'] !== $role) {
        redirect($user['role'] === 'company' ? '/company/dashboard.php' : '/lowongan.php');
    }
    return $user;
}

/** Profil pelamar (dibuat otomatis saat registrasi). */
function seeker_profile(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT * FROM seeker_profiles WHERE user_id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: ['user_id' => $userId, 'phone' => '', 'bio' => '', 'skills' => '', 'cv_file' => null, 'other_needs' => ''];
}

/** Profil perusahaan milik user. */
function company_row(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM companies WHERE user_id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}