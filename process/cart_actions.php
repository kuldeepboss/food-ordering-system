<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add_to_cart':
        handleAddToCart();
        break;
    case 'update_quantity':
        handleUpdateQuantity();
        break;
    case 'remove_item':
        handleRemoveItem();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function handleAddToCart() {
    $itemId = $_POST['item_id'] ?? 0;
    $itemName = $_POST['item_name'] ?? '';
    $itemPrice = $_POST['item_price'] ?? 0;
    $itemImage = $_POST['item_image'] ?? '';
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$itemId || !$itemName || !$itemPrice) {
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
        return;
    }
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if item already exists in cart
    $itemExists = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $itemId) {
            $item['quantity'] += $quantity;
            $itemExists = true;
            break;
        }
    }
    
    // Add new item if not exists
    if (!$itemExists) {
        $_SESSION['cart'][] = [
            'id' => $itemId,
            'name' => $itemName,
            'price' => $itemPrice,
            'image' => $itemImage,
            'quantity' => $quantity
        ];
    }
    
    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
    
    echo json_encode([
        'success' => true,
        'message' => "$itemName added to cart!",
        'cart_count' => $cartCount
    ]);
}

function handleUpdateQuantity() {
    $itemId = $_POST['item_id'] ?? 0;
    $change = $_POST['change'] ?? 0;
    
    if (!$itemId) {
        echo json_encode(['success' => false, 'message' => 'Missing item ID']);
        return;
    }
    
    if (!isset($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        return;
    }
    
    // Find and update item quantity
    foreach ($_SESSION['cart'] as $key => &$item) {
        if ($item['id'] == $itemId) {
            $item['quantity'] += $change;
            
            // Remove item if quantity becomes 0 or less
            if ($item['quantity'] <= 0) {
                unset($_SESSION['cart'][$key]);
            }
            break;
        }
    }
    
    // Reindex array
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    
    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
    
    echo json_encode([
        'success' => true,
        'cart_count' => $cartCount
    ]);
}

function handleRemoveItem() {
    $itemId = $_POST['item_id'] ?? 0;
    
    if (!$itemId) {
        echo json_encode(['success' => false, 'message' => 'Missing item ID']);
        return;
    }
    
    if (!isset($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        return;
    }
    
    // Remove item from cart
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $itemId) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    
    // Reindex array
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    
    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
    
    echo json_encode([
        'success' => true,
        'cart_count' => $cartCount
    ]);
}
?> 