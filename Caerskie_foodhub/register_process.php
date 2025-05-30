<?php
// register_process.php - Processes registration from register.php

require_once 'db_connect.php'; // <-- update with your DB connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $location = trim($_POST['location']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $privacy = isset($_POST['privacy']);

    // Basic validation
    $errors = [];
    if (!$username || !$fullname || !$location || !$phone || !$password || !$confirm_password) {
        $errors[] = "All fields are required.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (!$privacy) {
        $errors[] = "You must agree to the Data Privacy Act of 2012.";
    }

    // Optional: validate phone number format
    if (!preg_match('/^\d{11,}$/', $phone)) {
        $errors[] = "Phone number must be at least 11 digits.";
    }

    // Check if username exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Username already taken.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, fullname, location, phone, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $fullname, $location, $phone, $hashed_password);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?registered=1");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
} else {
    $errors[] = "Invalid request.";
}

// If errors, show them
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Error - Caerskie Foodhub</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <img src="assets/logo.png" alt="Caerskie Foodhub Logo" class="logo">
        <h2>Registration Error</h2>
        <?php
        if (!empty($errors)) {
            echo '<div class="form-group">';
            foreach ($errors as $error) {
                echo "<p style='color:var(--secondary); font-weight:bold;'>$error</p>";
            }
            echo '</div>';
        }
        ?>
        <a class="btn" href="register.php">Back to Registration</a>
    </div>
</body>
</html>