<?php
session_start();
require_once 'config/database.php';

// Get cart count
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - FoodHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .cart-page {
            padding-top: 100px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .cart-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .cart-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .cart-items {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-image {
            width: 100px;
            height: 100px;
            border-radius: 15px;
            object-fit: cover;
            margin-right: 1.5rem;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .cart-item-price {
            font-size: 1.1rem;
            color: #ff6b35;
            font-weight: 600;
        }
        
        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-btn {
            width: 35px;
            height: 35px;
            border: 2px solid #ff6b35;
            background: white;
            color: #ff6b35;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .quantity-btn:hover {
            background: #ff6b35;
            color: white;
        }
        
        .quantity-display {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            min-width: 30px;
            text-align: center;
        }
        
        .remove-btn {
            background: #ff4444;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .remove-btn:hover {
            background: #cc0000;
        }
        
        .cart-summary {
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
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }
        
        .summary-total {
            border-top: 2px solid #eee;
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.3rem;
            font-weight: 700;
            color: #ff6b35;
        }
        
        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: #ff6b35;
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .checkout-btn:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        
        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-cart i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        .empty-cart h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .continue-shopping {
            background: #ff6b35;
            color: white;
            padding: 1rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .continue-shopping:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
            
            .cart-item-image {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .cart-item-controls {
                justify-content: center;
                margin-top: 1rem;
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
                <li><a href="cart.php" class="nav-link cart-icon active">
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

    <!-- Cart Page -->
    <section class="cart-page">
        <div class="cart-container">
            <div class="cart-header">
                <h1>Shopping Cart</h1>
                <p>Review your order and proceed to checkout</p>
            </div>
            
            <div class="cart-content">
                <div class="cart-items" id="cartItems">
                    <!-- Cart items will be loaded here -->
                </div>
                
                <div class="cart-summary">
                    <h3 class="summary-title">Order Summary</h3>
                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span id="subtotal">₹0.00</span>
                    </div>
                    <div class="summary-item">
                        <span>Delivery Fee:</span>
                        <span id="deliveryFee">₹120</span>
                    </div>
                    <div class="summary-item summary-total">
                        <span>Total:</span>
                        <span id="total">₹0.00</span>
                    </div>
                    <button class="checkout-btn" id="checkoutBtn" onclick="proceedToCheckout()">
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Load cart items on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadCartItems();
        });

        function loadCartItems() {
            const cartContainer = document.getElementById('cartItems');
            fetch('process/get_cart.php')
                .then(res => res.json())
                .then(({ success, cart, cart_count }) => {
                    if (!success) throw new Error('Failed to fetch cart');
                    const cartItems = Array.isArray(cart) ? cart : Object.values(cart || {});

                    if (cartItems.length === 0) {
                        cartContainer.innerHTML = `
                            <div class="empty-cart">
                                <i class="fas fa-shopping-cart"></i>
                                <h2>Your cart is empty</h2>
                                <p>Add some delicious food to your cart!</p>
                                <a href="index.php#menu" class="continue-shopping">Continue Shopping</a>
                            </div>
                        `;
                        updateSummary(0);
                        updateCartCount(0);
                        return;
                    }

                    let cartHTML = '';
                    let subtotal = 0;
                    cartItems.forEach(item => {
                        const itemTotal = Number(item.price) * Number(item.quantity);
                        subtotal += itemTotal;
                        cartHTML += `
                            <div class="cart-item">
                                <div class="cart-item-details">
                                    <h3 class="cart-item-name">${item.name}</h3>
                                    <p class="cart-item-price">₹${Number(item.price).toFixed(2)}</p>
                                </div>
                                <div class="cart-item-controls">
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                                        <span class="quantity-display">${item.quantity}</span>
                                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                                    </div>
                                    <button class="remove-btn" onclick="removeItem(${item.id})">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        `;
                    });

                    cartContainer.innerHTML = cartHTML;
                    updateSummary(subtotal);
                    updateCartCount(cart_count || 0);
                })
                .catch(() => {
                    cartContainer.innerHTML = '<div class="empty-cart"><h2>Error loading cart</h2></div>';
                    updateSummary(0);
                });
        }

        function updateQuantity(itemId, change) {
            fetch('process/cart_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_quantity&item_id=${itemId}&change=${change}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCartItems();
                    updateCartCount(data.cart_count);
                }
            });
        }

        function removeItem(itemId) {
            fetch('process/cart_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove_item&item_id=${itemId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCartItems();
                    updateCartCount(data.cart_count);
                }
            });
        }

        function updateSummary(subtotal) {
            const deliveryFee = 120.00; // Fixed delivery fee
            const total = subtotal + deliveryFee;
            
            document.getElementById('subtotal').textContent = `₹${subtotal.toFixed(2)}`;
            document.getElementById('deliveryFee').textContent = `₹${deliveryFee.toFixed(2)}`;
            document.getElementById('total').textContent = `₹${total.toFixed(2)}`;
        }

        function updateCartCount(count) {
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = count;
            }
        }

        function proceedToCheckout() {
            fetch('process/get_cart.php')
                .then(res => res.json())
                .then(({ success, cart }) => {
                    const cartItems = Array.isArray(cart) ? cart : Object.values(cart || {});
                    if (!success || cartItems.length === 0) {
                        alert('Your cart is empty!');
                        return;
                    }
                    window.location.href = 'checkout.php';
                })
                .catch(() => alert('Unable to validate cart. Please try again.'));
        }
    </script>
</body>
</html> 