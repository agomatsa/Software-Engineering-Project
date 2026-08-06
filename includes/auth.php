<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function has_role(string $role): bool
{
    $user = current_user();

    if ($user === null) {
        return false;
    }

    if ($user['role'] === 'admin') {
        return true;
    }

    return $user['role'] === $role;
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('/admin/login.php');
    }
}

function require_role(string $role): void
{
    require_login();

    if (!has_role($role)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function attempt_login(string $username, string $password): bool
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
    ];

    return true;
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function require_admin_auth(): void
{
    require_role('admin');
}
