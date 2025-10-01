-- CREATE DATABASE IF NOT EXISTS srms_db;
USE srms_db;

-- Admin table (use the hash you generated for 'admin123')
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$RpMD87E.dC90sPQGeKv.HeX3UeZjoPZog47CjPUR5okQOG7n/hgum');

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100) NOT NULL,
    course VARCHAR(100) NOT NULL,
    grades JSON NOT NULL,  -- e.g., {"Math":90,"Science":85}
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);