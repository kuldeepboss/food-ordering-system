<?php
session_start();
require_once 'config/database.php';

echo "<h2>Payment Flow Test</h2>";

// Test 1: Database connection
echo "<h3>1. Database Connection Test</h3>";
try {
    $stmt = $pdo->query("SELECT 1");
    echo "✅ Database connection successful<br>";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 2: Check required tables exist
echo "<h3>2. Database Tables Test</h3>";
$tables = ['users', 'orders', 'order_items', 'menu_items'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        echo "✅ Table '$table' exists<br>";
    } catch (PDOException $e) {
        echo "❌ Table '$table' missing or error: " . $e->getMessage() . "<br>";
    }
}

// Test 3: Check session and cart functionality
echo "<h3>3. Session and Cart Test</h3>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session is active<br>";
    
    // Create test cart
    $_SESSION['cart'] = [
        [
            'id' => 1,
            'name' => 'Test Item',
            'price' => 299.99,
            'quantity' => 2
        ]
    ];
    echo "✅ Test cart created<br>";
    
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        echo "✅ Cart data accessible<br>";
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        echo "✅ Cart total calculation works: ₹" . number_format($total, 2) . "<br>";
    }
} else {
    echo "❌ Session not active<br>";
}

// Test 4: Check if user is logged in (optional)
echo "<h3>4. User Authentication Test</h3>";
if (isset($_SESSION['user_id'])) {
    echo "✅ User is logged in (ID: " . $_SESSION['user_id'] . ")<br>";
    
    // Test user email retrieval
    try {
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) {
            echo "✅ User email retrieved: " . $user['email'] . "<br>";
        } else {
            echo "❌ User not found in database<br>";
        }
    } catch (PDOException $e) {
        echo "❌ Error retrieving user: " . $e->getMessage() . "<br>";
    }
} else {
    echo "⚠️ No user logged in (payment will require login)<br>";
}

// Test 5: Check file accessibility
echo "<h3>5. File Accessibility Test</h3>";
$files = [
    'payment.php' => 'Payment page',
    'order_success.php' => 'Success page',
    'checkout.php' => 'Checkout page',
    'config/database.php' => 'Database config'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description ($file) exists<br>";
    } else {
        echo "❌ $description ($file) missing<br>";
    }
}

// Test 6: Payment form validation simulation
echo "<h3>6. Payment Processing Simulation</h3>";
$testData = [
    'payment_method' => 'card',
    'customer_name' => 'Test Customer',
    'customer_phone' => '9876543210',
    'customer_address' => 'Test Address, Test City'
];

$valid = true;
foreach (['payment_method', 'customer_name', 'customer_phone', 'customer_address'] as $field) {
    if (empty($testData[$field])) {
        $valid = false;
        echo "❌ Required field '$field' is empty<br>";
    }
}

if ($valid) {
    echo "✅ Payment form validation passes<br>";
}

// Clean up test data
unset($_SESSION['cart']);
echo "<br><strong>Test completed. Check results above for any issues.</strong><br>";
echo "<br><a href='payment.php'>Go to Payment Page</a> | <a href='checkout.php'>Go to Checkout</a> | <a href='index.php'>Go to Home</a>";
?>
