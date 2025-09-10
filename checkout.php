<?php
session_start();
require_once 'config/database.php';

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$deliveryFee = 120.00;
$total = $subtotal + $deliveryFee;

// Get cart count
$cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - FoodHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .checkout-page {
            padding-top: 100px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .checkout-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .checkout-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .checkout-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .checkout-form {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .form-section h3 {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 1rem;
            border-bottom: 2px solid #ff6b35;
            padding-bottom: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 1rem;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ff6b35;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .payment-method {
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover {
            border-color: #ff6b35;
        }
        
        .payment-method.selected {
            border-color: #ff6b35;
            background: #fff5f2;
        }
        
        .payment-method i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #ff6b35;
        }
        
        .order-summary {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }
        
        .summary-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1.5rem;
        }
        
        .order-items {
            margin-bottom: 2rem;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .item-details {
            color: #666;
            font-size: 0.9rem;
        }
        
        .item-price {
            font-weight: 600;
            color: #ff6b35;
        }
        
        .summary-totals {
            border-top: 2px solid #eee;
            padding-top: 1rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .summary-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ff6b35;
            border-top: 2px solid #eee;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        .place-order-btn {
            width: 100%;
            padding: 1.5rem;
            background: #ff6b35;
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .place-order-btn:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        
        .place-order-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        @media (max-width: 768px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .payment-methods {
                grid-template-columns: repeat(2, 1fr);
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
                    <span class="cart-count"><?php echo $cartCount; ?></span>
                </a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
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
                <?php else: ?>
                    <li><a href="register.php" class="nav-link btn-register">Register</a></li>
                <?php endif; ?>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <!-- Checkout Page -->
    <section class="checkout-page">
        <div class="checkout-container">
            <div class="checkout-header">
                <h1>Checkout</h1>
                <p>Complete your order and payment</p>
            </div>
            
            <div class="checkout-content">
                <div class="checkout-form">
                    <form id="checkoutForm" action="payment.php" method="GET">
                        <!-- Customer Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-user"></i> Customer Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName">First Name *</label>
                                    <input type="text" id="firstName" name="firstName" required>
                                </div>
                                <div class="form-group">
                                    <label for="lastName">Last Name *</label>
                                    <input type="text" id="lastName" name="lastName" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''; ?>" required>
                            </div>
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required value="<?php echo isset($_SESSION['user_phone']) ? htmlspecialchars($_SESSION['user_phone']) : ''; ?>">
                </div>
                        </div>
                        
                        <!-- Delivery Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-map-marker-alt"></i> Delivery Information</h3>
                            <div class="form-group">
                                <label for="address">Delivery Address *</label>
                                <textarea id="address" name="address" rows="3" required placeholder="Enter your full delivery address"></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City *</label>
                                    <input type="text" id="city" name="city" required>
                                </div>
                                <div class="form-group">
                                    <label for="zipCode">ZIP Code *</label>
                                    <input type="text" id="zipCode" name="zipCode" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="form-section">
                            <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                            <div class="payment-methods">
                                <div class="payment-method" data-method="cash">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <div>Cash on Delivery</div>
                                </div>
                                <div class="payment-method" data-method="card">
                                    <i class="fas fa-credit-card"></i>
                                    <div>Credit Card</div>
                                </div>
                                <div class="payment-method" data-method="upi">
                                    <i class="fas fa-mobile-alt"></i>
                                    <div>UPI Payment</div>
                                </div>
                            </div>
                            <input type="hidden" id="paymentMethod" name="paymentMethod" value="cash">
                        </div>
                        
                        <!-- Special Instructions -->
                        <div class="form-section">
                            <h3><i class="fas fa-comment"></i> Special Instructions</h3>
                            <div class="form-group">
                                <label for="instructions">Additional Notes (Optional)</label>
                                <textarea id="instructions" name="instructions" rows="3" placeholder="Any special instructions for your order..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="order-summary">
                    <h3 class="summary-title">Order Summary</h3>
                    <div class="order-items">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="order-item">
                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-details">Qty: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-totals">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery Fee:</span>
                            <span>₹<?php echo number_format($deliveryFee, 2); ?></span>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Total:</span>
                            <span>₹<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                    
                    <button class="place-order-btn" onclick="placeOrder()">
                        <i class="fas fa-check"></i> Place Order
                    </button>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                // Remove selected class from all methods
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                // Add selected class to clicked method
                this.classList.add('selected');
                // Update hidden input
                document.getElementById('paymentMethod').value = this.dataset.method;
            });
        });
        
        // Set default payment method
        document.querySelector('[data-method="cash"]').classList.add('selected');
        
        function placeOrder() {
            const form = document.getElementById('checkoutForm');
            const submitBtn = document.querySelector('.place-order-btn');
            
            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            // Submit form
            form.submit();
        }
        
        // Form validation
        document.getElementById('checkoutForm').addEventListener('input', function() {
            const submitBtn = document.querySelector('.place-order-btn');
            if (this.checkValidity()) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        });
    </script>
</body>
</html> 