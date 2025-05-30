<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Get user_id for current username
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

$orders = [];
$order_items = [];

if ($user_id) {
    // Fetch user's orders
    $stmt = $conn->prepare("
        SELECT order_id, order_date, total, status
        FROM orders
        WHERE user_id = ?
        ORDER BY order_date DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();

    // Fetch ordered items for each order, now including name
    if (!empty($orders)) {
        $order_ids = array_column($orders, 'order_id');
        $ids_placeholder = implode(',', array_fill(0, count($order_ids), '?'));
        $types = str_repeat('i', count($order_ids));
        // Get product name from order_items (assuming you have a 'name' column in order_items)
        $sql = "SELECT order_id, product_id, quantity, price, name FROM order_items WHERE order_id IN ($ids_placeholder)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$order_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $order_items[$row['order_id']][] = $row;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Orders - Caerskie Foodhub</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background: #f4f6fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 32px 0 rgba(31,38,135,.15);
            padding: 32px 24px 24px 24px;
        }
        h2 { text-align: center; margin-bottom: 24px; }
        .order-card {
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 28px;
            padding: 18px 15px 12px 15px;
            background: #fafbff;
            box-shadow: 0 2px 10px 0 rgba(31,38,135,.04);
        }
        .order-header {
            font-size: 1.07em;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .order-status {
            padding: 4px 14px;
            border-radius: 15px;
            font-size: 0.95em;
            background: #e3f8ed;
            color: #1d7a42;
            font-weight: 500;
            margin-left: 8px;
        }
        .order-status.Pending { background: #fff3cd; color: #b8860b;}
        .order-status.Preparing { background: #b3e5fc; color: #0277bd;}
        .order-status.Out\ for\ Delivery { background: #ffe0b2; color: #e65100;}
        .order-status.Delivered { background: #d4edda; color: #155724;}
        .order-status.Cancelled { background: #f8d7da; color: #721c24;}
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .order-table th, .order-table td {
            padding: 8px 4px;
            border-bottom: 1px solid #e0e0e0;
            text-align: center;
        }
        .order-table th { background: #f2f6ff; }
        .order-total {
            text-align: right;
            font-size: 1.1em;
            font-weight: bold;
            margin-top: 4px;
        }
        .no-orders {
            text-align: center;
            color: #888;
            font-size: 1.08em;
            margin-top: 80px;
        }
        .btn {
            padding: 8px 1px;
            border: none;
            border-radius: 6px;
            background: #4e54c8;
            color: #fff;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover, .btn:focus { background: #6367d6; }
        .btn-secondary {
            background: #bbb;
            color: #222;
        }
        .btn-secondary:hover, .btn-secondary:focus { background: #999; }
        @media (max-width: 700px) {
            .container { padding: 12px 4px; }
            .order-card { padding: 10px 3px 8px 3px; }
            .order-header { flex-direction: column; align-items: flex-start; font-size: 1em;}
            .order-table th, .order-table td { padding: 6px 2px; font-size: 0.97em; }
        }
    </style>
</head>
<div id="loader-overlay" style="display:none;">
  <video id="loader-video" src="assets/transition.mp4" autoplay muted playsinline style="width: 1500px;; height:auto; border-radius:14px; box-shadow:0 2px 16px #3333;">
    Sorry, your browser doesn't support embedded videos.
  </video>
</div>
<body>
    <div class="container">
        <h2>Your Orders</h2>
        <?php if (empty($orders)): ?>
            <div class="no-orders">
                You have not placed any orders yet.<br>
                <a href="user_dashboard.php" class="btn btn-secondary" style="margin-top:24px;">Back to Menu</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            Order #<?php echo htmlspecialchars($order['order_id']); ?>
                            <span style="color:#bbb; font-size:0.96em; font-weight:normal;">| <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></span>
                        </div>
                        <div>
                            <span class="order-status <?php echo htmlspecialchars($order['status']); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($order_items[$order['order_id']])): ?>
                            <?php foreach ($order_items[$order['order_id']] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="order-total">
                        Total: ₱<?php echo number_format($order['total'], 2); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <div style="text-align:center; margin-top:12px;">
                <a href="user_dashboard.php" class="btn btn-secondary">Back to Menu</a>
            </div>
        <?php endif; ?>
    </div>
    <script>
const loader = document.getElementById('loader-overlay');
const loaderVideo = document.getElementById('loader-video');

// Show loader on page load
loader.style.display = 'flex';
loaderVideo.currentTime = 0;
loaderVideo.play();

window.addEventListener('load', function() {
    setTimeout(function() {
        loader.style.opacity = '0';
        setTimeout(function(){
            loader.style.display = 'none';
            loader.style.opacity = '';
        }, 300);
    }, 1000); // Keep it visible for a short fade out
});

// Show loader on navigation
document.querySelectorAll('a').forEach(function(link){
    if(link.target === '' && link.getAttribute('href') && !link.getAttribute('href').startsWith('#') && !link.getAttribute('href').startsWith('javascript')) {
        link.addEventListener('click', function(e){
            loader.style.display = 'flex';
            loader.style.opacity = '1';
            loaderVideo.currentTime = 0;
            loaderVideo.play();
        });
    }
});
document.querySelectorAll('form').forEach(function(form){
    form.addEventListener('submit', function(){
        loader.style.display = 'flex';
        loader.style.opacity = '1';
        loaderVideo.currentTime = 0;
        loaderVideo.play();
    });
});
</script>
</body>
</html>