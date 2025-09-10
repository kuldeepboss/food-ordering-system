<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

// Handle message actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                if (!empty($_POST['id'])) {
                    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                }
                break;
            case 'mark_read':
                if (!empty($_POST['id'])) {
                    $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                }
                break;
        }
        header('Location: messages.php');
        exit;
    }
}

// Get messages
$messages = [];
try {
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching messages: " . $e->getMessage();
}

// Get message statistics
$stats = [];
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_messages FROM contact_messages");
    $stats['total_messages'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as unread_messages FROM contact_messages WHERE is_read = 0 OR is_read IS NULL");
    $stats['unread_messages'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as read_messages FROM contact_messages WHERE is_read = 1");
    $stats['read_messages'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as today_messages FROM contact_messages WHERE DATE(created_at) = CURDATE()");
    $stats['today_messages'] = $stmt->fetchColumn();
} catch (PDOException $e) {
    $stats = ['total_messages' => 0, 'unread_messages' => 0, 'read_messages' => 0, 'today_messages' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages Management - FoodHub Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
        }
        .sidebar h2 {
            margin-bottom: 30px;
            color: #ecf0f1;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar li {
            margin-bottom: 10px;
        }
        .sidebar a {
            color: #bdc3c7;
            text-decoration: none;
            padding: 10px;
            display: block;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #34495e;
            color: white;
        }
        .main-content {
            flex: 1;
            padding: 30px;
            background: #f8f9fa;
        }
        .main-content h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card i {
            font-size: 2rem;
            color: #3498db;
            margin-bottom: 10px;
        }
        .stat-card h3 {
            margin: 10px 0;
            color: #2c3e50;
        }
        .stat-card p {
            font-size: 1.5rem;
            font-weight: bold;
            color: #27ae60;
            margin: 0;
        }
        .messages-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .message-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }
        .message-item:hover {
            background: #f8f9fa;
        }
        .message-item.unread {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .message-sender {
            font-weight: bold;
            color: #2c3e50;
        }
        .message-date {
            color: #666;
            font-size: 12px;
        }
        .message-email {
            color: #3498db;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .message-content {
            color: #333;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .message-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .logout-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #e74c3c;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .no-messages {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .unread-badge {
            background: #e74c3c;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 10px;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2><i class="fas fa-utensils"></i> FoodHub Admin</h2>
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu Management</a></li>
                <li><a href="database.php"><i class="fas fa-database"></i> Database Viewer</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="messages.php" class="active"><i class="fas fa-envelope"></i> Messages</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <a href="logout.php" class="logout-btn">Logout</a>
            
            <h1><i class="fas fa-envelope"></i> Messages Management</h1>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-envelope"></i>
                    <h3>Total Messages</h3>
                    <p><?php echo $stats['total_messages']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-envelope-open"></i>
                    <h3>Unread Messages</h3>
                    <p><?php echo $stats['unread_messages']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Read Messages</h3>
                    <p><?php echo $stats['read_messages']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-day"></i>
                    <h3>Today's Messages</h3>
                    <p><?php echo $stats['today_messages']; ?></p>
                </div>
            </div>
            
            <div class="messages-container">
                <h3><i class="fas fa-list"></i> Contact Messages</h3>
                
                <?php if (empty($messages)): ?>
                    <div class="no-messages">
                        <i class="fas fa-inbox" style="font-size: 3rem; color: #ddd; margin-bottom: 20px;"></i>
                        <p>No contact messages found. Messages from customers will appear here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <?php 
                        // Handle NULL values in is_read column
                        $isRead = isset($message['is_read']) ? $message['is_read'] : 0;
                        ?>
                        <div class="message-item <?php echo $isRead ? '' : 'unread'; ?>">
                            <div class="message-header">
                                <div>
                                    <span class="message-sender">
                                        <?php echo htmlspecialchars($message['name']); ?>
                                        <?php if (!$isRead): ?>
                                            <span class="unread-badge">NEW</span>
                                        <?php endif; ?>
                                    </span>
                                    <div class="message-email">
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($message['email']); ?>
                                    </div>
                                </div>
                                <div class="message-date">
                                    <i class="fas fa-clock"></i> <?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?>
                                </div>
                            </div>
                            
                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                            </div>
                            
                            <div class="message-actions">
                                <?php if (!$isRead): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check"></i> Mark as Read
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-primary">
                                    <i class="fas fa-reply"></i> Reply
                                </a>
                                
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 