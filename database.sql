-- create database (run in MySQL)
CREATE DATABASE clothing_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clothing_store;

-- products table
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  competitor_price DECIMAL(10,2) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- admin users
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- sample admin (password: admin123)
INSERT INTO admins (username, password_hash) VALUES
('Voidxx', '$2y$10$EY8xB9Aqv3Bym.N40BpHPOD/GL47F52JCaZYcxExYAdJMHt8kW2Fq'); 
-- This hash corresponds to 'admin123' hashed with password_hash()
WHERE id=1;
-- sample products
INSERT INTO products (name, description, price, competitor_price, image) VALUES
('Blue Denim Jacket', 'Stylish blue denim jacket, slim fit.', 2999.00, 3299.00, 'denim_jacket.jpg'),
('Red Casual T-Shirt', 'Soft cotton t-shirt with a round neck.', 699.00, 799.00, 'red_tshirt.jpg'),
('Black Chinos', 'Comfortable chinos for daily wear.', 1499.00, 1699.00, 'black_chinos.jpg');