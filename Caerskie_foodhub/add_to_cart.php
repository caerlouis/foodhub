<?php
session_start();
require_once 'db_connect.php'; // Make sure this file connects to your DB in $conn

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Get product id from POST
if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);

    // Fetch product details from DB
    $stmt = $conn->prepare("SELECT product_id, name, price, image FROM food_products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if ($product) {
        // Initialize cart if not set
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        // If already in cart, increment quantity
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += 1;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1,
                'image' => $product['image']
            ];
        }
    }
}

header("Location: cart.php");
exit();
?>