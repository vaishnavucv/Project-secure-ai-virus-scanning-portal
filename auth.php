<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!current_user()) {
        header('Location: /login.php');
        exit;
    }
}

function require_admin(): void {
    require_login();
    if (current_user()['role'] !== 'admin') {
        http_response_code(403);
        echo 'Forbidden: Admins only';
        exit;
    }
}

function login(string $email, string $password): bool {
    $pdo = get_pdo_connection();
    $stmt = $pdo->prepare('SELECT id, email, password_hash, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        return true;
    }
    return false;
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function create_user(string $email, string $password, string $role = 'user'): bool {
    if (!in_array($role, ['admin', 'user'], true)) {
        $role = 'user';
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $pdo = get_pdo_connection();
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)');
    try {
        return $stmt->execute([$email, $hash, $role]);
    } catch (PDOException $e) {
        return false;
    }
}


