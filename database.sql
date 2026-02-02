-- Создание базы данных
CREATE DATABASE IF NOT EXISTS underground_psy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE underground_psy;

-- Таблица пользователей
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('CLIENT', 'PSYCHOLOGIST', 'ADMIN') DEFAULT 'CLIENT',
    is_blocked BOOLEAN DEFAULT FALSE,
    blocked_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Пример добавления тестового пользователя (опционально)
-- Пароль: password123
-- INSERT INTO users (name, email, password_hash, role) VALUES
-- ('Тестовый Клиент', 'client@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CLIENT'),
-- ('Тестовый Психолог', 'psychologist@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'PSYCHOLOGIST');
