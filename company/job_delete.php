<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$user    = require_role($pdo, 'company');
$company = company_row($pdo, (int) $user['id']);

if ($company === null || !is_post() || !csrf_ok()) {
    flash('err', 'Permintaan hapus tidak valid.');
    redirect('/company/dashboard.php');
}

$id = get_int('id');
$stmt = $pdo->prepare('DELETE FROM jobs WHERE id = ? AND company_id = ?');
$stmt->execute([$id, (int) $company['id']]);

flash($stmt->rowCount() > 0 ? 'ok' : 'err', $stmt->rowCount() > 0 ? 'Lowongan dihapus.' : 'Lowongan tidak ditemukan.');
redirect('/company/dashboard.php');