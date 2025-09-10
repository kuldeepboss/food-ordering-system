<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Ordering System - Admin Pages Overview</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .page-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .page-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            transition: transform 0.2s;
        }
        .page-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .page-card h3 {
            color: #007bff;
            margin-top: 0;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        .page-card p {
            color: #666;
            margin-bottom: 15px;
        }
        .page-card a {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .page-card a:hover {
            background: #0056b3;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .status-working {
            background: #d4edda;
            color: #155724;
        }
        .status-admin {
            background: #fff3cd;
            color: #856404;
        }
        .nav-links {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: #e9ecef;
            border-radius: 5px;
        }
        .nav-links a {
            display: inline-block;
            margin: 5px 10px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .nav-links a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🍕 Food Ordering System - Admin Pages Overview</h1>
        
        <div class="nav-links">
            <a href="index.php">🏠 Main Website</a>
            <a href="admin/">⚙️ Admin Panel</a>
            <a href="view_database.php">🗄️ Database Viewer</a>
            <a href="setup_database.php">🔧 Database Setup</a>
        </div>
        
        <div class="page-grid">
            <!-- Main Website -->
            <div class="page-card">
                <span class="status status-working">Customer Website</span>
                <h3>🏠 Main Website</h3>
                <p>Customer-facing food ordering website where customers can browse menu, add items to cart, and place orders.</p>
                <a href="index.php" target="_blank">Visit Website</a>
            </div>
            
            <!-- Admin Panel -->
            <div class="page-card">
                <span class="status status-admin">Admin Access Required</span>
                <h3>⚙️ Admin Panel</h3>
                <p>Administrative dashboard for managing orders, menu items, and system settings. Login required.</p>
                <a href="admin/" target="_blank">Access Admin Panel</a>
                <p><small>Username: admin | Password: admin123</small></p>
            </div>
            
            <!-- Database Viewer -->
            <div class="page-card">
                <span class="status status-working">Database Tool</span>
                <h3>🗄️ Database Viewer</h3>
                <p>Beautiful web interface to view all database tables, categories, menu items, orders, and messages.</p>
                <a href="view_database.php" target="_blank">View Database</a>
            </div>
            
            <!-- Database Setup -->
            <div class="page-card">
                <span class="status status-working">Setup Tool</span>
                <h3>🔧 Database Setup</h3>
                <p>Command-line tool to verify database connection, check tables, and display sample data.</p>
                <a href="setup_database.php" target="_blank">Run Setup Check</a>
            </div>
            
            <!-- Admin Database Viewer -->
            <div class="page-card">
                <span class="status status-admin">Admin Tool</span>
                <h3>📊 Admin Database Viewer</h3>
                <p>Integrated database viewer within the admin panel with full admin interface and navigation.</p>
                <a href="admin/database.php" target="_blank">Admin Database View</a>
                <p><small>Requires admin login</small></p>
            </div>
            
            <!-- phpMyAdmin -->
            <div class="page-card">
                <span class="status status-working">External Tool</span>
                <h3>🗄️ phpMyAdmin</h3>
                <p>Direct database management interface for advanced database operations and SQL queries.</p>
                <a href="http://localhost/phpmyadmin" target="_blank">Open phpMyAdmin</a>
            </div>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
            <h3>📋 Quick Access Guide</h3>
            <ul>
                <li><strong>For Customers:</strong> Use the Main Website to browse and order food</li>
                <li><strong>For Admins:</strong> Use Admin Panel to manage orders and menu items</li>
                <li><strong>For Database:</strong> Use Database Viewer or phpMyAdmin to view/edit data</li>
                <li><strong>For Setup:</strong> Use Database Setup to verify system configuration</li>
            </ul>
        </div>
        
        <div style="margin-top: 20px; padding: 20px; background: #e9ecef; border-radius: 5px;">
            <h3>🔧 System Status</h3>
            <p><strong>Database:</strong> ✅ Connected and working</p>
            <p><strong>XAMPP:</strong> ✅ Apache and MySQL services running</p>
            <p><strong>Admin Access:</strong> ✅ Username: admin, Password: admin123</p>
            <p><strong>Database Name:</strong> food_ordering_db</p>
        </div>
    </div>
</body>
</html> 