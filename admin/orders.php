<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!empty($_POST['order_id']) && !empty($_POST['status'])) {
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        header('Location: orders.php');
        exit;
    }
}

// Get orders with customer details
$orders = [];
try {
    $stmt = $pdo->query("
        SELECT o.*, 
               COUNT(oi.id) as item_count,
               GROUP_CONCAT(CONCAT(oi.quantity, 'x ', mi.name) SEPARATOR ', ') as order_items
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id
        GROUP BY o.id
        ORDER BY o.order_date DESC
    ");
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching orders: " . $e->getMessage();
}

// Get order statistics
$stats = [];
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
    $stats['total_orders'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE order_status != 'cancelled'");
    $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as pending_orders FROM orders WHERE order_status = 'pending'");
    $stats['pending_orders'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as completed_orders FROM orders WHERE order_status = 'completed'");
    $stats['completed_orders'] = $stmt->fetchColumn();
} catch (PDOException $e) {
    $stats = ['total_orders' => 0, 'total_revenue' => 0, 'pending_orders' => 0, 'completed_orders' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - FoodHub Admin</title>
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
            padding: 20px;
        }
        .sidebar h2 {
            margin-bottom: 30px;
            color: #ecf0f1;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar li {
            margin-bottom: 10px;
        }
        .sidebar a {
            color: #bdc3c7;
            text-decoration: none;
            padding: 10px;
            display: block;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #34495e;
            color: white;
        }
        .main-content {
            flex: 1;
            padding: 30px;
            background: #f8f9fa;
        }
        .main-content h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card i {
            font-size: 2rem;
            color: #3498db;
            margin-bottom: 10px;
        }
        .stat-card h3 {
            margin: 10px 0;
            color: #2c3e50;
        }
        .stat-card p {
            font-size: 1.5rem;
            font-weight: bold;
            color: #27ae60;
            margin: 0;
        }
        .orders-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .orders-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .orders-table th, .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .orders-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }
        .status-processing {
            background: #cce5ff;
            color: #004085;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
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
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .order-items {
            font-size: 12px;
            color: #666;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2><i class="fas fa-utensils"></i> FoodHub Admin</h2>
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu Management</a></li>
                <li><a href="database.php"><i class="fas fa-database"></i> Database Viewer</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <a href="logout.php" class="logout-btn">Logout</a>
            
            <h1><i class="fas fa-shopping-cart"></i> Orders Management</h1>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Total Orders</h3>
                    <p><?php echo $stats['total_orders']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-rupee-sign"></i>
                    <h3>Total Revenue</h3>
                    <p>₹<?php echo number_format($stats['total_revenue'], 2); ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>Pending Orders</h3>
                    <p><?php echo $stats['pending_orders']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Completed Orders</h3>
                    <p><?php echo $stats['completed_orders']; ?></p>
                </div>
            </div>
            
            <div class="orders-table">
                <h3><i class="fas fa-list"></i> All Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 20px;">
                                    No orders found. Orders will appear here when customers place them.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['customer_email']); ?><br>
                                        <?php echo htmlspecialchars($order['customer_phone']); ?>
                                    </td>
                                    <td>
                                        <div class="order-items" title="<?php echo htmlspecialchars($order['order_items']); ?>">
                                            <?php echo htmlspecialchars($order['order_items']); ?>
                                        </div>
                                    </td>
                                    <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" style="padding: 3px; font-size: 12px;">
                                                <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="completed" <?php echo $order['order_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 