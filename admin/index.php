<?php
session_start();
require_once '../config/database.php';

// Simple admin authentication (you should implement proper authentication)
$admin_username = 'admin';
$admin_password = 'admin123';

if (!isset($_SESSION['admin_logged_in'])) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
            $_SESSION['admin_logged_in'] = true;
        } else {
            $error = 'Invalid credentials';
        }
    }
    
    if (!isset($_SESSION['admin_logged_in'])) {
        // Show login form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Login - FoodHub</title>
            <link rel="stylesheet" href="../css/style.css">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <style>
                .admin-login {
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                .login-form {
                    background: white;
                    padding: 2rem;
                    border-radius: 10px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                    width: 100%;
                    max-width: 400px;
                }
                .login-form h2 {
                    text-align: center;
                    margin-bottom: 2rem;
                    color: #333;
                }
                .form-group {
                    margin-bottom: 1rem;
                }
                .form-group label {
                    display: block;
                    margin-bottom: 0.5rem;
                    color: #555;
                }
                .form-group input {
                    width: 100%;
                    padding: 0.75rem;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    font-size: 1rem;
                }
                .btn-login {
                    width: 100%;
                    padding: 0.75rem;
                    background: #667eea;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    font-size: 1rem;
                    cursor: pointer;
                    transition: background 0.3s;
                }
                .btn-login:hover {
                    background: #5a6fd8;
                }
                .error {
                    color: #e74c3c;
                    text-align: center;
                    margin-bottom: 1rem;
                }
            </style>
        </head>
        <body>
            <div class="admin-login">
                <div class="login-form">
                    <h2><i class="fas fa-utensils"></i> FoodHub Admin</h2>
                    <?php if (isset($error)): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn-login">Login</button>
                    </form>
                    <p style="text-align: center; margin-top: 1rem; color: #666;">
                        Username: admin<br>
                        Password: admin123
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Get statistics
$stats = [];
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_items FROM menu_items");
    $stats['total_items'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
    $stats['total_orders'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_categories FROM categories");
    $stats['total_categories'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE order_status != 'cancelled'");
    $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $stats = ['total_items' => 0, 'total_orders' => 0, 'total_categories' => 0, 'total_revenue' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FoodHub</title>
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
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        .stat-card h3 {
            margin: 0;
            color: #333;
        }
        .stat-card p {
            margin: 0.5rem 0 0 0;
            color: #666;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0.25rem;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .logout-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #e74c3c;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2><i class="fas fa-utensils"></i> FoodHub Admin</h2>
            <ul>
                <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu Management</a></li>
                <li><a href="database.php"><i class="fas fa-database"></i> Database Viewer</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <a href="logout.php" class="logout-btn">Logout</a>
            
            <h1>Dashboard</h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-utensils"></i>
                    <h3>Total Menu Items</h3>
                    <p><?php echo $stats['total_items']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Total Orders</h3>
                    <p><?php echo $stats['total_orders']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-tags"></i>
                    <h3>Categories</h3>
                    <p><?php echo $stats['total_categories']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-rupee-sign"></i>
                    <h3>Total Revenue</h3>
                    <p>₹<?php echo number_format($stats['total_revenue'], 2); ?></p>
                </div>
            </div>
            
            <div class="table-container">
                <h2>Recent Orders</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 5");
                            while ($order = $stmt->fetch()) {
                                echo "<tr>";
                                echo "<td>#{$order['id']}</td>";
                                echo "<td>{$order['customer_name']}</td>";
                                echo "<td>₹{$order['total_amount']}</td>";
                                echo "<td><span class='status-{$order['order_status']}'>{$order['order_status']}</span></td>";
                                echo "<td>" . date('M j, Y', strtotime($order['order_date'])) . "</td>";
                                echo "</tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='5'>No orders found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 