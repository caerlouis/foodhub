<?php
// dashboard.php - Caerskie Foodhub User Dashboard
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Caerskie Foodhub - Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <a href="dashboard.php" class="logo-link">
                <img src="assets/logo.png" alt="Caerskie Foodhub Logo" class="header-logo">
            </a>
            <nav class="nav-links">
                <a href="dashboard.php" class="nav-item active">Menu</a>
                <a href="cart.php" class="nav-item">Cart</a>
                <a href="orders.php" class="nav-item">Orders</a>
                <a href="messages.php" class="nav-item">Message</a>
                <a href="logout.php" class="nav-item logout">Log-out</a>
            </nav>
        </div>
    </header>
    <main class="dashboard-main">
        <section class="menu-section">
            <h1>Our Menu</h1>
            <div class="food-list">
                <!-- Food product cards will go here -->
                <div class="food-card">
                    <img src="assets/sample-dish.jpg" alt="Sample Dish">
                    <div class="food-info">
                        <h3>Sample Food Item</h3>
                        <p>Delicious sample description for this food item.</p>
                        <span class="food-price">₱99.00</span>
                        <button class="add-cart-btn">Add to Cart</button>
                    </div>
                </div>
                <!-- Add more food-card divs for each product -->
            </div>
        </section>
    </main>
</body>
</html>