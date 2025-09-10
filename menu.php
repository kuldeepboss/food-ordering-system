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

    $menuItems = [];
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
    <title>Menu - FoodHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <li><a href="menu.php" class="nav-link active">Menu</a></li>
                <li><a href="index.php#about" class="nav-link">About</a></li>
                <li><a href="index.php#contact" class="nav-link">Contact</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="cart.php" class="nav-link cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo $cartCount; ?></span>
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

    <!-- Menu Section -->
    <section class="menu-section" style="margin-top: 80px; padding: 2rem 0;">
        <div class="container">
            <h1 class="section-title">Our Menu</h1>            
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
                        $cat = strtolower(str_replace(' ', '_', $item['category_name']));
                        $imgSrc = isset($item['image']) ? trim($item['image']) : '';
                    ?>
                    <div class="menu-item" data-category="<?php echo $cat; ?>" 
                         <?php if (isset($_SESSION['user_id'])): ?>
                             onclick="showItemDetails(<?php echo htmlspecialchars(json_encode($item)); ?>)"
                         <?php else: ?>
                             onclick="showNotification('Please register first to view details and add items to cart!', 'warning')"
                         <?php endif; ?>>
                        <?php if (!empty($imgSrc)): ?>
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                             class="menu-item-image"
                             onerror="this.onerror=null; this.style.display='none'; this.insertAdjacentHTML('afterend', '<div class=\'menu-item-image-placeholder\' style=\'width:100%;height:200px;background:linear-gradient(135deg,#f5f5f5,#e0e0e0);display:flex;align-items:center;justify-content:center;color:#666;font-size:1.2rem;font-weight:500;\'><i class=\'fas fa-utensils\' style=\'font-size:3rem;color:#ccc;margin-bottom:10px;\'></i><br><?php echo htmlspecialchars($item['name']); ?></div>');">
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
                                            onclick="event.stopPropagation(); showNotification('Please register first to view details and add items to cart!', 'warning'); setTimeout(function(){ window.location.href='register.php'; }, 1500);">
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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="index.php#about">About</a></li>
                        <li><a href="index.php#contact">Contact</a></li>
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

    <script src="js/script.js"></script>
    <script>
        // Global variables for item details
        let currentItem = null;
        let currentQuantity = 1;

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

        // Show item details modal
        function showItemDetails(item) {
            currentItem = item;
            currentQuantity = 1;
            
            // Update modal content
            const imgEl = document.getElementById('detailItemImage');
            if (imgEl) {
                imgEl.style.display = 'none';
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
            
            // Clear any modal placeholders
            const modalPlaceholders = document.querySelectorAll('.modal-image-placeholder');
            modalPlaceholders.forEach(placeholder => placeholder.remove());
            
            // Reset image display
            const modalImage = document.getElementById('detailItemImage');
            modalImage.style.display = 'block';
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

        // Enhanced addToCart function
        function addToCart(itemId, itemName, itemPrice, quantity = 1) {
            <?php if (!isset($_SESSION['user_id'])): ?>
            showNotification('Please register or login first to add items to cart!', 'warning');
            setTimeout(() => {
                window.location.href = 'register.php';
            }, 2000);
            return;
            <?php endif; ?>

            fetch('process/cart_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add_to_cart&item_id=${itemId}&item_name=${encodeURIComponent(itemName)}&item_price=${itemPrice}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.cart_count);
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error adding item to cart', 'error');
            });
        }

        // Update cart count display
        function updateCartCount(count) {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(element => {
                element.textContent = count;
            });
        }

        // Show notification (uses global styles from js/script.js)
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            // Keep basic look here; let CSS handle slide-in via .notification/.notification.show
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#4CAF50' : type === 'warning' ? '#ff9800' : type === 'error' ? '#f44336' : '#2196F3'};
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

        // Close modal when clicking outside
        window.onclick = function(event) {
            const itemModal = document.getElementById('itemDetailsModal');
            if (event.target === itemModal) {
                closeItemDetails();
            }
        }

        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.querySelector('.hamburger');
            const navMenu = document.querySelector('.nav-menu');
            
            if (hamburger && navMenu) {
                hamburger.addEventListener('click', () => {
                    navMenu.classList.toggle('active');
                    hamburger.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>
