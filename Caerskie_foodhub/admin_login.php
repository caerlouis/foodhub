<?php
session_start();
require_once 'db_connect.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? "");
    $password = trim($_POST['password'] ?? "");

    // Simple hardcoded admin credentials (for demonstration)
    // In production, use a hashed password and store credentials securely in the DB!
    $admin_username = "admin";
    $admin_password = "pass"; // Change this to your desired admin password

    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_username'] = $username;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid login credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Caerskie Foodhub</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background: #fffaef; }
        .login-container {
            max-width: 350px;
            margin: 120px auto 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px #f9e0b066;
            padding: 36px 45px 28px 28px;
        }
        .login-container h2 { color: var(--primary); margin-bottom: 28px; }
        .form-group { margin-bottom: 20px; }
        label { font-weight: 600; color: var(--secondary); }
        input[type="text"], input[type="password"] {
            width: 100%;
            border: 1px solid #e7b77c;
            border-radius: 7px;
            padding: 10px;
            margin-top: 7px;
            background: #fffaf1;
            color: var(--text);
        }
        .btn { width: 100%; }
        .error-msg {
            background: #fee;
            color: #b71c1c;
            border: 1px solid #f88;
            border-radius: 7px;
            padding: 9px 10px;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="admin_login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn">Log In</button>
        </form>
    </div>
</body>
</html>