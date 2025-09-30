<?php
require_once __DIR__ . '/config.php';

// Mock database for testing without actual database
$mock_users = [
    [
        'id' => 1,
        'email' => 'admin@secure.com',
        'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password: "password"
        'role' => 'admin'
    ],
    [
        'id' => 2,
        'email' => 'user1@secure.com',
        'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password: "password"
        'role' => 'user'
    ]
];

function get_pdo_connection(): PDO {
    throw new Exception("Database not configured. Please install MySQL or SQLite.");
}

function mock_login(string $email, string $password): bool {
    global $mock_users;
    
    foreach ($mock_users as $user) {
        if ($user['email'] === $email && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            return true;
        }
    }
    return false;
}
