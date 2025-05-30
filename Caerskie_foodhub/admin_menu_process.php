<?php
session_start();
// Debug: Log session status
error_log("SESSION at start: " . print_r($_SESSION, true));

if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit();
}

require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errors = [];

    $name = trim($_POST['product_name'] ?? '');
    $price = trim($_POST['product_price'] ?? '');
    $details = trim($_POST['product_details'] ?? '');

    if ($name === '') $errors[] = "Product name is required.";
    if ($price === '' || !is_numeric($price) || $price < 0) $errors[] = "Valid product price is required.";
    if ($details === '') $errors[] = "Product details are required.";

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $imgTmp = $_FILES['product_image']['tmp_name'];
        $imgName = basename($_FILES['product_image']['name']);
        $imgExt = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($imgExt, $allowed)) {
            $errors[] = "Invalid image format. Allowed: jpg, jpeg, png, gif, webp.";
        } else {
            $newImgName = uniqid('product_', true) . '.' . $imgExt;
            $uploadPath = __DIR__ . '/uploads/' . $newImgName;
            if (!move_uploaded_file($imgTmp, $uploadPath)) {
                $errors[] = "Failed to upload image.";
            }
        }
    } else {
        $errors[] = "Product image is required.";
    }

    if (!$errors) {
        $stmt = $conn->prepare("INSERT INTO food_products (name, description, price, image, available) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("ssds", $name, $details, $price, $newImgName);
        if ($stmt->execute()) {
            $stmt->close();
            // Debug: Log session before redirect
            error_log("SESSION before redirect: " . print_r($_SESSION, true));
            header("Location: admin_dashboard.php?success=1");
            exit();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Menu Item - Admin</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .container { max-width: 480px; margin-top: 120px; }
        .error-list { background: #fee; color: #b71c1c; border: 1px solid #f88; border-radius: 7px; padding: 15px 16px; margin-bottom: 18px;}
        .btn { width: 100%; }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="btn" style="margin-bottom:18px; background:var(--primary);color:#fff;">&larr; Back to Dashboard</a>
        <h2>Add Menu Item</h2>
        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <ul>
                    <?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form action="admin_menu_process.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product_name">Product Name</label>
                <input type="text" name="product_name" id="product_name" maxlength="100" value="<?=htmlspecialchars($name ?? '')?>" required>
            </div>
            <div class="form-group">
                <label for="product_image">Product Image</label>
                <input type="file" name="product_image" id="product_image" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="product_price">Product Price (₱)</label>
                <input type="number" step="0.01" min="0" name="product_price" id="product_price" value="<?=htmlspecialchars($price ?? '')?>" required>
            </div>
            <div class="form-group">
                <label for="product_details">Product Details</label>
                <textarea name="product_details" id="product_details" maxlength="500" required><?=htmlspecialchars($details ?? '')?></textarea>
            </div>
            <button type="submit" class="btn">Add Menu Item</button>
        </form>
    </div>
</body>
</html>