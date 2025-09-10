<?php
session_start();
require_once 'config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: register.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Get user orders with items
$orders = [];
try {
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(oi.id) as item_count, SUM(oi.quantity) as total_quantity
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? 
        GROUP BY o.id 
        ORDER BY o.order_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    // Orders table might not have user_id column yet
}

// Get order items for a specific order
function getOrderItems($pdo, $orderId) {
    try {
        $stmt = $pdo->prepare("
            SELECT oi.*, mi.name as item_name, mi.image as item_image
            FROM order_items oi
            LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - FoodHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }
        .orders-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .orders-header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .orders-header p {
            color: #666;
            font-size: 1.1rem;
        }
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-left: 4px solid #ff6b35;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        .order-id {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        .order-date {
            color: #666;
            font-size: 0.9rem;
        }
        .order-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-preparing { background: #d4edda; color: #155724; }
        .status-out_for_delivery { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d1e7dd; color: #0f5132; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .order-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .summary-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }
        .summary-item h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-item p {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #ff6b35;
        }
        .order-items {
            margin-top: 1.5rem;
        }
        .order-items h3 {
            margin-bottom: 1rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .item-list {
            display: grid;
            gap: 1rem;
        }
        .item-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background: #e9ecef;
        }
        .item-details {
            flex: 1;
        }
        .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        .item-quantity {
            color: #666;
            font-size: 0.9rem;
        }
        .item-price {
            font-weight: 600;
            color: #ff6b35;
        }
        .no-orders {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }
        .no-orders i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 1rem;
        }
        .no-orders h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        .no-orders p {
            margin-bottom: 2rem;
        }
        .btn-order {
            background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%);
            color: white;
            text-decoration: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s ease;
            display: inline-block;
        }
        .btn-order:hover {
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .order-summary {
                grid-template-columns: 1fr;
            }
            .item-card {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <i class="fas fa-utensils"></i>
                <span>FoodHub</span>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="index.php#menu" class="nav-link">Menu</a></li>
                <li><a href="index.php#about" class="nav-link">About</a></li>
                <li><a href="index.php#contact" class="nav-link">Contact</a></li>
                <li><a href="cart.php" class="nav-link cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">0</span>
                </a></li>
                <li class="user-menu">
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </a>
                    <div class="user-dropdown">
                        <a href="profile.php">My Profile</a>
                        <a href="orders.php">My Orders</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <div class="orders-container">
        <div class="orders-header">
            <h1>My Orders</h1>
            <p>Track your order history and delivery status</p>
        </div>

        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?php echo $order['id']; ?></div>
                            <div class="order-date"><?php echo date('F j, Y \a\t g:i A', strtotime($order['order_date'])); ?></div>
                        </div>
                        <span class="order-status status-<?php echo $order['order_status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                        </span>
                    </div>

                    <div class="order-summary">
                        <div class="summary-item">
                            <h4>Total Amount</h4>
                            <p>₹<?php echo number_format($order['total_amount'], 2); ?></p>
                        </div>
                        <div class="summary-item">
                            <h4>Items</h4>
                            <p><?php echo $order['item_count']; ?> items</p>
                        </div>
                        <div class="summary-item">
                            <h4>Quantity</h4>
                            <p><?php echo $order['total_quantity']; ?> total</p>
                        </div>
                        <div class="summary-item">
                            <h4>Payment</h4>
                            <p><?php echo ucfirst($order['payment_method']); ?></p>
                        </div>
                    </div>

                    <div class="order-items">
                        <h3><i class="fas fa-utensils"></i> Order Items</h3>
                        <div class="item-list">
                            <?php 
                            $orderItems = getOrderItems($pdo, $order['id']);
                            foreach ($orderItems as $item): 
                            ?>
                                <div class="item-card">
                                    <img src="<?php echo htmlspecialchars($item['item_image'] ?? 'images/food-placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                                         class="item-image"
                                         onerror="this.src='images/food-placeholder.jpg'">
                                    <div class="item-details">
                                        <div class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                        <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                                    </div>
                                    <div class="item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if ($order['delivery_address']): ?>
                        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #f0f0f0;">
                            <h4 style="margin-bottom: 0.5rem; color: #333;">
                                <i class="fas fa-map-marker-alt"></i> Delivery Address
                            </h4>
                            <p style="color: #666; margin: 0;"><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-orders">
                <i class="fas fa-shopping-bag"></i>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet. Start exploring our delicious menu!</p>
                <a href="index.php" class="btn-order">
                    <i class="fas fa-utensils"></i> Browse Menu
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/script.js"></script>
</body>
</html> 