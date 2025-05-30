<?php
session_start();
$error = $_SESSION['login_error'] ?? "";
unset($_SESSION['login_error']);

if (isset($_SESSION['fullname'])) {
    header("Location: user_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Caerskie Foodhub - Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <img src="assets/logo.png" alt="Caerskie Foodhub Logo" class="logo">
        <h2>User Login</h2>
        <?php if ($error): ?>
            <div class="error-msg" style="color: #d44; margin-bottom: 15px; text-align: center;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form method="post" action="login_process.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="bottom-links">
            <a href="register.php">Not yet Registered? Register here</a>
            <a href="admin_login.php">Log-in as Admin</a>
        </div>
    </div>
</body>
</html>