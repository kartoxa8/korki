<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function start_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function current_user(): ?array
{
    start_session();
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && ($user['role'] ?? '') === 'admin';
}

function require_auth(): void
{
    if (current_user() === null) {
        header('Location: /index.php');
        exit;
    }
}

function require_admin(): void
{
    require_auth();

    if (!is_admin()) {
        header('Location: /requests.php');
        exit;
    }
}

function require_user(): void
{
    require_auth();

    if (is_admin()) {
        header('Location: /admin/requests.php');
        exit;
    }
}

function login_user(string $login, string $password): bool
{
    start_session();

    if ($login === ADMIN_LOGIN && $password === ADMIN_PASSWORD) {
        $_SESSION['user'] = [
            'id' => 0,
            'login' => ADMIN_LOGIN,
            'name' => 'Администратор',
            'role' => 'admin',
        ];

        return true;
    }

    $stmt = db()->prepare('SELECT id, login, password_hash, full_name FROM users WHERE login = ? LIMIT 1');
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'login' => $user['login'],
        'name' => $user['full_name'],
        'role' => 'user',
    ];

    return true;
}

function logout_user(): void
{
    start_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
    }

    session_destroy();
}
