CREATE DATABASE IF NOT EXISTS cryptic_db;
USE cryptic_db;

CREATE TABLE IF NOT EXISTS cipher_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cipher_type VARCHAR(50) NOT NULL,
    action_mode VARCHAR(10) NOT NULL,
    input_text TEXT NOT NULL,
    output_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);