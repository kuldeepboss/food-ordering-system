<?php
// Database configuration
$host = 'localhost';
$dbname = 'food_ordering_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If database doesn't exist, create it
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
        $pdo->exec("USE $dbname");
        
        // Create tables
        createTables($pdo);
        
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

function createTables($pdo) {
    // Create categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create menu_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )");
    
    // Create orders table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
		customer_name VARCHAR(255) NOT NULL,
        customer_email VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        delivery_address TEXT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        order_status ENUM('pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create order_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        menu_item_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
    )");
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Create contact_messages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert default categories if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    if ($stmt->fetchColumn() == 0) {
        $defaultCategories = [
            ['name' => 'Starters', 'description' => 'Appetizers and small plates'],
            ['name' => 'Main Courses', 'description' => 'Primary dishes and entrees'],
            ['name' => 'Salads', 'description' => 'Fresh salads and healthy options'],
            ['name' => 'Desserts', 'description' => 'Sweet treats and desserts'],
            ['name' => 'Beverages', 'description' => 'Drinks and refreshments'],
            ['name' => 'Vegan Specials', 'description' => 'Plant-based and vegan options'],
            ['name' => 'Sandwiches', 'description' => 'Sandwiches and wraps']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        foreach ($defaultCategories as $category) {
            $stmt->execute([$category['name'], $category['description']]);
        }
    }
    
    // Insert default menu items if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM menu_items");
    if ($stmt->fetchColumn() == 0) {
        $defaultItems = [
            ['category_id' => 1, 'name' => 'Vegetable Spring Rolls', 'description' => 'Crispy rolls with cabbage, carrots, and mushrooms', 'price' => 70, 'image' => 'spring_rolls.jpg'],
            ['category_id' => 1, 'name' => 'Bruschetta', 'description' => 'Toasted bread with tomatoes, basil, and garlic', 'price' => 100, 'image' => 'bruschetta.jpg'],
            ['category_id' => 1, 'name' => 'Paneer Tikka', 'description' => 'Grilled Indian cottage cheese with spices', 'price' => 150, 'image' => 'paneer_tikka.jpg'],
            ['category_id' => 2, 'name' => 'Vegetable Lasagna', 'description' => 'Layers of pasta, spinach, zucchini, and cheese', 'price' => 50, 'image' => 'veg_lasagna.jpg'],
            ['category_id' => 2, 'name' => 'Mushroom Risotto', 'description' => 'Creamy Arborio rice with wild mushrooms', 'price' => 90, 'image' => 'risotto.jpg'],
            ['category_id' => 2, 'name' => 'Chana Masala', 'description' => 'Spiced chickpea curry with basmati rice', 'price' => 120, 'image' => 'chana_masala.jpg'],
            ['category_id' => 3, 'name' => 'Greek Salad', 'description' => 'Cucumbers, tomatoes, olives, and feta cheese', 'price' => 77, 'image' => 'greek_salad.jpg'],
            ['category_id' => 3, 'name' => 'Quinoa Bowl', 'description' => 'Quinoa with roasted veggies and tahini dressing', 'price' => 140, 'image' => 'quinoa_bowl.jpg'],
            ['category_id' => 3, 'name' => 'Caprese Salad', 'description' => 'Fresh mozzarella, tomatoes, and basil', 'price' => 110, 'image' => 'caprese.jpg'],
            ['category_id' => 4, 'name' => 'Gulab Jamun', 'description' => 'Soft milk dumplings in rose syrup', 'price' => 200, 'image' => 'gulab_jamun.jpg'],
            ['category_id' => 4, 'name' => 'Chocolate Lava Cake', 'description' => 'Warm chocolate cake with molten center', 'price' => 500, 'image' => 'lava_cake.jpg'],
            ['category_id' => 4, 'name' => 'Mango Sorbet', 'description' => 'Dairy-free mango frozen dessert', 'price' => 96, 'image' => 'mango_sorbet.jpg'],
            ['category_id' => 5, 'name' => 'Mango Lassi', 'description' => 'Yogurt-based mango smoothie', 'price' => 60, 'image' => 'lassi.jpg'],
            ['category_id' => 5, 'name' => 'Iced Matcha Latte', 'description' => 'Green tea powder with milk and ice', 'price' => 88, 'image' => 'matcha_latte.jpg'],
            ['category_id' => 5, 'name' => 'Fresh Coconut Water', 'description' => 'Served straight from the coconut', 'price' => 99, 'image' => 'coconut.jpg'],
            ['category_id' => 6, 'name' => 'Vegan Buddha Bowl', 'description' => 'Grain bowl with roasted veggies and tahini', 'price' => 130, 'image' => 'buddha_bowl.jpg'],
            ['category_id' => 6, 'name' => 'Tofu Stir Fry', 'description' => 'Crispy tofu with mixed vegetables', 'price' => 78, 'image' => 'tofu_stirfry.jpg'],
            ['category_id' => 6, 'name' => 'Vegan Chocolate Mousse', 'description' => 'Dairy-free avocado chocolate mousse', 'price' => 45, 'image' => 'vegan_mousse.jpg'],
            ['category_id' => 7, 'name' => 'Grilled Cheese', 'description' => 'Melted cheddar between sourdough bread', 'price' => 160, 'image' => 'grilled_cheese.jpg'],
            ['category_id' => 7, 'name' => 'Veggie Panini', 'description' => 'Grilled vegetables with pesto', 'price' => 92, 'image' => 'veggie_panini.jpg'],
            ['category_id' => 7, 'name' => 'Falafel Wrap', 'description' => 'Crispy falafel with tahini sauce', 'price' => 109, 'image' => 'falafel_wrap.jpg']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO menu_items (category_id, name, description, price, image) VALUES (?, ?, ?, ?, ?)");
        foreach ($defaultItems as $item) {
            $stmt->execute([$item['category_id'], $item['name'], $item['description'], $item['price'], $item['image']]);
        }
    }
}
?> 