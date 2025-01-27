CREATE DATABASE IF NOT EXISTS lost_and_found;
USE lost_and_found;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL, -- Store hashed passwords
    role ENUM('user', 'admin') DEFAULT 'user', -- 'user' or 'admin'
    full_name VARCHAR(100),
    phone_number VARCHAR(15),
    is_active TINYINT(1) DEFAULT 1, -- 1 = Active, 0 = Deactivated
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type ENUM('lost', 'found', 'pending') DEFAULT 'lost',
    item_name VARCHAR(100) NOT NULL,
    item_description TEXT NOT NULL,
    location VARCHAR(100) NOT NULL,
    contact_info VARCHAR(100) NOT NULL,
    image_path VARCHAR(255),
    status ENUM('lost', 'found', 'pending') DEFAULT 'lost',
    date_reported TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
   
);

CREATE TABLE IF NOT EXISTS matched_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lost_item_id INT NOT NULL,
    found_item_id INT NOT NULL,
    admin_id INT NOT NULL,
    resolution_details TEXT,
    match_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    match_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lost_item_id) REFERENCES items(id),
    FOREIGN KEY (found_item_id) REFERENCES items(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);


-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- User receiving the notification
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0, -- 0 = Unread, 1 = Read
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
