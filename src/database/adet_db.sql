CREATE DATABASE IF NOT EXISTS adet_db;
USE adet_db;

CREATE TABLE IF NOT EXISTS address (
    id INT AUTO_INCREMENT PRIMARY KEY,
    region VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    barangay VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150),
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    contact_number VARCHAR(50),
    address_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (address_id) REFERENCES address(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS customer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    contact_number VARCHAR(50),
    address_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (address_id) REFERENCES address(id) ON DELETE SET NULL
);

-- Insert a default address
INSERT IGNORE INTO address (id, region, province, city, barangay) VALUES 
(1, 'NCR', 'Metro Manila', 'Manila', 'Ermita');

-- Insert a default admin tied to that address
INSERT IGNORE INTO admin (id, full_name, email, password, contact_number, address_id) VALUES 
(1, 'System Administrator', 'admin@souveniria.ph', '$2y$10$w3v6nCnj4.wXYM6M5xVqeeXn6q21Yg.Jg5CebzZ/2uF.zNf2qQh3.', '09000000000', 1);

-- Sample Customers & Addresses
INSERT IGNORE INTO address (id, region, province, city, barangay) VALUES 
(2, 'Region V', 'Camarines Sur', 'Naga City', 'Penafrancia'),
(3, 'NCR', 'Metro Manila', 'Quezon City', 'Diliman');

INSERT IGNORE INTO customer (id, full_name, email, password, contact_number, address_id) VALUES 
(1, 'Arvie Rivera', 'ar@gmail.com', '$2y$10$Z3x/6R.w/.FfUe5e1mXpTOW81bM3x8L8lBv/Mh8HkYjXtjM9R7R4q', '091224444444', 2),
(2, 'Juan Dela Cruz', 'juan@example.com', '$2y$10$Z3x/6R.w/.FfUe5e1mXpTOW81bM3x8L8lBv/Mh8HkYjXtjM9R7R4q', '09171234567', 3);
