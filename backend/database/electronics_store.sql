-- Electronics Store Database Schema
-- Created: February 2026
-- Database Structure with 3NF normalization
DROP DATABASE IF EXISTS electronics_store;
CREATE DATABASE IF NOT EXISTS electronics_store;
USE electronics_store;

-- Table 1: Users (Stores user information with roles)
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Table 2: Categories (Product categories)
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category_name (category_name)
);

-- Table 3: Products (Electronics inventory)
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255),
    sku VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE,
    INDEX idx_category_id (category_id),
    INDEX idx_product_name (product_name),
    INDEX idx_price (price),
    INDEX idx_sku (sku)
);

-- Table 8: Product Images (Supports multiple images per product)
CREATE TABLE product_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    image_name VARCHAR(150),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id)
);

-- Table 4: Orders (Customer orders)
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    delivery_address TEXT NOT NULL,
    payment_method VARCHAR(50),
    notes TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_order_date (order_date),
    INDEX idx_status (status)
);

-- Table 5: Order Items (Line items for each order)
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
);

-- Table 6: Shopping Cart (Temporary cart for users)
CREATE TABLE shopping_cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id)
);

-- Table 7: Reviews (Customer reviews for products)
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_user_id (user_id),
    INDEX idx_rating (rating)
);

-- Sample Data for Testing

-- Insert sample users
-- Default login credentials:
-- Admin: username=admin, password=admin123
-- Customer: username=customer, password=customer123
INSERT INTO users (username, email, password_hash, first_name, last_name, role, phone, city) VALUES
('admin', 'admin@electronics.com', '$2y$10$l86KIzqGDdkUvJ93n27/Eu7kc0SAodRuXHAtFpjBZQXz/.w5YG8v6', 'Admin', 'User', 'admin', '1234567890', 'New York'),
('customer', 'customer@example.com', '$2y$10$6vcSImLkXk7McoFs9dYlwej.FA5k9hcXoLZL2F6YIKHAL8IZ7k7hS', 'Customer', 'User', 'customer', '9876543210', 'Los Angeles'),
('testuser', 'testuser@example.com', '$2y$10$6vcSImLkXk7McoFs9dYlwej.FA5k9hcXoLZL2F6YIKHAL8IZ7k7hS', 'Test', 'User', 'customer', '5551234567', 'Chicago');

-- Insert sample categories
INSERT INTO categories (category_name, description) VALUES
('Laptops', 'High-performance laptops for work and gaming'),
('Smartphones', 'Latest smartphones with advanced features'),
('Tablets', 'Portable tablets for entertainment and productivity'),
('Headphones', 'Premium audio devices and headphones'),
('Cameras', 'Digital cameras and photography equipment'),
('Accessories', 'Phone and computer accessories');

-- Insert sample products (30+ products using all available images)
INSERT INTO products (category_id, product_name, description, price, stock_quantity, sku, image_url, is_active) VALUES
-- Laptops (5 products)
(1, 'Dell XPS 13 FHD', 'Compact and powerful laptop with Intel i7, 16GB RAM, 512GB SSD. Perfect for professionals on the go with stunning FHD display', 1299.99, 15, 'DELL-XPS13-001', '/assets/images/products/dell-xps13-1.jpg', TRUE),
(1, 'Dell XPS 13 4K', 'Premium variant with 4K OLED display, Intel i9, 32GB RAM, 1TB SSD. Ultimate clarity and performance', 1899.99, 8, 'DELL-XPS13-002', '/assets/images/products/dell-xps13-2.jpg', TRUE),
(1, 'Dell XPS 13 Plus', 'Next-gen design with Intel i5, 8GB RAM, 256GB SSD. Lightweight student and casual use option', 799.99, 20, 'DELL-XPS13-003', '/assets/images/products/dell-xps13-3.jpg', TRUE),
(1, 'Dell XPS 13 Business', 'Business edition with Intel i7, 16GB RAM, Windows Pro, security features', 1449.99, 12, 'DELL-XPS13-004', '/assets/images/products/dell-xps13-4.jpg', TRUE),
(1, 'Dell XPS 13 Creator', 'Optimized for content creators with RTX GPU, color-accurate display, 32GB RAM', 2099.99, 5, 'DELL-XPS13-005', '/assets/images/products/dell-xps13-3.jpg', TRUE),

-- MacBooks (3 products)
(1, 'MacBook Pro 14 M3', 'Pro-grade laptop with Apple M3 chip, 8-core GPU, 16GB RAM, 512GB SSD. Great for development', 1799.99, 12, 'APPLE-MBP14-001', '/assets/images/products/macbook-pro14-1.jpg', TRUE),
(1, 'MacBook Pro 14 M3 Max', 'Maximum performance with M3 Max chip, 36GB RAM, 1TB SSD, up to 30-hour battery life', 2499.99, 8, 'APPLE-MBP14-002', '/assets/images/products/macbook-pro14-2.jpg', TRUE),
(1, 'MacBook Pro 14 M3 Ultra', 'Ultimate powerhouse with M3 Ultra chip, 64GB RAM, 2TB SSD. For professional video and 3D work', 3299.99, 3, 'APPLE-MBP14-003', '/assets/images/products/macbook-pro14-1.jpg', TRUE),

-- iPhones (5 products)
(2, 'iPhone 15 Pro Max', 'Largest Pro model with 6.7" display, 1TB storage option, titanium design, advanced camera system', 1199.99, 18, 'APPLE-IP15P-001', '/assets/images/products/iphone15pro-1.jpg', TRUE),
(2, 'iPhone 15 Pro Standard', 'Latest Apple iPhone with A17 Pro chip, 128GB storage, titanium design, ProMotion display', 999.99, 25, 'APPLE-IP15P-002', '/assets/images/products/iphone15pro-2.jpg', TRUE),
(2, 'iPhone 15 Pro 256GB', '256GB storage variant with enhanced camera capabilities and video recording features', 1099.99, 20, 'APPLE-IP15P-003', '/assets/images/products/iphone15pro-3.jpg', TRUE),
(2, 'iPhone 15 Pro 512GB', '512GB storage for power users and content creators who need maximum space', 1299.99, 15, 'APPLE-IP15P-004', '/assets/images/products/iphone15pro-3.jpg', TRUE),
(2, 'iPhone 15 Pro Max 1TB', '1TB variant of the premium iPhone 15 Pro Max for professionals', 1399.99, 8, 'APPLE-IP15P-005', '/assets/images/products/iphone15pro-5.jpg', TRUE),

-- Samsung Galaxy S24 (4 products)
(2, 'Samsung Galaxy S24 FE', 'Affordable flagship killer with Snapdragon 8 Gen 3, 128GB storage, great camera', 699.99, 30, 'SAMSUNG-GS24-001', '/assets/images/products/galaxy-s24-1.jpg', TRUE),
(2, 'Samsung Galaxy S24+', 'Plus model with 6.7" display, 256GB storage, enhanced battery capacity, fast charging', 899.99, 25, 'SAMSUNG-GS24-002', '/assets/images/products/galaxy-s24-2.jpg', TRUE),
(2, 'Samsung Galaxy S24 Ultra', 'Premium ultra model with stylus, titanium frame, 512GB storage, professional camera suite', 1299.99, 12, 'SAMSUNG-GS24-003', '/assets/images/products/galaxy-s24-3.jpg', TRUE),
(2, 'Samsung Galaxy S24 Edge', 'Latest variant with edge-to-edge display, minimal bezels, exclusive color options', 999.99, 18, 'SAMSUNG-GS24-004', '/assets/images/products/galaxy-s24-4.jpg', TRUE),

-- iPad Models (3 products)
(3, 'iPad Pro 12.9 256GB', 'Apple iPad Pro with M2 chip, 256GB storage, Liquid Retina display, ideal for creative professionals', 1099.99, 12, 'APPLE-IPP129-001', '/assets/images/products/ipad-pro-1.jpg', TRUE),
(3, 'iPad Pro 12.9 512GB', '512GB high-capacity storage variant for power users and designers', 1299.99, 8, 'APPLE-IPP129-002', '/assets/images/products/ipad-pro-2.jpg', TRUE),
(3, 'iPad Pro 12.9 1TB', '1TB premium variant with maximum storage and performance for video editors and creators', 1599.99, 5, 'APPLE-IPP129-003', '/assets/images/products/ipad-pro-3.jpg', TRUE),

-- Headphones - Sony (3 products)
(4, 'Sony WH-1000XM5 Black', 'Premium noise-cancelling wireless headphones with 30-hour battery, industry-leading ANC technology', 399.99, 20, 'SONY-WH1000-001', '/assets/images/products/sony-wh1000xm5-1.jpg', TRUE),
(4, 'Sony WH-1000XM5 Silver', 'Same premium features in elegant silver finish with free carrying case', 399.99, 15, 'SONY-WH1000-002', '/assets/images/products/sony-wh1000xm5-2.jpg', TRUE),
(4, 'Sony WH-1000XM5 Blue', 'Premium variant in midnight blue with studio-quality sound and multipoint connection', 399.99, 12, 'SONY-WH1000-003', '/assets/images/products/sony-wh1000xm5-3.jpg', TRUE),

-- AirPods (3 products)
(4, 'Apple AirPods Pro Max', 'Premium over-ear wireless headphones with spatial audio and dynamic head tracking', 549.99, 10, 'APPLE-APP-MAX', '/assets/images/products/airpods-pro-1.jpg', TRUE),
(4, 'Apple AirPods Pro 2', 'Premium wireless earbuds with active noise cancellation and transparency mode', 249.99, 40, 'APPLE-APP-001', '/assets/images/products/airpods-pro-2.jpg', TRUE),
(4, 'Apple AirPods Air', 'Versatile wireless earbuds with adaptive audio for any environment', 179.99, 35, 'APPLE-APP-AIR', '/assets/images/products/airpods-pro-3.jpg', TRUE),

-- Cameras (3 products)
(5, 'Canon EOS R6 Body Only', 'Professional mirrorless camera with 20MP sensor and 4K video, body only', 2499.99, 8, 'CANON-R6-001', '/assets/images/products/canon-r6-1.jpg', TRUE),
(5, 'Canon EOS R6 with 24-70mm', 'Canon EOS R6 professional package with premium zoom lens included', 3299.99, 5, 'CANON-R6-002', '/assets/images/products/canon-r6-2.jpg', TRUE),
(5, 'Canon EOS R6 Mark II', 'Latest R6 variant with improved autofocus, higher fps shooting, 8K capabilities', 3999.99, 3, 'CANON-R6-003', '/assets/images/products/canon-r6-3.jpg', TRUE),

-- Accessories - USB Cables (2 products)
(6, 'USB-C Cable 2m Nylon', 'High-quality nylon-braided USB-C charging and data cable, 100W power delivery', 19.99, 100, 'GENERIC-USBC-001', '/assets/images/products/usbc-cable-1.jpg', TRUE),
(6, 'USB-C Cable 3m Kevlar', 'Extra-long 3m USB-C cable with Kevlar reinforcement for maximum durability', 24.99, 80, 'GENERIC-USBC-002', '/assets/images/products/usbc-cable-2.jpg', TRUE),

-- Accessories - Screen Protectors (2 products)
(6, 'Tempered Glass Protector 9H', 'Premium 9H hardness tempered glass screen protector for all smartphones', 9.99, 150, 'GENERIC-PROT-001', '/assets/images/products/protector-1.jpg', TRUE),
(6, 'Privacy Screen Protector', 'Anti-spy tempered glass protector with privacy filter, 9H hardness', 14.99, 120, 'GENERIC-PROT-002', '/assets/images/products/protector-2.jpg', TRUE);

-- Insert product images (using all 33 available images)
INSERT INTO product_images (product_id, image_url, image_name, sort_order) VALUES
-- Dell XPS 13 variants
(1, '/assets/images/products/dell-xps13-1.jpg', 'Dell XPS 13 FHD - Front View', 1),
(2, '/assets/images/products/dell-xps13-2.jpg', 'Dell XPS 13 4K - Open Lid', 1),
(3, '/assets/images/products/dell-xps13-3.jpg', 'Dell XPS 13 Plus - Keyboard Detail', 1),
(4, '/assets/images/products/dell-xps13-4.jpg', 'Dell XPS 13 Business - Ports', 1),
(5, '/assets/images/products/dell-xps13-3.jpg', 'Dell XPS 13 Creator - Right Side', 1),

-- MacBook Pro variants
(6, '/assets/images/products/macbook-pro14-1.jpg', 'MacBook Pro 14 M3 - Top View', 1),
(7, '/assets/images/products/macbook-pro14-2.jpg', 'MacBook Pro 14 M3 Max - Keyboard', 1),
(8, '/assets/images/products/macbook-pro14-1.jpg', 'MacBook Pro 14 M3 Ultra - Display', 1),

-- iPhone 15 Pro variants
(9, '/assets/images/products/iphone15pro-1.jpg', 'iPhone 15 Pro Max - Front', 1),
(10, '/assets/images/products/iphone15pro-2.jpg', 'iPhone 15 Pro Standard - Back', 1),
(11, '/assets/images/products/iphone15pro-3.jpg', 'iPhone 15 Pro 256GB - Right Side', 1),
(12, '/assets/images/products/iphone15pro-3.jpg', 'iPhone 15 Pro 512GB - Display', 1),
(13, '/assets/images/products/iphone15pro-5.jpg', 'iPhone 15 Pro Max 1TB - Colors', 1),

-- Samsung Galaxy S24 variants
(14, '/assets/images/products/galaxy-s24-1.jpg', 'Galaxy S24 FE - Front', 1),
(15, '/assets/images/products/galaxy-s24-2.jpg', 'Galaxy S24+ - Back Cameras', 1),
(16, '/assets/images/products/galaxy-s24-3.jpg', 'Galaxy S24 Ultra - Retail Box', 1),
(17, '/assets/images/products/galaxy-s24-4.jpg', 'Galaxy S24 Edge - Color Options', 1),

-- iPad Pro variants
(18, '/assets/images/products/ipad-pro-1.jpg', 'iPad Pro 12.9 256GB - Front', 1),
(19, '/assets/images/products/ipad-pro-2.jpg', 'iPad Pro 12.9 512GB - Back', 1),
(20, '/assets/images/products/ipad-pro-3.jpg', 'iPad Pro 12.9 1TB - With Case', 1),

-- Sony WH-1000XM5 variants
(21, '/assets/images/products/sony-wh1000xm5-1.jpg', 'Sony WH-1000XM5 Black - Folded', 1),
(22, '/assets/images/products/sony-wh1000xm5-2.jpg', 'Sony WH-1000XM5 Silver - Headband', 1),
(23, '/assets/images/products/sony-wh1000xm5-3.jpg', 'Sony WH-1000XM5 Blue - With Case', 1),

-- AirPods variants
(24, '/assets/images/products/airpods-pro-1.jpg', 'Apple AirPods Pro Max - In Box', 1),
(25, '/assets/images/products/airpods-pro-2.jpg', 'Apple AirPods Pro 2 - In Ear', 1),
(26, '/assets/images/products/airpods-pro-3.jpg', 'Apple AirPods Air - Accessories', 1),

-- Canon EOS R6 variants
(27, '/assets/images/products/canon-r6-1.jpg', 'Canon EOS R6 Body - Top View', 1),
(28, '/assets/images/products/canon-r6-2.jpg', 'Canon EOS R6 with Lens - Grip', 1),
(29, '/assets/images/products/canon-r6-3.jpg', 'Canon EOS R6 Mark II - Detailed', 1),

-- USB-C Cables
(30, '/assets/images/products/usbc-cable-1.jpg', 'USB-C Nylon Cable - Coiled', 1),
(31, '/assets/images/products/usbc-cable-2.jpg', 'USB-C Kevlar Cable - Connector', 1),

-- Screen Protectors
(32, '/assets/images/products/protector-1.jpg', 'Tempered Glass - Pack Box', 1),
(33, '/assets/images/products/protector-2.jpg', 'Privacy Protector - Close Up', 1);

-- Insert sample orders
INSERT INTO orders (user_id, total_amount, status, delivery_address, payment_method) VALUES
(2, 1319.98, 'delivered', '123 Main St, Los Angeles, CA 90001', 'Credit Card'),
(3, 649.98, 'shipped', '456 Oak Ave, Chicago, IL 60601', 'PayPal');

-- Insert sample order items
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES
(1, 6, 1, 399.99, 399.99),
(1, 9, 1, 19.99, 19.99),
(2, 7, 2, 249.99, 499.98),
(2, 10, 1, 9.99, 9.99);

-- Create indexes for better performance
CREATE INDEX idx_cart_user_product ON shopping_cart(user_id, product_id);
CREATE INDEX idx_orders_user_status ON orders(user_id, status);

-- Verify database creation
SELECT 'Database created successfully!' as status;
