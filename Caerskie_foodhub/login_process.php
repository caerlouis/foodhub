<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? "");
    $password = trim($_POST['password'] ?? "");

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // SECURE: Use password_verify for hashed passwords!
        if (password_verify($password, $row['password'])) {
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['username'] = $row['username'];
            header("Location: user_dashboard.php");
            exit();
        }
    }
    // Login failed
    $_SESSION['login_error'] = "Invalid username or password.";
    header("Location: index.php");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>