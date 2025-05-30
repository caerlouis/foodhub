<?php
// --- Database connection (edit these variables as needed) ---
$host = "localhost";
$user = "root";
$pass = "";
$db = "caerskie_foodhub"; // <-- replace with your actual database name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Fetch the image filename for product 'lalay' ---
$image = "";
$stmt = $conn->prepare("SELECT image FROM food_products WHERE name = ?");
$product_name = "lalay";
$stmt->bind_param("s", $product_name);
$stmt->execute();
$stmt->bind_result($image);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Product Image from Database</title>
    <style>
        img { max-width: 300px; border: 2px solid #333; }
        .notfound { color: red; }
    </style>
</head>
<body>
    <h2>Product Image: lalay</h2>
<?php if ($image && file_exists(__DIR__ . "/uploads/$image")): ?>
    <p>Image found for <strong>lalay</strong>:</p>
    <img src="uploads/<?php echo htmlspecialchars($image); ?>" alt="lalay">
<?php elseif ($image): ?>
    <p class="notfound">Database entry found, but file <code>uploads/<?php echo htmlspecialchars($image); ?></code> does not exist.</p>
<?php else: ?>
    <p class="notfound">No image found for product named <strong>lalay</strong> in your database.</p>
<?php endif; ?>
</body>
</html>