<?php
// register.php - User Registration Page for Caerskie Foodhub
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Caerskie Foodhub - Register</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="video-bg">
        <video autoplay muted loop id="bg-video">
            <source src="assets/transition.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    <div class="container">
        <img src="assets/logo.png" alt="Caerskie Foodhub Logo" class="logo">
        <h2>Create Account</h2>
        <form method="post" action="register_process.php">
            <div class="form-group">
                <label for="reg-username">Username</label>
                <input type="text" id="reg-username" name="username" required>
            </div>
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" required>
            </div>
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required pattern="[0-9]{11,}">
            </div>
            <div class="form-group">
                <label for="reg-password">Password</label>
                <input type="password" id="reg-password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm_password" required>
            </div>
            <div class="form-group privacy">
                <input type="checkbox" id="privacy" name="privacy" required>
                <label for="privacy">
                    Upon Registering you agree to 
                    <a href="https://www.privacy.gov.ph/data-privacy-act/" target="_blank">Data Privacy Act of 2012</a>
                </label>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        <div class="bottom-links">
            <a href="index.php">Back to Login</a>
        </div>
    </div>
    <div id="transition-overlay" style="display: none;">
    <video id="transition-video" src="assets/transition.mp4" autoplay muted playsinline></video>
</div>

<script>
function playTransitionAndRedirect(url) {
    const overlay = document.getElementById('transition-overlay');
    const video = document.getElementById('transition-video');
    overlay.style.display = 'flex';
    video.currentTime = 0;
    video.play();

    video.onended = () => {
        window.location.href = url;
    };
}
</script>

</body>
</html>