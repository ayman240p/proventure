-- database/schema.sql
-- Minimal starter schema for the cshub database (XAMPP / phpMyAdmin).
-- This is enough to make the scaffold pages run without errors.
-- A fully detailed, normalized (3NF) schema with indexes, constraints,
-- and seed data will be produced in the dedicated Database Design step
-- (Section 7-9 of the project spec).

CREATE DATABASE IF NOT EXISTS cshub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cshub;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    role ENUM('client', 'freelancer') NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT DEFAULT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    portfolio_image VARCHAR(255) DEFAULT NULL,
    availability TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    client_id INT NOT NULL,
    rating TINYINT NOT NULL,
    review_text TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    CHECK (rating BETWEEN 1 AND 5)
);

-- Starter categories (Section 7 example list)
INSERT INTO categories (name, description) VALUES
    ('Academic Tutoring', 'Peer tutoring for school/college subjects'),
    ('Graphic Design', 'Logo, poster, and digital design services'),
    ('Gadget Repair', 'Phone, laptop, and small electronics repair'),
    ('Delivery / Runner', 'Local dispatch and errand-running services'),
    ('Photography', 'Event and portrait photography'),
    ('Computer Services', 'Software setup, troubleshooting, installations'),
    ('Other', 'Miscellaneous local micro-services');
