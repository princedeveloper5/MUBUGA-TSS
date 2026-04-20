<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    $sessionDirectory = dirname(__DIR__) . '/storage/sessions';

    if (!is_dir($sessionDirectory)) {
        mkdir($sessionDirectory, 0777, true);
    }

    session_save_path($sessionDirectory);
    session_start();
}

function adminIsLoggedIn(): bool
{
    return !empty($_SESSION['admin_user']);
}

function requireAdminLogin(): void
{
    if (!adminIsLoggedIn()) {
        header('Location: /MUBUGA-TSS/admin/index.php');
        exit;
    }
}

function currentAdmin(): ?array
{
    return $_SESSION['admin_user'] ?? null;
}

function attemptAdminLogin(string $email, string $password): bool
{
    $pdo = getDatabaseConnection();

    if (!$pdo instanceof PDO) {
        return false;
    }

    $statement = $pdo->prepare('SELECT id, full_name, email, password_hash, role, is_active FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if (!$user || (int) $user['is_active'] !== 1) {
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    $_SESSION['admin_user'] = [
        'id' => $user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    $update = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
    $update->execute(['id' => $user['id']]);

    return true;
}

function adminLogout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function adminCsrfToken(): string
{
    if (empty($_SESSION['admin_csrf_token']) || !is_string($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['admin_csrf_token'];
}

function adminVerifyCsrfToken(?string $token): bool
{
    $submittedToken = trim((string) $token);
    $sessionToken = (string) ($_SESSION['admin_csrf_token'] ?? '');

    if ($submittedToken === '' || $sessionToken === '') {
        return false;
    }

    return hash_equals($sessionToken, $submittedToken);
}
