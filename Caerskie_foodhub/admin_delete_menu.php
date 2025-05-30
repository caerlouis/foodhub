<?php
session_start();
require_once 'db_connect.php';

// Check admin authentication
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit();
}

// Check for a valid product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Invalid request - redirect back
    header("Location: admin_dashboard.php");
    exit();
}

$product_id = intval($_GET['id']);

// Optionally, fetch image filename to delete the file as well
$stmt = $conn->prepare("SELECT image FROM food_products WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->bind_result($image_file);
$stmt->fetch();
$stmt->close();

// Delete the menu item
$stmt = $conn->prepare("DELETE FROM food_products WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->close();

// Delete the image file from uploads if it exists and is not empty
if (!empty($image_file)) {
    $image_path = __DIR__ . '/uploads/' . $image_file;
    if (file_exists($image_path)) {
        @unlink($image_path);
    }
}

// Optionally, you could set a session message here

header("Location: admin_dashboard.php");
exit();
?>