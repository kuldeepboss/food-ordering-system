<?php
session_start();
require_once '../config/database.php';

// Simple admin authentication
$admin_username = 'admin';
$admin_password = 'admin123';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

// Get database statistics
$stats = [];
try {
    $tables = ['users', 'categories', 'menu_items', 'orders', 'order_items', 'contact_messages'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $stats[$table] = $stmt->fetchColumn();
    }
} catch (PDOException $e) {
    $stats = array_fill_keys($tables, 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Viewer - FoodHub Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 1rem;
        }
        .sidebar h2 {
            margin-bottom: 2rem;
            text-align: center;
            border-bottom: 1px solid #34495e;
            padding-bottom: 1rem;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar li {
            margin-bottom: 0.5rem;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 0.75rem 1rem;
            display: block;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #34495e;
        }
        .main-content {
            flex: 1;
            padding: 2rem;
            background: #f8f9fa;
            overflow-y: auto;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card i {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            color: #7f8c8d;
            margin-top: 0.5rem;
        }
        .section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .section h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #667eea;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #e9ecef;
        }
        .status-delivered { color: #28a745; font-weight: bold; }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-cancelled { color: #dc3545; font-weight: bold; }
        .status-preparing { color: #17a2b8; font-weight: bold; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #17a2b8; }
        .connection-status {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2><i class="fas fa-utensils"></i> FoodHub Admin</h2>
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="menu.php"><i class="fas fa-list"></i> Menu Management</a></li>
                <li><a href="database.php" class="active"><i class="fas fa-database"></i> Database Viewer</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1><i class="fas fa-database"></i> Database Viewer</h1>
            
            <?php
            try {
                echo "<div class='connection-status'>";
                echo "<i class='fas fa-check-circle'></i> Database connection successful!";
                echo "</div>";
                
                // Database Statistics
                echo "<div class='stats-grid'>";
                $tableIcons = [
                    'users' => 'fas fa-users',
                    'categories' => 'fas fa-tags',
                    'menu_items' => 'fas fa-utensils',
                    'orders' => 'fas fa-shopping-cart',
                    'order_items' => 'fas fa-list-alt',
                    'contact_messages' => 'fas fa-envelope'
                ];
                
                foreach ($stats as $table => $count) {
                    $icon = $tableIcons[$table] ?? 'fas fa-table';
                    $label = ucfirst(str_replace('_', ' ', $table));
                    echo "<div class='stat-card'>";
                    echo "<i class='$icon'></i>";
                    echo "<div class='stat-number'>$count</div>";
                    echo "<div class='stat-label'>$label</div>";
                    echo "</div>";
                }
                echo "</div>";
                
                // Categories Section
                echo "<div class='section'>";
                echo "<h3><i class='fas fa-tags'></i> Food Categories</h3>";
                $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                $categories = $stmt->fetchAll();
                
                if ($categories) {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Category Name</th><th>Description</th><th>Created</th></tr>";
                    foreach ($categories as $category) {
                        echo "<tr>";
                        echo "<td>" . $category['id'] . "</td>";
                        echo "<td><strong>" . htmlspecialchars($category['name']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($category['description']) . "</td>";
                        echo "<td>" . $category['created_at'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p class='info'>No categories found</p>";
                }
                echo "</div>";
                
                // Menu Items Section
                echo "<div class='section'>";
                echo "<h3><i class='fas fa-utensils'></i> Menu Items</h3>";
                $stmt = $pdo->query("SELECT m.*, c.name as category_name FROM menu_items m 
                                     JOIN categories c ON m.category_id = c.id 
                                     ORDER BY c.name, m.name");
                $menuItems = $stmt->fetchAll();
                
                if ($menuItems) {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Item Name</th><th>Category</th><th>Price</th><th>Description</th></tr>";
                    foreach ($menuItems as $item) {
                        echo "<tr>";
                        echo "<td>" . $item['id'] . "</td>";
                        echo "<td><strong>" . htmlspecialchars($item['name']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($item['category_name']) . "</td>";
                        echo "<td>₹" . number_format($item['price'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($item['description']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p class='info'>No menu items found</p>";
                }
                echo "</div>";
                
                // Orders Section
                echo "<div class='section'>";
                echo "<h3><i class='fas fa-shopping-cart'></i> Orders</h3>";
                $stmt = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 10");
                $orders = $stmt->fetchAll();
                
                if ($orders) {
                    echo "<table>";
                    echo "<tr><th>Order ID</th><th>Customer</th><th>Email</th><th>Total</th><th>Status</th><th>Date</th></tr>";
                    foreach ($orders as $order) {
                        $statusClass = 'status-' . $order['order_status'];
                        echo "<tr>";
                        echo "<td>#" . $order['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($order['customer_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($order['customer_email']) . "</td>";
                        echo "<td>₹" . number_format($order['total_amount'], 2) . "</td>";
                        echo "<td class='$statusClass'>" . ucfirst($order['order_status']) . "</td>";
                        echo "<td>" . $order['order_date'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p class='info'>📝 No orders found yet. Orders will appear here when customers place orders.</p>";
                }
                echo "</div>";
                
                // Contact Messages Section
                echo "<div class='section'>";
                echo "<h3><i class='fas fa-envelope'></i> Contact Messages</h3>";
                $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 10");
                $messages = $stmt->fetchAll();
                
                if ($messages) {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr>";
                    foreach ($messages as $message) {
                        echo "<tr>";
                        echo "<td>" . $message['id'] . "</td>";
                        echo "<td><strong>" . htmlspecialchars($message['name']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($message['email']) . "</td>";
                        echo "<td>" . htmlspecialchars(substr($message['message'], 0, 100)) . 
                             (strlen($message['message']) > 100 ? "..." : "") . "</td>";
                        echo "<td>" . $message['created_at'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p class='info'>📝 No contact messages found yet. Messages will appear here when customers use the contact form.</p>";
                }
                echo "</div>";
                
                // Users Section
                echo "<div class='section'>";
                echo "<h3><i class='fas fa-users'></i> Users</h3>";
                $stmt = $pdo->query("SELECT id, email, phone, address, created_at FROM users ORDER BY created_at DESC");
                $users = $stmt->fetchAll();
                
                if ($users) {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Email</th><th>Phone</th><th>Address</th><th>Registered</th></tr>";
                    foreach ($users as $user) {
                        echo "<tr>";
                        echo "<td>" . $user['id'] . "</td>";
                        echo "<td><strong>" . htmlspecialchars($user['email']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($user['phone'] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($user['address'] ?? 'N/A') . "</td>";
                        echo "<td>" . $user['created_at'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p class='info'>No users found</p>";
                }
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
                echo "<p class='info'>Please make sure XAMPP is running and MySQL service is started.</p>";
            }
            ?>
            
            <div class="section">
                <h3><i class="fas fa-tools"></i> Quick Actions</h3>
                <p><strong>Main Website:</strong> <a href="../index.php" target="_blank">View Customer Website</a></p>
                <p><strong>phpMyAdmin:</strong> <a href="http://localhost/phpmyadmin" target="_blank">Manage Database</a></p>
                <p><strong>Menu Management:</strong> <a href="menu.php">Edit Menu Items</a></p>
            </div>
        </div>
    </div>
</body>
</html> 