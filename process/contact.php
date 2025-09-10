<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// Get form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$message = $_POST['message'] ?? '';

// Validate required fields
if (empty($name) || empty($email) || empty($message)) {
    $_SESSION['contact_error'] = 'Please fill in all required fields.';
    header('Location: ../index.php#contact');
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['contact_error'] = 'Please enter a valid email address.';
    header('Location: ../index.php#contact');
    exit;
}

try {
    // Insert contact message into database
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $message]);
    
    $_SESSION['contact_success'] = 'Thank you for your message! We will get back to you soon.';
    
} catch (PDOException $e) {
    $_SESSION['contact_error'] = 'An error occurred while sending your message. Please try again.';
}

header('Location: ../index.php#contact');
exit;
?> 