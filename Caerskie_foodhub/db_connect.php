<?php
$host = "localhost";
$user = "root";
$pass = ""; // If you set a password for root, put it here
$db = "caerskie_foodhub";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>