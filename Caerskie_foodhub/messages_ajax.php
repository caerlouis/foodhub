<?php
session_start();
require_once 'db_connect.php';

// Check session (user or admin)
$is_admin = isset($_SESSION['admin_username']);
if (!$is_admin && !isset($_SESSION['username'])) exit('');

// Find IDs
if ($is_admin) {
    $partner_id = isset($_GET['user']) ? intval($_GET['user']) : 0;
    if ($partner_id === 0) exit(""); // No user selected
} else {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username=?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $stmt->bind_result($sender_id);
    $stmt->fetch();
    $stmt->close();
    $partner_id = 0;
}

// Fetch messages (same as main page logic)
$messages = [];
if ($is_admin) {
    $stmt = $conn->prepare("SELECT * FROM messages WHERE (from_id=0 AND to_id=?) OR (from_id=? AND to_id=0) ORDER BY sent_at ASC");
    $stmt->bind_param("ii", $partner_id, $partner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $messages[] = $row;
    $stmt->close();
} else {
    $stmt = $conn->prepare("SELECT * FROM messages WHERE (from_id=? AND to_id=0) OR (from_id=0 AND to_id=?) ORDER BY sent_at ASC");
    $stmt->bind_param("ii", $sender_id, $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $messages[] = $row;
    $stmt->close();
}

// Output chat bubbles (HTML only)
foreach ($messages as $msg) {
    $me = ($is_admin && $msg['from_id']==0) || (!$is_admin && $msg['from_id']==$sender_id);
    $row_class = $me ? "me" : "them";
    $bubble = htmlspecialchars($msg['message']);
    $meta = date('M j g:i a', strtotime($msg['sent_at']));
    echo "<div class='msg-row $row_class'><div class='msg-bubble'>$bubble</div></div>";
    echo "<div class='msg-row $row_class'><div class='msg-meta' style='margin-bottom:7px;'>$meta</div></div>";
}
?>