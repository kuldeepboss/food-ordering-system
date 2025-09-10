<?php
session_start();
require_once 'config/database.php';

// Get cart count
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
}

// Get menu items from database with categories
$menuItems = [];
try {
    $stmt = $pdo->query("
        SELECT mi.*, c.name as category_name 
        FROM menu_items mi 
        LEFT JOIN categories c ON mi.category_id = c.id 
        ORDER BY c.name, mi.name
    ");
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If table doesn't exist, use default data
    $menuItems = [
       ];
}

// Get unique categories for filters
$categories = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = ['Starter', 'Paneer Special Dish', 'Kaju Special Dish'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodHub - Online Food Ordering System</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Vanta.js for 3D Background -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.birds.min.js"></script>
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
                <li><a href="#home" class="nav-link">Home</a></li>
                <li><a href="menu.php" class="nav-link">Menu</a></li>
                <li><a href="#about" class="nav-link">About</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="cart.php" class="nav-link cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo $cartCount; ?></span>
                        <span class="cart-label">Add to Cart</span>
                    </a></li>
                <?php else: ?>
                    <li><a href="register.php" class="nav-link cart-icon" title="Register to access cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a></li>
                <?php endif; ?>
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

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1 class="hero-title">
                <span class="animate-text">Order Fresh Food</span>
                <span class="animate-text">Anytime 🍴</span>
            </h1>
            <p class="hero-subtitle">Experience the future of food delivery with our modern 3D interface</p>
            <div class="hero-buttons">
                <a href="#menu" class="btn btn-primary">Browse Menu</a>
                <a href="#about" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
        <div class="hero-image">
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose FoodHub?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Quality Food</h3>
                    <p>Fresh ingredients and delicious recipes</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3>Secure Payment</h3>
                    <p>Multiple payment options available</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Call us anytime: +91 7016688687</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Section -->
    <section id="menu" class="menu-section" style="margin-top: 0; padding: 2rem 0;">
        <div class="container">
            <h2 class="section-title">Our Menu</h2>            
            <!-- Menu Filters -->
            <div class="menu-filters">
                <button class="filter-btn active" data-category="all">All Items</button>
                <?php foreach ($categories as $category): ?>
                    <button class="filter-btn" data-category="<?php echo strtolower(str_replace(' ', '_', $category)); ?>">
                        <?php echo htmlspecialchars($category); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <!-- Menu Grid -->
            <div class="menu-grid" id="menuGrid">
                <?php if (empty($menuItems)): ?>
                    <div class="no-items">
                        <i class="fas fa-utensils"></i>
                        <h3>No menu items available</h3>
                        <p>Please check back later or contact us for more information.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($menuItems as $item): 
                        $cat = strtolower(str_replace(' ', '_', $item['category_name'])); ?>
                    <div class="menu-item" data-category="<?php echo $cat; ?>" 
                         <?php if (isset($_SESSION['user_id'])): ?>
                             onclick="showItemDetails(<?php echo htmlspecialchars(json_encode($item)); ?>)"
                         <?php else: ?>
                             onclick="showNotification('Please register first to view details and add items to cart!', 'warning')"
                         <?php endif; ?>>
                        <?php if (!empty($item['image'])): ?>
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             onerror="this.src='images/placeholder.jpg'; this.onerror=null;"
                             class="menu-item-image">
                        <?php else: ?>
                        <div class="menu-item-image-placeholder" style="width: 100%; height: 200px; background: linear-gradient(135deg, #f5f5f5, #e0e0e0); display: flex; align-items: center; justify-content: center; color: #666; font-size: 1.2rem; font-weight: 500;">
                            <i class="fas fa-utensils" style="font-size: 3rem; color: #ccc; margin-bottom: 10px;"></i>
                            <br><?php echo htmlspecialchars($item['name']); ?>
                        </div>
                        <?php endif; ?>
                        <div class="menu-item-content">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="menu-item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="menu-item-price">
                                <span class="price">₹<?php echo number_format($item['price'], 2); ?></span>
                                <?php if (!isset($_SESSION['user_id'])): ?>
                                    <button class="view-details" 
                                            onclick="event.stopPropagation(); showNotification('Please register first to view details and add items to cart!', 'warning'); setTimeout(function(){ window.location.href='register.php'; }, 100);">
                                        <i class="fas fa-lock"></i> Register to Order
                                    </button>
                                <?php else: ?>
                                    <button class="view-details" onclick="event.stopPropagation(); showItemDetails(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

<script>
// Menu filter functionality
document.querySelectorAll('.filter-btn').forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        // Add active to clicked button
        button.classList.add('active');

        const category = button.getAttribute('data-category');
        const items = document.querySelectorAll('.menu-item');

        items.forEach(item => {
            if (category === 'all' || item.getAttribute('data-category') === category) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>


    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>About FoodHub</h2>
                    <p>We are passionate about delivering the best food experience to our customers. Our team of expert chefs creates delicious meals using fresh, high-quality ingredients.</p>
                    <div class="stats">
                        <div class="stat">
                            <h3>100</h3>
                            <p>Happy Customers</p>
                        </div>
                        <div class="stat">
                            <h3><?php echo count($menuItems); ?>+</h3>
                            <p>Menu Items</p>
                        </div>
                        <div class="stat">
                            <h3>30min</h3>
                            <p>Delivery Time</p>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="images/restaurant.jpg" alt="Restaurant">
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <h2 class="section-title">Contact Us</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                                <h3>Phone</h3>
                                <p>+91 7016688687</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h3>Email</h3>
                            <p>kuldeepvanani0@gmail.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h3>Address</h3>
                            <p>191 shree saivila residency, surat</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form">
                    <form id="contactForm" action="process/contact.php" method="POST">
                        <input type="text" name="name" placeholder="Your Name" required>
                        <input type="email" name="email" placeholder="Your Email" required>
                        <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>FoodHub</h3>
                    <p>Delivering happiness one meal at a time.</p>
                    <div class="social-links">
                        <a href="https://www.youtube.com/@website_coding"><i class="fab fa-youtube"></i></a>
                        <a href="https://www.instagram.com/website_coding/"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#menu">Menu</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-phone"></i> +91 7016688687</p>
                    <p><i class="fas fa-envelope"></i> kuldeepvanani0@gmail.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 191 shree saivila residency, surat</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 FoodHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Item Details Modal -->
    <div id="itemDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeItemDetails()">&times;</span>
            <div class="item-details">
                <div class="item-image">
                    <img id="detailItemImage" src="" alt="">
                </div>
                <div class="item-info">
                    <h2 id="detailItemName"></h2>
                    <p class="item-category" id="detailItemCategory"></p>
                    <p class="item-description" id="detailItemDescription"></p>
                    <div class="item-price">
                        <span class="price">₹<span id="detailItemPrice"></span></span>
                    </div>
                    <div class="quantity-selector">
                        <label>Quantity:</label>
                        <div class="quantity-controls">
                            <button onclick="changeQuantity(-1)">-</button>
                            <span id="quantityDisplay">1</span>
                            <button onclick="changeQuantity(1)">+</button>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="addToCartFromDetails()">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Modal -->
    <div id="cartModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Shopping Cart</h2>
            <div id="cartItems"></div>
            <div class="cart-total">
                <h3>Total: ₹<span id="cartTotal">0.00</span></h3>
            </div>
            <button id="checkoutBtn" class="btn btn-primary">Proceed to Checkout</button>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script>
        // Global variables for item details
        let currentItem = null;
        let currentQuantity = 1;

        // Show item details modal
        function showItemDetails(item) {
            currentItem = item;
            currentQuantity = 1;
            
            // Update modal content
            const imgEl = document.getElementById('detailItemImage');
            if (imgEl) {
                imgEl.style.display = 'none';
                imgEl.removeAttribute('src');
                imgEl.removeAttribute('alt');
                const ph = imgEl.parentElement ? imgEl.parentElement.querySelector('.modal-image-placeholder') : null;
                if (ph) ph.remove();
            }
            document.getElementById('detailItemName').textContent = item.name;
            document.getElementById('detailItemCategory').textContent = item.category_name;
            document.getElementById('detailItemDescription').textContent = item.description;
            document.getElementById('detailItemPrice').textContent = parseFloat(item.price).toFixed(2);
            document.getElementById('quantityDisplay').textContent = currentQuantity;
            
            // Show modal
            document.getElementById('itemDetailsModal').style.display = 'block';
        }

        // Close item details modal
        function closeItemDetails() {
            document.getElementById('itemDetailsModal').style.display = 'none';
            currentItem = null;
            currentQuantity = 1;
            
        }

        // Change quantity
        function changeQuantity(change) {
            currentQuantity = Math.max(1, currentQuantity + change);
            document.getElementById('quantityDisplay').textContent = currentQuantity;
        }

        // Add to cart from details modal
        function addToCartFromDetails() {
            if (currentItem) {
                addToCart(currentItem.id, currentItem.name, parseFloat(currentItem.price), currentQuantity);
                closeItemDetails();
            }
        }

        // Enhanced addToCart function to add to server-side session cart
        function addToCart(itemId, itemName, itemPrice, quantity = 1) {
            <?php if (!isset($_SESSION['user_id'])): ?>
            showNotification('Please register or login first to add items to cart!', 'warning');
            setTimeout(() => { window.location.href = 'register.php'; }, 1500);
            return;
            <?php endif; ?>

            fetch('process/cart_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=add_to_cart&item_id=${encodeURIComponent(itemId)}&item_name=${encodeURIComponent(itemName)}&item_price=${encodeURIComponent(itemPrice)}&quantity=${encodeURIComponent(quantity)}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.cart_count);
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message || 'Error adding item to cart', 'error');
                }
            })
            .catch(() => showNotification('Error adding item to cart', 'error'));
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#4CAF50' : type === 'warning' ? '#ff9800' : type==='error' ? '#f44336' : '#2196F3'};
                color: white;
                padding: 1rem 2rem;
                border-radius: 10px;
                z-index: 3000;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            `;
            
            document.body.appendChild(notification);
            // Trigger slide-in
            requestAnimationFrame(() => notification.classList.add('show'));
            
            // Auto-hide after 3s
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Update cart display
        function updateCartDisplay() {
            const cartCount = window.cart ? window.cart.reduce((total, item) => total + item.quantity, 0) : 0;
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = cartCount;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const itemModal = document.getElementById('itemDetailsModal');
            const cartModal = document.getElementById('cartModal');
            
            if (event.target === itemModal) {
                closeItemDetails();
            }
            if (event.target === cartModal) {
                cartModal.style.display = 'none';
            }
        }

        // Handle menu item image loading errors
        document.addEventListener('DOMContentLoaded', function() {
            const menuImages = document.querySelectorAll('.menu-item-image');
            
            menuImages.forEach(function(img) {
                img.addEventListener('error', function() {
                    // If the fallback image also fails, show a placeholder
                    if (this.src.includes('lassi.jpg')) {
                        this.style.display = 'none';
                        const placeholder = document.createElement('div');
                        placeholder.className = 'image-placeholder';
                        placeholder.innerHTML = '<i class="fas fa-utensils"></i>';
                        placeholder.style.cssText = `
                            width: 100%;
                            height: 200px;
                            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 3rem;
                            color: #6c757d;
                            border-radius: 8px 8px 0 0;
                        `;
                        this.parentNode.insertBefore(placeholder, this);
                    }
                });
            });
        });
    </script>
    <script>
        VANTA.BIRDS({
            el: "#home",
            mouseControls: true,
            touchControls: true,
            gyroControls: false,
            minHeight: 200.00,
            minWidth: 200.00,
            scale: 1.00,
            scaleMobile: 1.00
        })
    </script>
</body>
</html> 