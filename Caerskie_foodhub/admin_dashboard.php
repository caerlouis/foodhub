<?php
session_start();
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit();
}
$admin = $_SESSION['admin_username'];
require_once 'db_connect.php';

// Handle user deletion (with orders)
if (isset($_GET['remove_user']) && is_numeric($_GET['remove_user'])) {
    $remove_user_id = intval($_GET['remove_user']);
    // First, delete orders for that user
    $stmt_orders = $conn->prepare("DELETE FROM orders WHERE user_id=?");
    $stmt_orders->bind_param("i", $remove_user_id);
    $stmt_orders->execute();
    $stmt_orders->close();
    // Then, delete the user
    $stmt_user = $conn->prepare("DELETE FROM users WHERE user_id=?");
    $stmt_user->bind_param("i", $remove_user_id);
    $stmt_user->execute();
    $stmt_user->close();
    // Optionally: add notification for success
    $_SESSION['user_removal_msg'] = "User and their orders have been deleted.";
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Caerskie Foodhub - Admin Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .main-header {
            background: #fff;
            box-shadow: 0 1px 6px 0 rgba(31,38,135,.08);
            position: sticky;
            top: 0;
            z-index: 101;
            width: 100%;
        }
        .header-content {
            display: flex;
            align-items: center;
            max-width: 1150px;
            margin: 0 auto;
            padding: 16px 24px 10px 24px;
        }
        .header-logo {
            height: 36px;
            margin-right: 10px;
        }
        .logo-link {
            display: flex;
            align-items: center;
        }
        .nav-menu {
            margin-left: 24px;
            display: flex;
            gap: 18px;
        }
        .nav-menu a {
            color: #4e54c8;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.08em;
            padding: 6px 10px;
            border-radius: 7px;
            transition: background 0.15s;
        }
        .nav-menu a.active, .nav-menu a:hover {
            background: #e8eaf6;
            color: #43b77d;
        }
        .logout-btn {
            background: var(--secondary, #43b77d);
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 9px 18px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            margin-left: 18px;
            transition: background 0.18s;
        }
        .logout-btn:hover, .logout-btn:focus { background: var(--primary, #4e54c8); color: #222; }
        .admin-panel { max-width: 1150px; margin: 110px auto 0 auto; background: #fff; border-radius: 18px; box-shadow: var(--shadow); padding: 36px 32px 32px 32px; }
        .admin-panel h2 { color: var(--secondary); margin-bottom: 26px; font-size: 1.5em; }
        .menu-form { background: #f8f8f8; border-radius: 12px; padding: 26px 22px; margin-bottom: 36px; box-shadow: 0 2px 8px rgba(239,172,98,0.11); }
        .menu-form label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--primary); }
        .menu-form input[type="text"], .menu-form input[type="number"], .menu-form textarea { width: 100%; border: 1px solid var(--input-border); border-radius: 7px; padding: 10px; font-size: 1em; margin-bottom: 14px; background: var(--input-bg); color: var(--text); }
        .menu-form input[type="file"] { margin-bottom: 16px; }
        .menu-form textarea { resize: vertical; min-height: 70px; max-height: 200px; }
        .menu-form .btn { width: auto; padding: 11px 32px; }
        .menu-table, .users-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .menu-table th, .menu-table td, .users-table th, .users-table td { padding: 12px 10px; border-bottom: 1px solid #ececec; text-align: center; }
        .menu-table th, .users-table th { background: var(--primary); color: #fff; font-size: 1.08em; }
        .menu-table img { height: 52px; border-radius: 8px; }
        .remove-user-btn {
            background: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 7px 16px;
            font-size: 0.96em;
            font-weight: 600;
            cursor: pointer;
            margin-left: 4px;
            transition: background 0.14s;
        }
        .remove-user-btn:hover, .remove-user-btn:focus { background: #b32d1e; }
        @media (max-width: 1200px) { .admin-panel { max-width: 98vw; } }
        @media (max-width: 600px) { .admin-panel { padding: 16px 3vw 22px 3vw;} .logout-btn { width: 100%; margin: 12px 0 0 0;} }
        .users-table { margin-bottom: 36px; }
        .users-table td { font-size: 0.98em; }
        .user-removal-msg {
            background: #c8e6c9;
            color: #256029;
            border: 1px solid #43b77d;
            border-radius: 7px;
            padding: 13px 20px;
            margin-bottom: 18px;
            font-size: 1.07em;
            text-align: center;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <a href="admin_dashboard.php" class="logo-link">
                <img src="assets/logo.png" alt="Caerskie Foodhub Logo" class="header-logo">
            </a>
            <nav class="nav-menu">
                <a href="admin_dashboard.php" class="active">Menu</a>
                <a href="messages_admin.php">Messages</a>
                <form action="logout.php" method="post" style="margin:0;display:inline;">
                    <button type="submit" class="logout-btn">Log-out</button>
                </form>
            </nav>
        </div>
    </header>
    <main>
        <div class="admin-panel">

            <!-- USER REMOVAL MESSAGE -->
            <?php if (isset($_SESSION['user_removal_msg'])): ?>
                <div class="user-removal-msg"><?php echo $_SESSION['user_removal_msg']; unset($_SESSION['user_removal_msg']); ?></div>
            <?php endif; ?>

            <!-- USER INFORMATION TABLE SECTION -->
            <h2>Registered Users</h2>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Location</th>
                        <th>Phone</th>
                        <th>Registered At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $users = $conn->query("SELECT user_id, username, fullname, location, phone, created_at FROM users ORDER BY user_id ASC");
                if ($users && $users->num_rows > 0) {
                    while ($user = $users->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($user['user_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['fullname']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['location']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['phone']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
                        echo "<td>
                                <form method='get' action='admin_dashboard.php' onsubmit='return confirm(\"Are you sure you want to remove this user? All their orders will also be deleted.\");' style='margin:0;display:inline;'>
                                    <input type='hidden' name='remove_user' value='" . (int)$user['user_id'] . "'>
                                    <button type='submit' class='remove-user-btn'>Remove</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' style='color: #999;'>No users found.</td></tr>";
                }
                ?>
                </tbody>
            </table>

            <h2>Manage Menu Items</h2>
            <form class="menu-form" action="admin_menu_process.php" method="POST" enctype="multipart/form-data">
                <label for="product_name">Product Name</label>
                <input type="text" name="product_name" id="product_name" required maxlength="100">
                <label for="product_image">Product Image</label>
                <input type="file" name="product_image" id="product_image" accept="image/*" required>
                <label for="product_price">Product Price (₱)</label>
                <input type="number" step="0.01" min="0" name="product_price" id="product_price" required>
                <label for="product_details">Product Details</label>
                <textarea name="product_details" id="product_details" required maxlength="500"></textarea>
                <button type="submit" class="btn">Add Menu Item</button>
            </form>
            <h2 style="margin-top:36px;">Current Menu</h2>
            <table class="menu-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Price (₱)</th>
                        <th>Details</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $result = $conn->query("SELECT * FROM food_products ORDER BY created_at DESC");
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $img = htmlspecialchars($row['image']);
                        $name = htmlspecialchars($row['name']);
                        $price = number_format($row['price'], 2);
                        $desc = htmlspecialchars($row['description']);
                        $id = (int)$row['product_id'];
                        echo "<tr>
                            <td><img src='uploads/{$img}' alt='{$name}'></td>
                            <td>{$name}</td>
                            <td>{$price}</td>
                            <td>{$desc}</td>
                            <td>
                                <a href='admin_edit_menu.php?id={$id}' style='color:var(--primary);font-weight:600;'>Edit</a> |
                                <a href='admin_delete_menu.php?id={$id}' style='color:var(--secondary);font-weight:600;' onclick='return confirm(\"Are you sure?\");'>Delete</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='color: #999;'>No menu items found.</td></tr>";
                }
                ?>
                </tbody>
            </table>

            <!-- Orders management section -->
            <h2 style="margin-top:36px;">Manage Orders</h2>
            <table class="menu-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>User ID</th>
                        <th>Date</th>
                        <th>Total (₱)</th>
                        <th>Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $orders = $conn->query("SELECT * FROM orders ORDER BY order_date DESC");
                if ($orders && $orders->num_rows > 0) {
                    while ($order = $orders->fetch_assoc()) {
                        $oid = (int)$order['order_id'];
                        $uid = (int)$order['user_id'];
                        $date = htmlspecialchars($order['order_date']);
                        $total = number_format($order['total'], 2);
                        $status = htmlspecialchars($order['status']);
                        echo "<tr>
                            <td>{$oid}</td>
                            <td>{$uid}</td>
                            <td>{$date}</td>
                            <td>{$total}</td>
                            <td>{$status}</td>
                            <td>
                                <form method='post' action='admin_update_order_status.php' style='display:inline-flex;gap:6px;'>
                                    <input type='hidden' name='order_id' value='{$oid}'>
                                    <select name='new_status' required>
                                        <option value='Pending' ".($status=="Pending"?"selected":"").">Pending</option>
                                        <option value='Preparing' ".($status=="Preparing"?"selected":"").">Preparing</option>
                                        <option value='Out for Delivery' ".($status=="Out for Delivery"?"selected":"").">Out for Delivery</option>
                                        <option value='Delivered' ".($status=="Delivered"?"selected":"").">Delivered</option>
                                        <option value='Cancelled' ".($status=="Cancelled"?"selected":"").">Cancelled</option>
                                    </select>
                                    <button type='submit' class='btn' style='padding:3px 14px;font-size:0.96em;'>Update</button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='color: #999;'>No orders found.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>