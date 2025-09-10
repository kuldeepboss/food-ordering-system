<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: register.php');
    exit();
}

// Get cart items
$cartItems = [];
$totalAmount = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartItems[] = $item;
        $totalAmount += $item['price'] * $item['quantity'];
    }
} else {
    header('Location: cart.php');
    exit();
}

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'] ?? '';
    $customerName = $_POST['customer_name'] ?? '';
    $customerPhone = $_POST['customer_phone'] ?? '';
    $customerAddress = $_POST['customer_address'] ?? '';
    
    if (!empty($paymentMethod) && !empty($customerName) && !empty($customerPhone) && !empty($customerAddress)) {
        try {
            // Get user email for order
            $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            $customerEmail = $user['email'] ?? '';
            
            // Create order with correct column names
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, delivery_address, total_amount, payment_method, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $customerName, $customerEmail, $customerPhone, $customerAddress, $totalAmount, $paymentMethod]);
            $orderId = $pdo->lastInsertId();
            
            // Add order items with correct structure
            foreach ($cartItems as $item) {
                // Try to find menu item ID, if not found use 0 or create a fallback
                $menuItemId = $item['id'] ?? 0;
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$orderId, $menuItemId, $item['quantity'], $item['price']]);
            }
            
            // Set order success data for the success page
            $_SESSION['order_success'] = [
                'order_id' => $orderId,
                'customer_name' => $customerName,
                'payment_method' => $paymentMethod,
                'total' => $totalAmount,
                'delivery_address' => $customerAddress
            ];
            
            // Clear cart
            unset($_SESSION['cart']);
            
            // Redirect to success page
            header('Location: order_success.php');
            exit();
        } catch (PDOException $e) {
            $error = 'Payment processing failed: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - FoodHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #ff6b35 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .payment-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }

        .payment-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            max-width: 1400px;
            width: 100%;
            perspective: 1000px;
        }

        /* Order Summary */
        .order-summary {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            transform-style: preserve-3d;
            transition: all 0.4s ease;
        }

        .order-summary:hover {
            transform: translateY(-10px) rotateX(5deg);
        }

        .summary-title {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 2rem;
            text-align: center;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            background: linear-gradient(45deg, #ffd23f, #ff6b35, #ef476f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .order-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 2rem;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .order-item:hover {
            transform: translateY(-5px) rotateX(10deg);
        }

        .item-details h4 {
            color: white;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .item-details p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        .item-price {
            color: #ffd23f;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .total-section {
            border-top: 2px solid rgba(255, 255, 255, 0.2);
            padding-top: 1.5rem;
            text-align: center;
        }

        .total-amount {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ffd23f;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            margin-bottom: 1rem;
        }

        /* Payment Section */
        .payment-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            transform-style: preserve-3d;
            transition: all 0.4s ease;
        }

        .payment-section:hover {
            transform: translateY(-10px) rotateX(-5deg);
        }

        .payment-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
            background: linear-gradient(45deg, #ff6b35, #f7931e, #ffd23f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Payment Methods */
        .payment-methods {
            margin-bottom: 2rem;
        }

        .method-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .method-tab {
            flex: 1;
            padding: 1rem;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-weight: 600;
            color: #666;
        }

        .method-tab.active {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            border-color: #ff6b35;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
        }

        .method-tab i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .payment-method {
            display: none;
        }

        .payment-method.active {
            display: block;
        }

        /* 3D Credit Card */
        .card-container {
            perspective: 1000px;
            margin-bottom: 2rem;
        }

        .credit-card {
            width: 100%;
            height: 200px;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s;
            cursor: pointer;
        }

        .credit-card.flipped {
            transform: rotateY(180deg);
        }

        .credit-card:hover {
            transform: translateY(-10px) rotateX(10deg) rotateY(5deg);
        }

        .card-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 20px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .card-front {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .card-back {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            transform: rotateY(180deg);
        }

        .card-chip {
            width: 50px;
            height: 35px;
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            border-radius: 8px;
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
        }

        .card-chip::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 80%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 2px,
                rgba(0,0,0,0.1) 2px,
                rgba(0,0,0,0.1) 4px
            );
        }

        .card-number {
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 3px;
            margin: 1rem 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .card-details {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .card-holder {
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .card-expiry {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .card-logo {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            font-size: 2rem;
            opacity: 0.8;
        }

        .magnetic-strip {
            width: 100%;
            height: 50px;
            background: #333;
            margin: 1rem 0;
        }

        .cvv-section {
            background: white;
            color: #333;
            padding: 0.5rem;
            border-radius: 5px;
            text-align: right;
            font-weight: 600;
            margin-top: 1rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #ff6b35;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
            transform: translateY(-2px);
        }

        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
        }

        /* UPI Payment */
        .upi-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .upi-option {
            padding: 1.5rem;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            transform-style: preserve-3d;
        }

        .upi-option:hover {
            transform: translateY(-5px) rotateX(10deg);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .upi-option.selected {
            border-color: #ff6b35;
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
        }

        .upi-logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            background: white;
            color: #333;
        }

        .upi-option.selected .upi-logo {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* Pay Button */
        .pay-button {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
        }

        .pay-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .pay-button:hover::before {
            left: 100%;
        }

        .pay-button:hover {
            transform: translateY(-3px) rotateX(10deg);
            box-shadow: 0 15px 40px rgba(255, 107, 53, 0.6);
        }

        /* Error Message */
        .error {
            color: #e74c3c;
            background: rgba(231, 76, 60, 0.1);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid #e74c3c;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .payment-wrapper {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .method-tabs {
                flex-direction: column;
            }
            
            .upi-options {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* 3D Text Animation */
        @keyframes textFloat {
            0%, 100% { transform: translateY(0px) rotateX(0deg); }
            50% { transform: translateY(-10px) rotateX(5deg); }
        }

        .floating-text {
            animation: textFloat 3s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-wrapper">
            <!-- Order Summary -->
            <div class="order-summary">
                <h2 class="summary-title floating-text">Order Summary</h2>
                
                <div class="order-items">
                    <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <div class="item-details">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p>Quantity: <?php echo $item['quantity']; ?></p>
                        </div>
                        <div class="item-price">
                            ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="total-section">
                    <div class="total-amount floating-text">
                        ₹<?php echo number_format($totalAmount, 2); ?>
                    </div>
                    <p style="color: rgba(255, 255, 255, 0.8);">Total Amount</p>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="payment-section">
                <h2 class="payment-title floating-text">Payment Details</h2>
                
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" id="paymentForm">
                    <!-- Customer Details -->
                    <div class="form-group">
                        <label for="customer_name">Full Name *</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_phone">Phone Number *</label>
                        <input type="tel" id="customer_phone" name="customer_phone" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_address">Delivery Address *</label>
                        <input type="text" id="customer_address" name="customer_address" required>
                    </div>

                    <!-- Payment Methods -->
                    <div class="payment-methods">
                        <div class="method-tabs">
                            <div class="method-tab active" onclick="switchPaymentMethod('card')">
                                <i class="fas fa-credit-card"></i>
                                Credit Card
                            </div>
                            <div class="method-tab" onclick="switchPaymentMethod('upi')">
                                <i class="fas fa-mobile-alt"></i>
                                UPI Payment
                            </div>
                            <div class="method-tab" onclick="switchPaymentMethod('cash')">
                                <i class="fas fa-money-bill-wave"></i>
                                Cash on Delivery
                            </div>
                        </div>

                        <!-- Card Payment -->
                        <div id="cardPayment" class="payment-method active">
                            <div class="card-container">
                                <div class="credit-card" id="creditCard">
                                    <div class="card-face card-front">
                                        <div class="card-chip"></div>
                                        <div class="card-number" id="cardNumberDisplay">**** **** **** ****</div>
                                        <div class="card-details">
                                            <div class="card-holder">
                                                <div style="font-size: 0.7rem; opacity: 0.8;">CARD HOLDER</div>
                                                <div id="cardHolderDisplay">YOUR NAME</div>
                                            </div>
                                            <div class="card-expiry">
                                                <div style="font-size: 0.7rem; opacity: 0.8;">EXPIRES</div>
                                                <div id="cardExpiryDisplay">MM/YY</div>
                                            </div>
                                        </div>
                                        <div class="card-logo">
                                            <i class="fab fa-cc-visa"></i>
                                        </div>
                                    </div>
                                    <div class="card-face card-back">
                                        <div class="magnetic-strip"></div>
                                        <div class="cvv-section">
                                            CVV: <span id="cvvDisplay">***</span>
                                        </div>
                                        <p style="font-size: 0.8rem; margin-top: 1rem; opacity: 0.8;">
                                            This card is property of FoodHub Bank. If found, please return to nearest branch.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            
                            <div class="form-group">
                                <label for="card_holder">Card Holder Name</label>
                                <input type="text" id="card_holder" name="card_holder" placeholder="John Doe">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date</label>
                                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" maxlength="5">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="3">
                                </div>
                            </div>
                        </div>

                        <!-- UPI Payment -->
                        <div id="upiPayment" class="payment-method">
                            <div class="upi-options" id="upiOptions">
                                <div class="upi-option" onclick="selectUPI('googlepay')">
                                    <div class="upi-logo" style="background: #4285f4;">
                                        <i class="fab fa-google"></i>
                                    </div>
                                    <div>Google Pay</div>
                                </div>
                                <div class="upi-option" onclick="selectUPI('paytm')">
                                    <div class="upi-logo" style="background: #00baf2;">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div>Paytm</div>
                                </div>
                                <div class="upi-option" onclick="selectUPI('phonepe')">
                                    <div class="upi-logo" style="background: #5f259f;">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <div>PhonePe</div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="upi_id">UPI ID</label>
                                <input type="text" id="upi_id" name="upi_id" placeholder="yourname@upi">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="payment_method" id="paymentMethodInput" value="card">
                    
                    <button type="submit" class="pay-button">
                        <i class="fas fa-lock"></i> Pay ₹<?php echo number_format($totalAmount, 2); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Payment method switching
        function switchPaymentMethod(method) {
            // Update tabs
            document.querySelectorAll('.method-tab').forEach(tab => tab.classList.remove('active'));
            event.target.closest('.method-tab').classList.add('active');
            
            // Update payment sections
            document.querySelectorAll('.payment-method').forEach(section => section.classList.remove('active'));
            
            if (method === 'card') {
                document.getElementById('cardPayment').classList.add('active');
                document.getElementById('paymentMethodInput').value = 'card';
            } else if (method === 'upi') {
                document.getElementById('upiPayment').classList.add('active');
                document.getElementById('paymentMethodInput').value = 'upi';
            } else if (method === 'cash') {
                document.getElementById('paymentMethodInput').value = 'cash';
            }
        }

        // Card input formatting and animation
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
            
            // Update card display
            document.getElementById('cardNumberDisplay').textContent = 
                formattedValue || '**** **** **** ****';
        });

        document.getElementById('card_holder').addEventListener('input', function(e) {
            document.getElementById('cardHolderDisplay').textContent = 
                e.target.value.toUpperCase() || 'YOUR NAME';
        });

        document.getElementById('expiry_date').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
            
            document.getElementById('cardExpiryDisplay').textContent = 
                value || 'MM/YY';
        });

        document.getElementById('cvv').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
            
            document.getElementById('cvvDisplay').textContent = 
                value || '***';
        });

        // Card flip animation
        document.getElementById('cvv').addEventListener('focus', function() {
            document.getElementById('creditCard').classList.add('flipped');
        });

        document.getElementById('cvv').addEventListener('blur', function() {
            document.getElementById('creditCard').classList.remove('flipped');
        });

        // UPI selection
        function selectUPI(provider) {
            document.querySelectorAll('.upi-option').forEach(option => 
                option.classList.remove('selected'));
            event.target.closest('.upi-option').classList.add('selected');
            
            document.getElementById('paymentMethodInput').value = 'upi_' + provider;
        }

        // Form validation
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const paymentMethod = document.getElementById('paymentMethodInput').value;
            
            if (paymentMethod === 'card') {
                const cardNumber = document.getElementById('card_number').value;
                const cardHolder = document.getElementById('card_holder').value;
                const expiryDate = document.getElementById('expiry_date').value;
                const cvv = document.getElementById('cvv').value;
                
                if (!cardNumber || !cardHolder || !expiryDate || !cvv) {
                    e.preventDefault();
                    alert('Please fill in all card details');
                    return;
                }
            } else if (paymentMethod.startsWith('upi')) {
                const upiId = document.getElementById('upi_id').value;
                if (!upiId) {
                    e.preventDefault();
                    alert('Please enter your UPI ID');
                    return;
                }
            }
        });
    </script>
</body>
</html>
