-- Blood Donor Finder System
-- Database Structure

CREATE DATABASE IF NOT EXISTS blood_donor_system;
USE blood_donor_system;

CREATE TABLE donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    blood_group ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
    city VARCHAR(50) NOT NULL,
    last_donation_date DATE NULL,
    photo VARCHAR(255) DEFAULT NULL,
    is_available ENUM('Yes','No') DEFAULT 'Yes',
    role ENUM('donor','admin') DEFAULT 'donor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO donors (full_name, email, password, phone, blood_group, city, is_available, role)
VALUES ('Admin', 'admin@blooddonor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9999999999', 'O+', 'Ahmedabad', 'No', 'admin');

INSERT INTO donors (full_name, email, password, phone, blood_group, city, is_available, role) VALUES
('Rahul Sharma', 'rahul@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9812345670', 'B+', 'Ahmedabad', 'Yes', 'donor'),
('Priya Patel', 'priya@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9812345671', 'O-', 'Surat', 'Yes', 'donor'),
('Amit Verma', 'amit@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9812345672', 'A+', 'Ahmedabad', 'No', 'donor');