-- FoodHub Online Food Ordering System Database
-- Created for PHP-based food ordering website

-- Create database
CREATE DATABASE IF NOT EXISTS food_ordering_db;
USE food_ordering_db;

-- Create users table for customer authentication
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create categories table
DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create menu_items table
DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Create orders table
DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    delivery_address TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    order_status ENUM('pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create order_items table
DROP TABLE IF EXISTS `order_items`;
    CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

-- Create contact_messages table
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample users (password is 'password123' hashed with password_hash)
INSERT INTO users (email, password, phone, address) VALUES
('john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1 (555) 123-4567', '123 Main St, New York, NY 10001'),
('jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1 (555) 987-6543', '456 Oak Ave, Los Angeles, CA 90210'),
('mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1 (555) 456-7890', '789 Pine Rd, Chicago, IL 60601');

-- Insert categories with description and created_at
INSERT INTO categories (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Starter', 'Soups, appetizers, and light dishes to begin your meal.', NOW()),
(2, 'Paneer Special Dish', 'Rich and flavorful Indian paneer-based curries.', NOW()),
(3, 'Kaju Special Dish', 'Delicious cashew-based curries and gravies.', NOW());

-- Starters (category_id = 1)
INSERT INTO `menu_items`(`id`, `category_id`, `name`, `description`, `price`, `image`, `created_at`) VALUES
(NULL, 1, 'Veg. Tomato Soup', 'Fresh tomato soup seasoned with herbs and spices.', 90, 'images/veg_tomato_soup.jpg', NOW()),
(NULL, 1, 'Veg. Manchow Soup', 'Hot and sour soup with vegetables and crispy noodles.', 110, 'images/veg_manchow_soup.jpg', NOW()),
(NULL, 1, 'Veg. Manchurian', 'Crispy vegetable balls tossed in tangy Manchurian sauce.', 150, 'images/veg_manchurian.jpg', NOW()),
(NULL, 1, 'Veg. Spring Rolls', 'Crispy rolls stuffed with mixed vegetables.', 150, 'images/veg_spring_rolls.jpg', NOW()),
(NULL, 1, 'Veg. Manchurian Rice', 'Fried rice served with Manchurian gravy.', 150, 'images/veg_manchurian_rice.jpg', NOW());

-- Paneer Special Dish (category_id = 2)
INSERT INTO `menu_items`(`id`, `category_id`, `name`, `description`, `price`, `image`, `created_at`) VALUES
(NULL, 2, 'Paneer Tufani', 'Spicy and creamy paneer curry with special spices.', 220, 'images/paneer_tufani.jpg', NOW()),
(NULL, 2, 'Paneer Rajewadi', 'Rich paneer curry with royal Indian flavors.', 225, 'images/paneer_rajewadi.jpg', NOW()),
(NULL, 2, 'Paneer Pasanda', 'Paneer stuffed with dry fruits in creamy gravy.', 220, 'images/paneer_pasanda.jpg', NOW()),
(NULL, 2, 'Paneer Handi', 'Paneer cooked with vegetables and spices in handi style.', 230, 'images/paneer_handi.jpg', NOW()),
(NULL, 2, 'Paneer Bhurji', 'Scrambled paneer with onions, tomatoes, and spices.', 225, 'images/paneer_bhurji.jpg', NOW()),
(NULL, 2, 'Paneer Masala', 'Paneer cooked in thick spicy onion-tomato gravy.', 230, 'images/paneer_masala.jpg', NOW()),
(NULL, 2, 'Paneer Kofta', 'Paneer dumplings in rich and creamy sauce.', 245, 'images/paneer_kofta.jpg', NOW()),
(NULL, 2, 'Paneer Butter Masala', 'Paneer in creamy tomato gravy with butter.', 235, 'images/paneer_butter_masala.jpg', NOW());

-- Kaju Special Dish (category_id = 3)
INSERT INTO `menu_items`(`id`, `category_id`, `name`, `description`, `price`, `image`, `created_at`) VALUES
(NULL, 3, 'Kaju Kari', 'Cashew-based curry cooked with spices and herbs.', 220, 'images/kaju_kari.jpg', NOW()),
(NULL, 3, 'Kaju Masala', 'Rich cashew gravy with aromatic spices.', 230, 'images/kaju_masala.jpg', NOW()),
(NULL, 3, 'Kaju Paneer', 'Combination of paneer and cashew in rich gravy.', 235, 'images/kaju_paneer.jpg', NOW()),
(NULL, 3, 'Kaju Angara', 'Spicy and smoky cashew curry.', 240, 'images/kaju_angara.jpg', NOW()),
(NULL, 3, 'Kaju Pasanda', 'Cashew curry with creamy and nutty flavor.', 225, 'images/kaju_pasanda.jpg', NOW());


-- Insert sample orders (for demonstration)
INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, delivery_address, total_amount, payment_method, order_status) VALUES
(1, 'John Doe', 'john@example.com', '+1 (555) 123-4567', '123 Main St, New York, NY 10001', 25.98, 'cash', 'delivered'),
(2, 'Jane Smith', 'jane@example.com', '+1 (555) 987-6543', '456 Oak Ave, Los Angeles, CA 90210', 32.97, 'card', 'out_for_delivery'),
(3, 'Mike Johnson', 'mike@example.com', '+1 (555) 456-7890', '789 Pine Rd, Chicago, IL 60601', 18.99, 'paypal', 'preparing');

-- Insert sample order items
INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES
(1, 1, 2, 70.00), -- 2 Vegetable Spring Rolls
(2, 4, 1, 50.00),  -- 1 Vegetable Lasagna
(2, 10, 1, 200.00),  -- 1 Gulab Jamun
(2, 11, 2, 500.00),  -- 2 Chocolate Lava Cake
(3, 7, 1, 77.00), -- 1 Greek Salad
(3, 10, 1, 200.00);  -- 1 Gulab Jamun

-- Insert sample contact messages
INSERT INTO contact_messages (name, email, message) VALUES
('Sarah Wilson', 'sarah@example.com', 'Great food and fast delivery! Will order again.'),
('David Brown', 'david@example.com', 'The pizza was amazing! Best in town.'),
('Lisa Davis', 'lisa@example.com', 'Quick delivery and food was still hot. Highly recommend!');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_menu_category ON menu_items(category_id);
CREATE INDEX idx_orders_status ON orders(order_status);
CREATE INDEX idx_orders_date ON orders(order_date);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_contact_messages_date ON contact_messages(created_at);

-- Create view for order summary
CREATE VIEW order_summary AS
SELECT 
    o.id as order_id,
    o.user_id,
    o.customer_name,
    o.customer_email,
    o.customer_phone,
    o.delivery_address,
    o.total_amount,
    o.payment_method,
    o.order_status,
    o.order_date,
    COUNT(oi.id) as total_items,
    SUM(oi.quantity) as total_quantity
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id;

-- Create view for popular menu items
CREATE VIEW popular_items AS
SELECT 
    mi.id,
    mi.name,
    c.name as category_name,
    mi.price,
    COUNT(oi.id) as order_count,
    SUM(oi.quantity) as total_quantity
FROM menu_items mi
LEFT JOIN categories c ON mi.category_id = c.id
LEFT JOIN order_items oi ON mi.id = oi.menu_item_id
GROUP BY mi.id
ORDER BY total_quantity DESC;

-- Grant permissions (adjust as needed for your setup)
-- GRANT ALL PRIVILEGES ON food_ordering_db.* TO 'your_username'@'localhost';
-- FLUSH PRIVILEGES;

-- Show table structure
DESCRIBE users;
DESCRIBE categories;
DESCRIBE menu_items;
DESCRIBE orders;
DESCRIBE order_items;
DESCRIBE contact_messages;

-- Show sample data
SELECT 'Users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'Categories', COUNT(*) FROM categories
UNION ALL
SELECT 'Menu Items', COUNT(*) FROM menu_items
UNION ALL
SELECT 'Orders', COUNT(*) FROM orders
UNION ALL
SELECT 'Order Items', COUNT(*) FROM order_items
UNION ALL
SELECT 'Contact Messages', COUNT(*) FROM contact_messages; 
DESCRIBE menu_items;
DESCRIBE orders;
DESCRIBE order_items;
DESCRIBE contact_messages;

-- Show sample data
SELECT 'Categories' as table_name, COUNT(*) as count FROM categories
UNION ALL
SELECT 'Menu Items', COUNT(*) FROM menu_items
UNION ALL
SELECT 'Orders', COUNT(*) FROM orders
UNION ALL
SELECT 'Order Items', COUNT(*) FROM order_items
UNION ALL
SELECT 'Contact Messages', COUNT(*) FROM contact_messages; 