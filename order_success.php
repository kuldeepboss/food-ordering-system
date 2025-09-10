<?php
session_start();

// Redirect if no order success data
if (!isset($_SESSION['order_success'])) {
    header('Location: index.php');
    exit;
}

$orderData = $_SESSION['order_success'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - FoodHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .success-page {
            padding-top: 100px;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
            text-align: center;
            animation: slideInUp 0.6s ease-out;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: bounce 1s ease-out;
        }
        
        .success-icon i {
            font-size: 3rem;
            color: white;
        }
        
        .success-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .success-subtitle {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
        }
        
        .order-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: left;
        }
        
        .order-details h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            font-weight: 700;
            color: #ff6b35;
            font-size: 1.1rem;
        }
        
        .status-badge {
            background: #ff6b35;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            display: inline-block;
            margin: 1rem 0;
        }
        
        .contact-info {
            background: #fff5f2;
            border: 2px solid #ff6b35;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        
        .contact-info h4 {
            color: #ff6b35;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .contact-item i {
            width: 20px;
            margin-right: 0.5rem;
            color: #ff6b35;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-primary {
            background: #ff6b35;
            color: white;
        }
        
        .btn-primary:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: transparent;
            color: #ff6b35;
            border: 2px solid #ff6b35;
        }
        
        .btn-secondary:hover {
            background: #ff6b35;
            color: white;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        @media (max-width: 768px) {
            .success-container {
                padding: 2rem;
            }
            
            .success-title {
                font-size: 2rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
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
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <!-- Success Page -->
    <section class="success-page">
        <div class="success-container">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 class="success-title">Order Placed Successfully!</h1>
            <p class="success-subtitle">Thank you for your order. We're preparing your delicious food!</p>
            
            <div class="status-badge">
                <i class="fas fa-clock"></i> Order Status: Pending
            </div>
            
            <div class="order-details">
                <h3><i class="fas fa-receipt"></i> Order Details</h3>
                <div class="detail-row">
                    <span>Order ID:</span>
                    <span>#<?php echo $orderData['order_id']; ?></span>
                </div>
                <div class="detail-row">
                    <span>Customer:</span>
                    <span><?php echo htmlspecialchars($orderData['customer_name']); ?></span>
                </div>
                <div class="detail-row">
                    <span>Payment Method:</span>
                    <span><?php echo ucfirst(htmlspecialchars($orderData['payment_method'])); ?></span>
                </div>
                <div class="detail-row">
                    <span>Total Amount:</span>
                    <span>₹<?php echo number_format($orderData['total'], 2); ?></span>
                </div>
            </div>
            
            <div class="contact-info">
                <h4><i class="fas fa-info-circle"></i> Important Information</h4>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>Call us: +91 7016688687</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Delivery Address: <?php echo htmlspecialchars($orderData['delivery_address']); ?></span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <span>Estimated Delivery: 30-45 minutes</span>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <a href="menu.php" class="btn btn-secondary">
                    <i class="fas fa-utensils"></i> Order More Food
                </a>
            </div>
        </div>
    </section>

    <script>
        // Clear order success data after showing
        setTimeout(() => {
            // Keep the data for a while in case user refreshes
        }, 5000);
    </script>
</body>
</html> 