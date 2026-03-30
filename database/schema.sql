CREATE DATABASE IF NOT EXISTS retail_system;
USE retail_system;

DROP TABLE IF EXISTS support_ticket_replies;
DROP TABLE IF EXISTS support_tickets;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS user_activity_logs;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    contact_no VARCHAR(30),
    address TEXT,
    payment_preference VARCHAR(100),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(180) NOT NULL,
    brand VARCHAR(120),
    sku VARCHAR(80) UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_qty INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT,
    payment_method VARCHAR(80),
    payment_gateway VARCHAR(50) NOT NULL DEFAULT 'cod',
    payment_status VARCHAR(40) NOT NULL DEFAULT 'Pending',
    payment_transaction_id VARCHAR(120) NULL DEFAULT NULL,
    order_status ENUM('Pending', 'Processing', 'Shipped', 'Delivered') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_product (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating TINYINT NOT NULL,
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (rating BETWEEN 1 AND 5),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist_item (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    ticket_type ENUM('Inquiry', 'Complaint', 'Return') DEFAULT 'Inquiry',
    description TEXT NOT NULL,
    status ENUM('Open', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE support_ticket_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    responder_id INT NOT NULL,
    reply_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (responder_id) REFERENCES users(id)
);

CREATE TABLE user_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity_type VARCHAR(100) NOT NULL,
    activity_detail TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO roles (role_name) VALUES ('Admin'), ('Customer'), ('Support');

INSERT INTO users (role_id, full_name, email, password_hash, contact_no, address, payment_preference) VALUES
(1, 'System Admin', 'admin@retail.local', '$2y$10$u3bAwfgaA8xA0bm3elIMzO1wImN9MSYhJR1fugM5Y1wI87D8YUNmG', '0771234567', 'Colombo', 'Mock Card'),
(3, 'Support Agent', 'support@retail.local', '$2y$10$u3bAwfgaA8xA0bm3elIMzO1wImN9MSYhJR1fugM5Y1wI87D8YUNmG', '0772345678', 'Kandy', 'Mock Wallet'),
(2, 'John Customer', 'john@retail.local', '$2y$10$u3bAwfgaA8xA0bm3elIMzO1wImN9MSYhJR1fugM5Y1wI87D8YUNmG', '0773456789', 'Galle', 'Cash on Delivery');
-- password for sample users: Password@123

INSERT INTO categories (name, description) VALUES
('Electronics', 'Phones, laptops, and gadgets'),
('Fashion', 'Clothing and accessories'),
('Home', 'Household essentials');

INSERT INTO products (category_id, name, brand, sku, description, price, stock_qty, image_url) VALUES
(1, 'Smartphone X', 'NovaTech', 'NT-SMX-001', '6.7 inch display, 128GB storage', 499.99, 25, 'smartphone-x.svg'),
(1, 'Laptop Pro 14', 'NovaTech', 'NT-LTP-014', '14 inch business laptop', 899.99, 14, 'laptop-pro-14.svg'),
(2, 'Running Shoes', 'Stride', 'SD-RUN-220', 'Lightweight and comfortable', 79.99, 40, 'running-shoes.svg'),
(3, 'Blender Max', 'HomeMix', 'HM-BLD-300', '3-speed kitchen blender', 59.99, 18, 'blender-max.svg');

INSERT INTO reviews (user_id, product_id, rating, review_text) VALUES
(3, 1, 5, 'Excellent phone for the price.'),
(3, 3, 4, 'Very comfortable for daily use.');

INSERT INTO wishlist (user_id, product_id) VALUES
(3, 2), (3, 4);

INSERT INTO support_tickets (user_id, subject, ticket_type, description, status) VALUES
(3, 'Late delivery inquiry', 'Inquiry', 'My order has not arrived yet.', 'Open');

INSERT INTO user_activity_logs (user_id, activity_type, activity_detail) VALUES
(1, 'LOGIN', 'Admin logged in successfully'),
(3, 'VIEW_PRODUCT', 'Viewed Smartphone X'),
(3, 'ADD_TO_CART', 'Added Running Shoes to cart');

CREATE VIEW sales_summary AS
SELECT DATE(created_at) AS order_date,
       COUNT(*) AS total_orders,
       SUM(total_amount) AS revenue
FROM orders
GROUP BY DATE(created_at)
ORDER BY order_date DESC;

CREATE VIEW top_selling_products AS
SELECT p.id, p.name, SUM(oi.quantity) AS units_sold, SUM(oi.subtotal) AS sales_amount
FROM order_items oi
JOIN products p ON p.id = oi.product_id
GROUP BY p.id, p.name
ORDER BY units_sold DESC;
