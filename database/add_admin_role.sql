-- database/add_admin_role.sql
-- Add admin role to the users table and create default admin account
-- Run this in phpMyAdmin after the initial schema.sql

USE cshub;

-- Step 1: Modify the users table to include 'admin' role
ALTER TABLE users
MODIFY COLUMN role ENUM('client', 'freelancer', 'admin') NOT NULL;

-- Step 2: Create default admin account
-- Username: admin@cshub.com
-- Password: admin123 (you should change this after first login)
INSERT INTO users (name, email, password, phone, role)
VALUES (
    'Administrator',
    'admin@cshub.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
    '60123456789',
    'admin'
);

-- Note: The password hash above is for 'admin123'
-- To change the password later, use the following format:
-- UPDATE users SET password = PASSWORD_HASH_HERE WHERE email = 'admin@cshub.com';
