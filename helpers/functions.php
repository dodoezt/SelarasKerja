<?php
declare(strict_types=1);

/** Escape output untuk mencegah XSS. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** URL internal aplikasi, mis. u('/auth/login.php'). */
function u(string $path = ''): string
{
    return APP_URL . $path;
}

function redirect(string $path): void
{
    header('Location: ' . u($path));
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Token CSRF per sesi. */
function csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_ok(): bool
{
    return hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''));
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf()) . '">';
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function status_label(string $status): string
{
    return [
        'pending'  => 'Menunggu',
        'reviewed' => 'Ditinjau',
        'accepted' => 'Diterima',
        'rejected' => 'Ditolak',
    ][$status] ?? $status;
}

function status_class(string $status): string
{
    return [
        'pending'  => 'status--pending',
        'reviewed' => 'status--reviewed',
        'accepted' => 'status--accepted',
        'rejected' => 'status--rejected',
    ][$status] ?? '';
}

/** Angka aman dari input GET maupun POST. */
function get_int(string $key, int $default = 0): int
{
    $raw = $_POST[$key] ?? $_GET[$key] ?? null;
    return $raw === null ? $default : (int) $raw;
}