-- database/add_auto_approval_and_reports.sql
-- Migration to support configurable auto-approval and community reporting for live listings

USE cshub;

-- 1. System Settings Table
CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default auto-approval to enabled ('1')
INSERT INTO system_settings (setting_key, setting_value)
VALUES ('auto_approve_services', '1')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- 2. Service Reports Table (for spam / malicious listings)
CREATE TABLE IF NOT EXISTS service_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    reporter_id INT DEFAULT NULL COMMENT 'NULL if submitted by guest',
    reporter_name VARCHAR(100) DEFAULT NULL,
    reporter_email VARCHAR(150) DEFAULT NULL,
    reason ENUM('spam', 'malicious', 'misleading', 'inappropriate', 'other') NOT NULL DEFAULT 'spam',
    details TEXT DEFAULT NULL,
    status ENUM('pending', 'reviewed', 'dismissed', 'action_taken') NOT NULL DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_service_id (service_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
