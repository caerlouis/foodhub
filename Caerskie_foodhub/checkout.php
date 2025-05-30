<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Check if cart exists and is not empty
if (!isset($_SESSION['cart']) || empty(array_filter($_SESSION['cart'], 'is_array'))) {
    header("Location: cart.php");
    exit();
}

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    if (is_array($item) && isset($item['price'], $item['quantity'])) {
        $total += $item['price'] * $item['quantity'];
    }
}

// Handle order submission
$order_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    if ($user_id) {
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'Pending')");
        $stmt->bind_param("id", $user_id, $total);
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            $stmt->close();

            // Insert each cart item into order_items with product_id, quantity, price, name
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, name) VALUES (?, ?, ?, ?, ?)");
            foreach ($_SESSION['cart'] as $product_id => $item) {
                if (!is_array($item)) continue;
                $prod_name = isset($item['name']) ? $item['name'] : "Product #$product_id";
                $stmt->bind_param("iiids", $order_id, $product_id, $item['quantity'], $item['price'], $prod_name);
                $stmt->execute();
            }
            $stmt->close();

            $_SESSION['cart'] = [];
            $order_success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Caerskie Foodhub</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background: #f4f6fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, .15);
            padding: 32px 24px 24px 24px;
        }
        h2 { text-align: center; margin-bottom: 24px; }
        .checkout-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .checkout-table th, .checkout-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #eaeaea;
            text-align: center;
        }
        .checkout-table th { background: #eef2fb; }
        .checkout-total {
            font-size: 1.15em;
            font-weight: bold;
            text-align: right;
            margin-top: 18px;
        }
        .btn {
            padding: 9px 1px;
            border: none;
            border-radius: 6px;
            background: #43b77d;
            color: #fff;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover, .btn:focus { background: #369668; }
        .btn-secondary {
            background: #bbb;
            color: #222;
        }
        .btn-secondary:hover, .btn-secondary:focus { background: #999; }
        .success-message {
            text-align: center;
            color: #43b77d;
            font-size: 1.2em;
            margin-bottom: 16px;
        }
        @media (max-width: 600px) {
            .container { padding: 10px 2px; }
            .checkout-table th, .checkout-table td { padding: 7px 2px; font-size: 0.97em; }
            .btn { padding: 8px 12px; font-size: 0.97em; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Checkout</h2>

        <?php if ($order_success): ?>
            <div class="success-message">
                🎉 Thank you for your order!<br>Your order has been placed successfully.
            </div>
            <div style="text-align:center;">
                <a href="user_dashboard.php" class="btn btn-secondary">Back to Menu</a>
            </div>
        <?php else: ?>
            <form method="post" action="checkout.php">
                <table class="checkout-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($_SESSION['cart'] as $product_id => $item):
                        if (!is_array($item)) continue;
                        $prod_name = isset($item['name']) ? $item['name'] : "Product #$product_id";
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($prod_name); ?></td>
                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="checkout-total">
                    Total: ₱<?php echo number_format($total, 2); ?>
                </div>
                <div style="text-align:center; margin-top: 18px;">
                    <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                    <button type="submit" name="place_order" class="btn">Place Order</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>