<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// Validate cart
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: ../index.php');
    exit;
}

// Get form data
$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$zipCode = $_POST['zipCode'] ?? '';
$paymentMethod = $_POST['paymentMethod'] ?? 'cash';
$instructions = $_POST['instructions'] ?? '';

// Validate required fields
if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($zipCode)) {
    $_SESSION['error'] = 'Please fill in all required fields.';
    header('Location: ../checkout.php');
    exit;
}

try {
    // Calculate totals
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $deliveryFee = 120;
    $total = $subtotal + $deliveryFee;
    
    // Combine address
    $fullAddress = $address . ', ' . $city . ', ' . $zipCode;
    
    // Insert order into database
    $customerName = $firstName . ' ' . $lastName;
    
    // Check if user is logged in
    $userId = null;
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, delivery_address, total_amount, payment_method, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$userId, $customerName, $email, $phone, $fullAddress, $total, $paymentMethod]);
    
    $orderId = $pdo->lastInsertId();
    
    // Insert order items
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($_SESSION['cart'] as $item) {
        $stmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
    }
    
    // Store order details in session for success page
    $_SESSION['order_success'] = [
        'order_id' => $orderId,
        'customer_name' => $customerName,
        'total' => $total,
        'payment_method' => $paymentMethod,
        'delivery_address' => $fullAddress,
        'phone' => $phone
    ];
    
    // Clear cart
    unset($_SESSION['cart']);
    
    // Redirect to success page
    header('Location: ../order_success.php');
    exit;
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'An error occurred while processing your order. Please try again.';
    header('Location: ../checkout.php');
    exit;
}
?> 