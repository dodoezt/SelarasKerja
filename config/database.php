<?php
declare(strict_types=1);

// Koneksi PDO ke MariaDB/MySQL.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'selaraskerja_db';
const DB_USER = 'root';
const DB_PASS = '';

// APP_URL dipakai supaya link/aset tetap benar dari halaman mana pun.
$rootPath = str_replace('\\', '/', dirname(__DIR__));
$docRoot  = str_replace('\\', '/', rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/'));
$appUrl   = ($docRoot !== '' && str_starts_with($rootPath, $docRoot))
    ? substr($rootPath, strlen($docRoot))
    : '';
define('APP_URL', rtrim($appUrl, '/'));
define('UPLOAD_DIR', $rootPath . '/public/uploads/cv');
define('UPLOAD_URL', APP_URL . '/public/uploads/cv');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL berjalan dan database.sql sudah diimpor.');
}