# FoodHub - Online Food Ordering System

A modern, responsive food ordering website built with PHP, MySQL, and JavaScript.

## Features

### 🍽️ Core Features
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices
- **Menu Management**: Browse food items by categories with filtering
- **Shopping Cart**: Add items, adjust quantities, and manage your cart
- **Order Processing**: Complete checkout with delivery and payment options
- **Admin Dashboard**: Manage menu items, orders, and view statistics

### 🔐 Authentication System
- **User Registration**: Create new accounts with email verification
- **User Login**: Secure login with password hashing
- **User Profiles**: View and edit personal information
- **Order History**: Track all your previous orders
- **Session Management**: Secure session handling

### 👤 User Features
- **Profile Management**: Update personal information and change passwords
- **Order Tracking**: View order status and delivery information
- **Order History**: Complete history of all orders with details
- **Auto-fill Checkout**: Pre-filled forms for logged-in users

## Installation

1. **Setup Database**
   ```sql
   -- Import the database structure
   mysql -u your_username -p food_ordering_db < database/food_ordering_db.sql
   ```

2. **Configure Database Connection**
   - Edit `config/database.php` with your database credentials

3. **Setup Web Server**
   - Place files in your web server directory
   - Ensure PHP and MySQL are installed
   - Configure your web server to serve PHP files

## Demo Accounts

For testing purposes, the following demo accounts are available:

| Email | Password | Username |
|-------|----------|----------|
| john@example.com | password123 | john_doe |
| jane@example.com | password123 | jane_smith |
| mike@example.com | password123 | mike_johnson |

## File Structure

```
food-ordering-system/
├── admin/                 # Admin panel files
│   ├── index.php         # Admin dashboard
│   ├── menu.php          # Menu management
│   └── logout.php        # Admin logout
├── config/
│   └── database.php      # Database configuration
├── css/
│   └── style.css         # Main stylesheet
├── database/
│   └── food_ordering_db.sql  # Database structure
├── js/
│   └── script.js         # JavaScript functionality
├── process/              # Backend processing files
│   ├── cart_actions.php  # Cart operations
│   ├── contact.php       # Contact form processing
│   ├── get_cart.php      # Cart data API
│   └── place_order.php   # Order processing
├── index.php             # Main homepage
├── login.php             # User login page
├── register.php          # User registration page
├── logout.php            # User logout
├── profile.php           # User profile page
├── orders.php            # User order history
├── cart.php              # Shopping cart page
├── checkout.php          # Checkout page
└── order_success.php     # Order confirmation page
```

## Database Schema

### Users Table
- `id`: Primary key
- `username`: Unique username
- `email`: Unique email address
- `password`: Hashed password
- `first_name`, `last_name`: User's name
- `phone`, `address`: Contact information
- `created_at`, `updated_at`: Timestamps

### Orders Table
- `id`: Primary key
- `user_id`: Foreign key to users table (nullable for guest orders)
- `customer_name`, `customer_email`, `customer_phone`: Order details
- `delivery_address`: Delivery location
- `total_amount`: Order total
- `payment_method`: Payment type
- `order_status`: Order status (pending, confirmed, etc.)
- `order_date`: Order timestamp

## Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()`
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Prevention**: HTML escaping for all user input
- **Session Security**: Secure session handling and validation
- **Input Validation**: Server-side validation for all forms

## Admin Access

Admin panel is available at `/admin/` with these credentials:
- **Username**: admin
- **Password**: admin123

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome
- **Fonts**: Google Fonts (Poppins)

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

This project is open source and available under the MIT License.

## Support

For support or questions, please contact the development team. 