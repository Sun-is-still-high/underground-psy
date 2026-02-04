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

-- ============================================
-- ИНТЕРВИЗИОННАЯ СИСТЕМА
-- ============================================

-- Группы интервизий
CREATE TABLE IF NOT EXISTS intervision_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    max_participants INT NOT NULL DEFAULT 10,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_is_active (is_active),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Сессии интервизий
CREATE TABLE IF NOT EXISTS intervision_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    topic VARCHAR(255) NOT NULL,
    description TEXT,
    scheduled_at DATETIME NOT NULL,
    duration_minutes INT NOT NULL DEFAULT 90,
    meeting_link VARCHAR(500),
    status ENUM('SCHEDULED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'SCHEDULED',
    cancelled_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (group_id) REFERENCES intervision_groups(id) ON DELETE CASCADE,
    INDEX idx_group_id (group_id),
    INDEX idx_status (status),
    INDEX idx_scheduled_at (scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Участники групп (психологи)
CREATE TABLE IF NOT EXISTS intervision_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    psychologist_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    left_at TIMESTAMP NULL,

    FOREIGN KEY (group_id) REFERENCES intervision_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (psychologist_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_group_psychologist (group_id, psychologist_id),
    INDEX idx_psychologist_id (psychologist_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Посещаемость сессий
CREATE TABLE IF NOT EXISTS intervision_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    participant_id INT NOT NULL,
    attended BOOLEAN DEFAULT FALSE,
    marked_at TIMESTAMP NULL,
    marked_by INT NULL,
    notes TEXT,

    FOREIGN KEY (session_id) REFERENCES intervision_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES intervision_participants(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_session_participant (session_id, participant_id),
    INDEX idx_session_id (session_id),
    INDEX idx_attended (attended)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Настройки интервизий
CREATE TABLE IF NOT EXISTS intervision_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Минимум сессий в месяц для права консультировать
INSERT INTO intervision_settings (setting_key, setting_value, description) VALUES
('min_monthly_sessions', '2', 'Минимальное количество интервизий в месяц для права консультировать')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
