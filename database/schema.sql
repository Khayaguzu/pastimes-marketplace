-- Pastimes Marketplace database schema
-- No accounts, passwords, or personal information are included.

CREATE DATABASE IF NOT EXISTS clothingstore
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE clothingstore;

CREATE TABLE IF NOT EXISTS tblUser
(
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    status ENUM('pending', 'verified', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS tblAdmin
(
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100),
    role ENUM('super_admin', 'moderator') DEFAULT 'moderator',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tblClothes
(
    clothes_id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    brand VARCHAR(100),
    category VARCHAR(50) NOT NULL,
    gender ENUM('men', 'women', 'unisex') DEFAULT 'unisex',
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(500),
    stock INT DEFAULT 1,
    description TEXT,
    status ENUM('pending', 'approved', 'rejected', 'sold') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_clothes_seller FOREIGN KEY (seller_id)
        REFERENCES tblUser(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_clothes_price CHECK (price BETWEEN 50 AND 400),
    CONSTRAINT chk_clothes_stock CHECK (stock >= 0)
);

CREATE TABLE IF NOT EXISTS tblAorder
(
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    buyer_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_address TEXT NOT NULL,
    delivery_city VARCHAR(100),
    delivery_postal VARCHAR(20),
    payment_method ENUM('eft', 'card', 'cod', 'payflex') DEFAULT 'card',
    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    tracking_number VARCHAR(100),
    CONSTRAINT fk_order_buyer FOREIGN KEY (buyer_id)
        REFERENCES tblUser(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tblOrderItems
(
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    clothes_id INT NOT NULL,
    quantity INT DEFAULT 1,
    price_at_time DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_item_order FOREIGN KEY (order_id)
        REFERENCES tblAorder(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_item_clothes FOREIGN KEY (clothes_id)
        REFERENCES tblClothes(clothes_id) ON DELETE CASCADE,
    CONSTRAINT chk_item_quantity CHECK (quantity > 0)
);

CREATE TABLE IF NOT EXISTS tblMessages
(
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NULL,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_message_sender FOREIGN KEY (from_user_id)
        REFERENCES tblUser(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_message_recipient FOREIGN KEY (to_user_id)
        REFERENCES tblUser(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_message_order FOREIGN KEY (order_id)
        REFERENCES tblAorder(order_id) ON DELETE SET NULL
);

-- Create the first administrator through a local, access-controlled process.
-- Generate a password hash with:
-- php -r "echo password_hash('replace-with-a-strong-password', PASSWORD_DEFAULT);"
