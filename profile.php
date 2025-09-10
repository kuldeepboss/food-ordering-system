<?php
session_start();
require_once 'config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: register.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$error = '';
$success = '';

// Get user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit();
    }
} catch (PDOException $e) {
    $error = 'Failed to load user data';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    try {
        // Update basic info
        $stmt = $pdo->prepare("UPDATE users SET phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$phone, $address, $_SESSION['user_id']]);
            
            // Handle password change if provided
            if (!empty($current_password)) {
                if (!password_verify($current_password, $user['password'])) {
                    $error = 'Current password is incorrect';
                } elseif (empty($new_password)) {
                    $error = 'New password is required';
                } elseif (strlen($new_password) < 6) {
                    $error = 'New password must be at least 6 characters long';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match';
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                    $success = 'Profile and password updated successfully!';
                }
            } else {
                $success = 'Profile updated successfully!';
            }
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
        } catch (PDOException $e) {
            $error = 'Failed to update profile';
        }
    }

// Get user orders
$orders = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    // Orders table might not have user_id column yet
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - FoodHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .profile-header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .profile-header p {
            color: #666;
            font-size: 1.1rem;
        }
        .profile-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }
        .profile-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .profile-section h2 {
            color: #333;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b35;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .btn-update {
            background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .btn-update:hover {
            transform: translateY(-2px);
        }
        .error {
            color: #e74c3c;
            background: #fdf2f2;
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid #fecaca;
            margin-bottom: 1rem;
        }
        .success {
            color: #27ae60;
            background: #f0f9ff;
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid #b3e5fc;
            margin-bottom: 1rem;
        }
        .orders-list {
            list-style: none;
            padding: 0;
        }
        .order-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #ff6b35;
        }
        .order-item h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }
        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            font-size: 0.9rem;
            color: #666;
        }
        .order-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
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
        .no-orders {
            text-align: center;
            color: #666;
            padding: 2rem;
        }
        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .profile-header h1 {
                font-size: 2rem;
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

    <div class="profile-container">
        <div class="profile-header">
            <h1>My Profile</h1>
            <p>Manage your account information and view your order history</p>
        </div>

        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="profile-content">
            <div class="profile-section">
                <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Delivery Address</label>
                        <textarea id="address" name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <h3 style="margin: 2rem 0 1rem 0; color: #333;">Change Password</h3>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit" class="btn-update">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>

            <div class="profile-section">
                <h2><i class="fas fa-list"></i> Recent Orders</h2>
                <?php if (!empty($orders)): ?>
                    <ul class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <li class="order-item">
                                <h4>Order #<?php echo $order['id']; ?></h4>
                                <div class="order-details">
                                    <div>
                                        <strong>Date:</strong> <?php echo date('M j, Y', strtotime($order['order_date'])); ?><br>
                                        <strong>Amount:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?><br>
                                        <strong>Payment:</strong>₹<?php echo ucfirst($order['payment_method']); ?>
                                    </div>
                                    <div>
                                        <strong>Status:</strong><br>
                                        <span class="order-status status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="no-orders">
                        <i class="fas fa-shopping-bag" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <p>No orders yet</p>
                        <p><a href="index.php#menu" style="color: #ff6b35; text-decoration: none;">Browse our menu</a> to place your first order!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html> 