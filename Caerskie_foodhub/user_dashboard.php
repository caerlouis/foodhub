<?php
// user_dashboard.php - Caerskie Foodhub User Dashboard
session_start();
require_once 'db_connect.php'; // Needed to fetch menu from DB

$user = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : "Foodie";

// Fetch menu items from the database
$menu_items = [];
$result = $conn->query("SELECT * FROM food_products WHERE available = 1 ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Caerskie Foodhub - Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0; width: 100%; height: 100%;
            overflow: auto;
            background: rgba(0,0,0,0.76);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            margin: auto;
            display: block;
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 14px;
            box-shadow: 0 4px 32px #0008;
            background: #fff;
            animation: fadeIn .3s;
        }
        .modal-close {
            position: absolute;
            top: 38px;
            right: 60px;
            color: #fff;
            font-size: 38px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10001;
            transition: color 0.15s;
            text-shadow: 0 2px 16px #0009;
        }
        .modal-close:hover { color: #ffd37c; }
        @media (max-width: 650px) {
            .modal-content { max-width: 98vw; max-height: 70vh; }
            .modal-close { top: 20px; right: 20px; }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.94);}
            to   { opacity: 1; transform: scale(1);}
        }
    </style>
</head>
<div id="loader-overlay" style="display:none;">
  <video id="loader-video" src="assets/transition.mp4" autoplay muted playsinline style="width: 1500px;; height:auto; border-radius:14px; box-shadow:0 2px 16px #3333;">
    Sorry, your browser doesn't support embedded videos.
  </video>
</div>
<body>
    <header class="main-header">
        <div class="header-content">
            <a href="user_dashboard.php" class="logo-link">
                <img src="assets/logo.png" alt="Caerskie Foodhub Logo" class="header-logo">
            </a>
            <nav class="nav-links">
                <a href="user_dashboard.php" class="nav-item active">Menu</a>
                <a href="cart.php" class="nav-item">Cart</a>
                <a href="orders.php" class="nav-item">Orders</a>
                <a href="messages.php" class="nav-item">Messages</a>
                <a href="profile.php" class="nav-item">Profile</a>
                <a href="logout.php" class="nav-item logout">Log-out</a>
            </nav>
        </div>
    </header>
    <main class="dashboard-main">
        <!-- Welcome Section -->
        <section class="welcome-section" style="margin-bottom:32px;">
            <h1 style="font-size:2.2rem;color:var(--secondary);font-weight:800;">Welcome, <?php echo htmlspecialchars($user); ?>!</h1>
            <p style="font-size:1.15em;">Enjoy browsing our menu, check your orders, or manage your cart!</p>
        </section>
        
        <!-- Menu Section (Dynamic) -->
        <section class="menu-section">
            <h2 style="color:var(--secondary);margin-bottom:18px;">Menu</h2>
            <div class="food-list">
                <?php if (count($menu_items) === 0): ?>
                    <div style="color:#888;font-size:1.2em;margin:32px 0;">No menu items available right now.</div>
                <?php else: ?>
                    <?php foreach ($menu_items as $item): ?>
                        <div class="food-card">
                            <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>"
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 class="menu-img"
                                 style="cursor:zoom-in;"
                                 data-imgsrc="uploads/<?php echo htmlspecialchars($item['image']); ?>">
                            <div class="food-info">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                                <span class="food-price">₱<?php echo number_format($item['price'], 2); ?></span>
                                <form method="post" action="add_to_cart.php" style="margin-top:8px;">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$item['product_id']; ?>">
                                    <button type="submit" class="add-cart-btn">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        <!-- Notifications Section -->
        <section style="margin-top:38px;">
            <h2 style="color:var(--primary);margin-bottom:10px;">Notifications</h2>
            <ul style="padding-left:20px;">
                <li>Your recent order is being prepared!</li>
                <li>New: Try our latest menu items!</li>
                <li>Promo: Free delivery for orders above ₱500 this week!</li>
            </ul>
        </section>
    </main>

    <!-- Modal for enlarged image -->
    <div id="imgModal" class="modal">
        <span class="modal-close" id="modalClose">&times;</span>
        <img class="modal-content" id="modalImg" alt="Big Menu Item">
    </div>

    <script>
    // Modal functionality
    const modal = document.getElementById('imgModal');
    const modalImg = document.getElementById('modalImg');
    const modalClose = document.getElementById('modalClose');

    document.querySelectorAll('.menu-img').forEach(img => {
        img.addEventListener('click', function() {
            modal.style.display = "flex";
            modalImg.src = this.getAttribute('data-imgsrc');
            modalImg.alt = this.alt;
        });
    });

    modalClose.onclick = function() {
        modal.style.display = "none";
        modalImg.src = "";
    }
    // Close modal when clicking outside the image
    modal.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
            modalImg.src = "";
        }
    }
    </script>
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