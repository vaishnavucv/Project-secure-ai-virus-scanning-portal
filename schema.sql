-- Database schema for PHP Web Portal
-- Create database (run if not existing)
CREATE DATABASE IF NOT EXISTS php_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE php_portal;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Keys for VirusTotal
CREATE TABLE IF NOT EXISTS api_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    api_key VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File scan records
CREATE TABLE IF NOT EXISTS file_scans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_path VARCHAR(255) NOT NULL,
    vt_analysis_id VARCHAR(128) NOT NULL,
    status VARCHAR(50) NOT NULL,
    verdict VARCHAR(50) DEFAULT NULL,
    malicious_count INT DEFAULT NULL,
    suspicious_count INT DEFAULT NULL,
    undetected_count INT DEFAULT NULL,
    vt_response JSON NULL,
    log TEXT NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_scans_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed demo users (password is "password")
INSERT INTO users (email, password_hash, role) VALUES
('admin@secure.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO users (email, password_hash, role) VALUES
('user1@secure.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user')
ON DUPLICATE KEY UPDATE email = VALUES(email);


