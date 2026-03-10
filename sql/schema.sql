CREATE DATABASE IF NOT EXISTS ticket_api;
USE ticket_api;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS users;

-- USERS TABLE
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('admin','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- EVENTS TABLE
CREATE TABLE events (
    id VARCHAR(20) PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(150),
    venue VARCHAR(200),
    price DECIMAL(10,2),
    category VARCHAR(100),
    available_tickets INT,
    sold_tickets INT
);

-- TICKETS TABLE
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id VARCHAR(20) NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- AUDIT LOGS
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(20) NOT NULL,
    entity VARCHAR(50) NOT NULL,
    entity_id VARCHAR(50) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
);