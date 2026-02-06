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

-- ============================================
-- СИСТЕМА КЕЙСОВ (ПОИСК КЛИЕНТОВ)
-- ============================================

-- Справочник типов проблем
CREATE TABLE IF NOT EXISTS problem_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Предустановленные типы проблем
INSERT INTO problem_types (name, slug, description, sort_order) VALUES
('Тревога и панические атаки', 'anxiety', 'Тревожные расстройства, панические атаки, фобии', 1),
('Депрессия', 'depression', 'Депрессивные состояния, апатия, потеря интереса к жизни', 2),
('Зависимости', 'addiction', 'Алкогольная, наркотическая, игровая и другие зависимости', 3),
('Отношения', 'relationships', 'Проблемы в отношениях, развод, одиночество', 4),
('Самооценка', 'self-esteem', 'Низкая самооценка, неуверенность в себе', 5),
('Травма и ПТСР', 'trauma', 'Посттравматическое стрессовое расстройство, психологические травмы', 6),
('Горе и утрата', 'grief', 'Переживание потери близких', 7),
('Стресс и выгорание', 'burnout', 'Профессиональное выгорание, хронический стресс', 8),
('Расстройства пищевого поведения', 'eating-disorders', 'Анорексия, булимия, компульсивное переедание', 9),
('Другое', 'other', 'Другие психологические проблемы', 100)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Кейсы (запросы клиентов на помощь)
CREATE TABLE IF NOT EXISTS cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    problem_type_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    is_anonymous BOOLEAN DEFAULT FALSE,
    status ENUM('OPEN', 'IN_PROGRESS', 'CLOSED', 'CANCELLED') DEFAULT 'OPEN',
    budget_type ENUM('PAID', 'REVIEW', 'NEGOTIABLE') DEFAULT 'NEGOTIABLE',
    budget_amount DECIMAL(10, 2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,

    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (problem_type_id) REFERENCES problem_types(id) ON DELETE RESTRICT,
    INDEX idx_client_id (client_id),
    INDEX idx_problem_type_id (problem_type_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Отклики психологов на кейсы
CREATE TABLE IF NOT EXISTS case_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    psychologist_id INT NOT NULL,
    message TEXT NOT NULL,
    proposed_price DECIMAL(10, 2) NULL,
    status ENUM('PENDING', 'ACCEPTED', 'REJECTED') DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL,

    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (psychologist_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_case_psychologist (case_id, psychologist_id),
    INDEX idx_case_id (case_id),
    INDEX idx_psychologist_id (psychologist_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
