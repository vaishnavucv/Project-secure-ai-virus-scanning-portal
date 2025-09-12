<?php
require_once __DIR__ . '/../auth.php';

$user = current_user();
if (!$user) {
    header('Location: /login.php');
    exit;
}

if ($user['role'] === 'admin') {
    header('Location: /admin.php');
    exit;
}

header('Location: /user.php');
exit;


