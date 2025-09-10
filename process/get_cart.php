<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $cart = $_SESSION['cart'] ?? [];
    $cartCount = array_sum(array_column($cart, 'quantity'));
    
    echo json_encode([
        'success' => true,
        'cart' => $cart,
        'cart_count' => $cartCount
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching cart data'
    ]);
}
?> 