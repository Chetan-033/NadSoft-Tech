-- Business Listing & Rating System
-- Database Schema

CREATE DATABASE IF NOT EXISTS nadsoft_business;
USE nadsoft_business;

CREATE TABLE IF NOT EXISTS businesses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    rating DECIMAL(2,1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    INDEX idx_business_email (business_id, email),
    INDEX idx_business_phone (business_id, phone)
);

-- Sample data (optional)
INSERT INTO businesses (name, address, phone, email) VALUES
('Tech Solutions Inc', '123 Main St, City', '555-0100', 'info@techsolutions.com'),
('Coffee House Cafe', '456 Oak Ave, Town', '555-0200', 'hello@coffeehouse.com');
