// Global variables
let cart = [];

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    setupEventListeners();
    setupAnimations();
    setupUserDropdown();
    setupScrollHeader();
}

// Setup user dropdown functionality
function setupUserDropdown() {
    const userMenu = document.querySelector('.user-menu');
    const userDropdown = document.querySelector('.user-dropdown');
    const userLink = userMenu ? userMenu.querySelector('.nav-link') : null;
    
    if (userMenu && userDropdown && userLink) {
        let isDropdownOpen = false;
        
        // Prevent default link behavior for user menu only
        userLink.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isDropdownOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                closeDropdown();
            }
        });
        
        // Prevent dropdown from closing when clicking inside it
        userDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        function openDropdown() {
            userDropdown.style.opacity = '1';
            userDropdown.style.visibility = 'visible';
            userDropdown.style.transform = 'translateY(0) rotateX(0deg) scale(1)';
            isDropdownOpen = true;
            userMenu.classList.add('active');
        }
        
        function closeDropdown() {
            userDropdown.style.opacity = '0';
            userDropdown.style.visibility = 'hidden';
            userDropdown.style.transform = 'translateY(-20px) rotateX(-15deg) scale(0.9)';
            isDropdownOpen = false;
            userMenu.classList.remove('active');
        }
        
        // Remove hover functionality - only use click
        userMenu.addEventListener('mouseenter', function() {
            // Do nothing - only respond to clicks
        });
        
        userMenu.addEventListener('mouseleave', function() {
            // Do nothing - only respond to clicks
        });
    }
    
    // Ensure other navigation links work normally
    const navLinks = document.querySelectorAll('.nav-link:not(.user-menu .nav-link)');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Allow normal navigation for all other links
            // Don't prevent default - let them navigate normally
        });
    });
}

// Add to cart functionality
function addToCart(itemId, itemName, itemPrice, itemImage = '') {
    fetch('process/cart_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add_to_cart&item_id=${itemId}&item_name=${encodeURIComponent(itemName)}&item_price=${itemPrice}&item_image=${encodeURIComponent(itemImage)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            showNotification(data.message);
            
            // Add animation to cart icon
            const cartIcon = document.querySelector('.cart-icon');
            if (cartIcon) {
                cartIcon.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    cartIcon.style.transform = 'scale(1)';
                }, 200);
            }
        } else {
            showNotification('Error adding item to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding item to cart');
    });
}

// Update cart count display
function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
    });
}

// Show notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 2000);
}

// Setup event listeners
function setupEventListeners() {
    // Mobile menu toggle
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    }

    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Menu filtering
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const category = button.getAttribute('data-category');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Filter menu items
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                if (category === 'all' || item.getAttribute('data-category') === category) {
                    item.style.display = 'block';
                    item.style.animation = 'fadeInUp 0.6s ease-out';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Cart modal
	const cartIcon = document.querySelector('.cart-icon');
    const cartModal = document.getElementById('cartModal');
    const closeModal = document.querySelector('.close');
    const checkoutBtn = document.getElementById('checkoutBtn');

	// Only intercept click to open modal when explicitly marked
	if (cartIcon && cartModal) {
		const href = (cartIcon.getAttribute('href') || '').trim();
		const shouldOpenModal = href === '#' || cartIcon.dataset.modal === 'true';
		if (shouldOpenModal) {
			cartIcon.addEventListener('click', (e) => {
				e.preventDefault();
				updateCartDisplay();
				cartModal.style.display = 'block';
			});
		}
	}
    
    if (closeModal && cartModal) {
        closeModal.addEventListener('click', () => {
            cartModal.style.display = 'none';
        });
    }
    
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            window.location.href = 'checkout.php';
        });
    }
    
    // Close modal when clicking outside
    if (cartModal) {
        window.addEventListener('click', (e) => {
            if (e.target === cartModal) {
                cartModal.style.display = 'none';
            }
        });
    }

    // Contact form
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            // Form will be submitted normally to PHP
            // Success/error messages will be handled by PHP redirects
        });
    }

    // Header scroll effect
    window.addEventListener('scroll', () => {
        const header = document.querySelector('.header');
        if (header) {
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = 'none';
            }
        }
    });

    // Show contact form messages if they exist
    showContactMessages();
}

// Show contact form messages
function showContactMessages() {
    // This will be handled by PHP session messages
    // The messages will be displayed in the PHP template
}

// Update cart display
function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    
    if (!cartItems || !cartTotal) return;

    // Fetch cart data from server
    fetch('process/get_cart.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCartItems(data.cart, cartItems, cartTotal);
            }
        })
        .catch(error => {
            console.error('Error fetching cart:', error);
        });
}

// Display cart items
function displayCartItems(cartItems, container, totalElement) {
    container.innerHTML = '';

    if (cartItems.length === 0) {
        container.innerHTML = '<p>Your cart is empty</p>';
        totalElement.textContent = '0.00';
        return;
    }

    let total = 0;

    cartItems.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;

        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        cartItem.innerHTML = `
            <div class="cart-item-info">
                <h4>${item.name}</h4>
                <p>₹${item.price.toFixed(2)} x ${item.quantity}</p>
            </div>
            <div class="cart-item-actions">
                <button onclick="updateCartQuantity(${item.id}, -1)">-</button>
                <span>${item.quantity}</span>
                <button onclick="updateCartQuantity(${item.id}, 1)">+</button>
                <button onclick="removeFromCart(${item.id})" class="remove-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(cartItem);
    });

    totalElement.textContent = total.toFixed(2);
}

// Update cart quantity
function updateCartQuantity(itemId, change) {
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
            updateCartCount(data.cart_count);
            updateCartDisplay();
        }
    });
}

// Remove from cart
function removeFromCart(itemId) {
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
            updateCartCount(data.cart_count);
            updateCartDisplay();
        }
    });
}

// Setup animations
function setupAnimations() {
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.feature-card, .menu-item, .contact-item').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // Counter animation for stats
    const stats = document.querySelectorAll('.stat h3');
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target;
                const finalValue = parseInt(target.textContent);
                animateCounter(target, 0, finalValue, 2000);
                statsObserver.unobserve(target);
            }
        });
    }, { threshold: 0.5 });

    stats.forEach(stat => statsObserver.observe(stat));
}

// Animate counter
function animateCounter(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        if (current >= end) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current) + (element.textContent.includes('+') ? '+' : '');
    }, 16);
}

// Setup scroll-based header background
function setupScrollHeader() {
    const header = document.querySelector('.header');
    
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
}

// Add CSS for notifications
const notificationStyles = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ff6b35;
        color: white;
        padding: 1rem 2rem;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        transform: translateX(100%);
        transition: transform 0.3s ease;
        z-index: 3000;
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #eee;
    }
    
    .cart-item-info h4 {
        margin: 0 0 0.5rem 0;
        color: #333;
    }
    
    .cart-item-info p {
        margin: 0;
        color: #666;
    }
    
    .cart-item-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .cart-item-actions button {
        width: 30px;
        height: 30px;
        border: 1px solid #ff6b35;
        background: white;
        color: #ff6b35;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .cart-item-actions button:hover {
        background: #ff6b35;
        color: white;
    }
    
    .remove-btn {
        background: #ff4444 !important;
        border-color: #ff4444 !important;
        color: white !important;
    }
    
    .remove-btn:hover {
        background: #cc0000 !important;
    }
`;

// Inject notification styles
const styleSheet = document.createElement('style');
styleSheet.textContent = notificationStyles;
document.head.appendChild(styleSheet); 