<?php
session_start();
require_once 'db_connect.php';

// Require user login (not admin)
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user info
$stmt = $conn->prepare("SELECT user_id, fullname, location, phone FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id, $fullname, $location, $phone);
$stmt->fetch();
$stmt->close();

// Fetch order count
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($order_count);
$stmt->fetch();
$stmt->close();

// Determine whether user is editing profile
$editing = (isset($_GET['edit']) && $_GET['edit'] == '1');

// Handle profile update
$update_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $new_fullname = trim($_POST['fullname']);
    $new_location = trim($_POST['location']);
    $new_phone = trim($_POST['phone']);

    $stmt = $conn->prepare("UPDATE users SET fullname=?, location=?, phone=? WHERE user_id=?");
    $stmt->bind_param("sssi", $new_fullname, $new_location, $new_phone, $user_id);
    $update_success = $stmt->execute();
    $stmt->close();

    // Update values for display
    $fullname = $new_fullname;
    $location = $new_location;
    $phone = $new_phone;
    $editing = false; // Go back to view mode after saving
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - Caerskie Foodhub</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        .profile-wrapper {
            max-width: 550px;
            margin: 100px auto 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px 0 rgba(31,38,135,.10);
            padding: 36px 28px 30px 28px;
        }
        .profile-flex {
            display: flex;
            align-items: center;
            gap: 28px;
        }
        .profile-logo {
            height: 96px;
            width: 96px;
            border-radius: 50%;
            background: #f5f7fa;
            object-fit: contain;
            border: 2px solid #e8eaf6;
            box-shadow: 0 2px 8px #e9e8fd33;
        }
        .profile-info-form {
            flex: 1;
        }
        .profile-label {
            color: #4e54c8;
            font-weight: 600;
            margin-right: 5px;
            font-size: 1.2em;
        }
        .profile-count {
            background: #43b77d;
            color: #fff;
            padding: 3px 14px;
            border-radius: 99px;
            font-size: 1.12em;
            font-weight: 600;
            margin-left: 8px;
            display: inline-block;
        }
        .profile-success {
            color: #43b77d;
            text-align: center;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .profile-info-form input[type="text"] {
            width: 100%;
            padding: 11px 14px;
            border-radius: 7px;
            border: 1px solid #cfcfcf;
            font-size: 1.22em;
            margin-bottom: 14px;
        }
        .profile-info-form .btn, .edit-profile-btn {
            padding: 10px 30px;
            border-radius: 7px;
            background: #43b77d;
            color: #fff;
            border: none;
            font-size: 1.15em;
            font-weight: 600;
            cursor: pointer;
            margin-top: 14px;
            transition: background 0.17s;
            display: inline-block;
            text-decoration: none;
        }
        .profile-info-form .btn:hover, .profile-info-form .btn:focus,
        .edit-profile-btn:hover, .edit-profile-btn:focus { background: #4e54c8; }
        .greetings {
            margin-top: 38px;
            text-align: center;
            font-size: 1.18em;
            color: #43b77d;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .profile-field {
            font-size: 1.35em;
            margin-bottom: 12px;
        }
        @media (max-width: 600px) {
            .profile-wrapper { padding: 14px 3vw 18px 3vw; }
            .profile-flex { flex-direction: column; gap: 10px; text-align: center; }
            .profile-info-form { width: 100%; }
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
            <nav class="nav-menu">
                <a href="user_dashboard.php">Menu</a>
                <a href="cart.php">Cart</a>
                <a href="orders.php">Orders</a>
                <a href="messages.php">Messages</a>
                <a href="profile.php" class="active">Profile</a>
                <form action="logout.php" method="post" style="margin:0;display:inline;">
                    <button type="submit" class="logout-btn">Log-out</button>
                </form>
            </nav>
        </div>
    </header>
    <div class="profile-wrapper">
        <div class="profile-flex">
            <img src="assets/logo.png" class="profile-logo" alt="Caerskie Foodhub Logo">
            <div style="flex:1;">
                <?php if ($update_success): ?>
                    <div class="profile-success">Profile updated successfully!</div>
                <?php endif; ?>
                <?php if ($editing): ?>
                    <form method="post" class="profile-info-form" autocomplete="off">
                        <div>
                            <span class="profile-label">Full Name:</span>
                            <input type="text" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                        </div>
                        <div>
                            <span class="profile-label">Location:</span>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>">
                        </div>
                        <div>
                            <span class="profile-label">Phone:</span>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                        </div>
                        <div style="margin-top: 8px;">
                            <span class="profile-label">Orders Made:</span>
                            <span class="profile-count"><?php echo (int)$order_count; ?></span>
                        </div>
                        <button type="submit" name="save_profile" class="btn">Save Changes</button>
                        <a href="profile.php" class="edit-profile-btn" style="background:#999;margin-left:10px;">Cancel</a>
                    </form>
                <?php else: ?>
                    <div class="profile-field">
                        <span class="profile-label">Full Name:</span>
                        <?php echo htmlspecialchars($fullname); ?>
                    </div>
                    <div class="profile-field">
                        <span class="profile-label">Location:</span>
                        <?php echo htmlspecialchars($location); ?>
                    </div>
                    <div class="profile-field">
                        <span class="profile-label">Phone:</span>
                        <?php echo htmlspecialchars($phone); ?>
                    </div>
                    <div class="profile-field" style="margin-top: 8px;">
                        <span class="profile-label">Orders Made:</span>
                        <span class="profile-count"><?php echo (int)$order_count; ?></span>
                    </div>
                    <a href="profile.php?edit=1" class="edit-profile-btn" style="margin-top:16px;">Edit Profile</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="greetings">
            Thank you, <?php echo htmlspecialchars($fullname); ?>, for being a valued customer of Caerskie Foodhub!<br>
            We appreciate your trust and support in our service.
        </div>
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