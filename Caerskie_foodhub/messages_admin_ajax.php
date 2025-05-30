<?php
session_start();
require_once 'db_connect.php';

// Admin-only
if (!isset($_SESSION['admin_username'])) exit();

// Get user id for this chat
$partner_id = isset($_GET['user']) ? intval($_GET['user']) : 0;
if (!$partner_id) exit;

// Fetch messages
$messages = [];
$stmt = $conn->prepare("SELECT * FROM messages WHERE (from_id=0 AND to_id=?) OR (from_id=? AND to_id=0) ORDER BY sent_at ASC");
$stmt->bind_param("ii", $partner_id, $partner_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $messages[] = $row;
$stmt->close();

// Output chat bubbles (HTML)
foreach ($messages as $msg) {
    $me = ($msg['from_id'] == 0);
    $row_class = $me ? "me" : "them";
    $bubble = htmlspecialchars($msg['message']);
    $meta = date('M j g:i a', strtotime($msg['sent_at']));
    echo "<div class='msg-row $row_class'><div class='msg-bubble'>$bubble</div></div>";
    echo "<div class='msg-row $row_class'><div class='msg-meta' style='margin-bottom:7px;'>$meta</div></div>";
}
?>