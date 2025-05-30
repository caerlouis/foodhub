<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['username']) && !isset($_SESSION['admin_username'])) {
    header("Location: index.php");
    exit();
}

$is_admin = isset($_SESSION['admin_username']);
$username = $is_admin ? $_SESSION['admin_username'] : $_SESSION['username'];
$user_type = $is_admin ? 'admin' : 'user';

// Get user/admin id and info
if ($is_admin) {
    $sender_id = 0;
    $users = $conn->query("SELECT user_id, username FROM users ORDER BY username");
    $active_user_id = isset($_GET['user']) ? intval($_GET['user']) : 0;
    if ($active_user_id > 0) {
        $partner_id = $active_user_id;
        $stmt = $conn->prepare("SELECT username FROM users WHERE user_id=?");
        $stmt->bind_param("i", $partner_id);
        $stmt->execute();
        $stmt->bind_result($partner_name);
        $stmt->fetch();
        $stmt->close();
    } else {
        $partner_id = 0;
        $partner_name = "";
    }
} else {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($sender_id);
    $stmt->fetch();
    $stmt->close();
    $partner_id = 0; // admin is 0
    $partner_name = "Admin";
}

// Handle sending new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && trim($_POST['message']) !== "") {
    $msg = trim($_POST['message']);
    $from_id = $is_admin ? 0 : $sender_id;
    $to_id = $is_admin ? $partner_id : 0;
    $stmt = $conn->prepare("INSERT INTO messages (from_id, to_id, message, sent_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $from_id, $to_id, $msg);
    $stmt->execute();
    $stmt->close();
    if (isset($_POST['ajax'])) {
        echo "sent";
        exit();
    }
    if ($is_admin) {
        header("Location: messages.php?user=" . $partner_id);
    } else {
        header("Location: messages.php");
    }
    exit();
}

// Fetch messages for display
$messages = [];
if ($is_admin) {
    if ($partner_id > 0) {
        $stmt = $conn->prepare("SELECT * FROM messages WHERE (from_id=0 AND to_id=?) OR (from_id=? AND to_id=0) ORDER BY sent_at ASC");
        $stmt->bind_param("ii", $partner_id, $partner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        $stmt->close();
    }
} else {
    $stmt = $conn->prepare("SELECT * FROM messages WHERE (from_id=? AND to_id=0) OR (from_id=0 AND to_id=?) ORDER BY sent_at ASC");
    $stmt->bind_param("ii", $sender_id, $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages - Caerskie Foodhub</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Use the same header as other pages */
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

        /* Messenger styles (as before) */
        .messenger-container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 32px 0 rgba(31,38,135,.15);
            display: flex;
            min-height: 600px;
        }
        .sidebar {
            width: 240px;
            background: #f5f7fa;
            border-right: 1px solid #ececec;
            border-radius: 12px 0 0 12px;
            padding: 0;
            overflow-y: auto;
        }
        .sidebar h3 {
            text-align: center;
            margin: 18px 0 10px 0;
            font-size: 1.18em;
            color: #4e54c8;
        }
        .user-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .user-list li {
            padding: 13px 18px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }
        .user-list li.active, .user-list li:hover {
            background: #e8eaf6;
            font-weight: bold;
            color: #4e54c8;
        }
        .main-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            border-radius: 0 12px 12px 0;
            overflow: hidden;
        }
        .chat-header {
            padding: 18px 24px;
            background: #f2f4fb;
            font-size: 1.2em;
            color: #333;
            border-bottom: 1px solid #ececec;
            font-weight: 600;
            letter-spacing: 0.02em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .chat-messages {
            flex: 1;
            padding: 32px 24px 10px 24px;
            overflow-y: auto;
            background: #f8fafd;
        }
        .msg-row {
            display: flex;
            margin-bottom: 12px;
        }
        .msg-row.me { justify-content: flex-end; }
        .msg-bubble {
            max-width: 70%;
            padding: 11px 16px;
            border-radius: 18px;
            font-size: 1.05em;
            background: #e9e8fd;
            color: #302e4c;
            margin-left: 5px;
            margin-right: 5px;
            position: relative;
            word-break: break-word;
        }
        .msg-row.me .msg-bubble {
            background: #43b77d;
            color: #fff;
            border-bottom-right-radius: 6px;
        }
        .msg-row.them .msg-bubble {
            background: #e9e8fd;
            color: #302e4c;
            border-bottom-left-radius: 6px;
        }
        .msg-meta {
            font-size: 0.87em;
            color: #999;
            margin-top: 2px;
            margin-left: 5px;
        }
        .msg-row.me .msg-meta { text-align: right; }
        .chat-input-bar {
            border-top: 1px solid #ececec;
            padding: 18px 20px;
            background: #fff;
            display: flex;
        }
        .chat-input-bar input[type="text"] {
            flex: 1;
            border: 1px solid #bbb;
            border-radius: 18px;
            padding: 10px 18px;
            font-size: 1em;
            margin-right: 12px;
            outline: none;
            transition: border 0.15s;
        }
        .chat-input-bar input[type="text"]:focus {
            border-color: #4e54c8;
        }
        .chat-input-bar button {
            background: #4e54c8;
            color: #fff;
            border: none;
            border-radius: 18px;
            padding: 10px 26px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s;
        }
        .chat-input-bar button:hover, .chat-input-bar button:focus {
            background: #43b77d;
        }
        @media (max-width: 900px) {
            .messenger-container { max-width: 99vw; }
            .sidebar { width: 140px; }
            .chat-header, .chat-messages, .chat-input-bar { padding-left: 8px; padding-right: 8px; }
            .header-content { max-width: 99vw; }
        }
        @media (max-width: 600px) {
            .messenger-container { flex-direction: column; min-height: 80vh; }
            .sidebar { display: none; }
            .main-panel { border-radius: 12px; }
            .header-content { padding: 10px 4vw 10px 4vw;}
        }
    </style>
    <script>
    function scrollChatBottom() {
        var chat = document.getElementById('chat-messages');
        if(chat) chat.scrollTop = chat.scrollHeight;
    }
    document.addEventListener("DOMContentLoaded", scrollChatBottom);

    function fetchMessages() {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "<?php 
            if ($is_admin) {
                echo 'messages_ajax.php?user=' . intval($partner_id);
            } else {
                echo 'messages_ajax.php';
            }
        ?>", true);
        xhr.onload = function() {
            if(xhr.status === 200) {
                let data = xhr.responseText;
                let chat = document.getElementById('chat-messages');
                if(chat && chat.innerHTML !== data) {
                    chat.innerHTML = data;
                    scrollChatBottom();
                }
            }
        };
        xhr.send();
    }
    setInterval(fetchMessages, 1800);

    function sendMsg(e) {
        e.preventDefault();
        var msg = document.getElementById("chat-input");
        if(msg.value.trim() === "") return false;
        var form = document.getElementById("chat-form");
        var xhr = new XMLHttpRequest();
        xhr.open("POST", form.action, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function(){
            if(xhr.status === 200 && xhr.responseText === "sent") {
                msg.value = "";
                fetchMessages();
            }
        };
        var params = "message=" + encodeURIComponent(msg.value) + "&ajax=1";
        <?php if ($is_admin): ?>
        params += "&user_id=<?php echo intval($partner_id); ?>";
        <?php endif; ?>
        xhr.send(params);
        return false;
    }
    </script>
</head>
<div id="loader-overlay" style="display:none;">
  <video id="loader-video" src="assets/transition.mp4" autoplay muted playsinline style="width: 1500px;; height:auto; border-radius:14px; box-shadow:0 2px 16px #3333;">
    Sorry, your browser doesn't support embedded videos.
  </video>
</div>
<body>
    <header class="main-header">
        <div class="header-content">
            <a href="<?php echo $is_admin ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>" class="logo-link">
                <img src="assets/logo.png" alt="Caerskie Foodhub Logo" class="header-logo">
            </a>
            <nav class="nav-menu">
                <?php if ($is_admin): ?>
                    <a href="admin_dashboard.php">Menu</a>
                    <a href="orders_admin.php">Orders</a>
                    <a href="messages.php" class="active">Messages</a>
                    <a href="profile.php">Profile</a>
                <?php else: ?>
                    <a href="user_dashboard.php">Menu</a>
                    <a href="cart.php">Cart</a>
                    <a href="orders.php">Orders</a>
                    <a href="messages.php" class="active">Messages</a>
                    <a href="profile.php">Profile</a>
                <?php endif; ?>
                <form action="logout.php" method="post" style="margin:0;display:inline;">
                    <button type="submit" class="logout-btn">Log-out</button>
                </form>
            </nav>
            <!-- No user name on the right! -->
        </div>
    </header>
    <div class="messenger-container">
        <?php if ($is_admin): ?>
        <div class="sidebar">
            <h3>Users</h3>
            <ul class="user-list">
            <?php
            if ($users && $users->num_rows > 0) {
                while ($row = $users->fetch_assoc()) {
                    $active = ($partner_id == $row['user_id']) ? 'active' : '';
                    echo "<li class='$active'><a style='display:block;color:inherit;text-decoration:none;' href='messages.php?user={$row['user_id']}'>" . htmlspecialchars($row['username']) . "</a></li>";
                }
            }
            ?>
            </ul>
        </div>
        <?php endif; ?>
        <div class="main-panel">
            <div class="chat-header">
                <?php if ($is_admin): ?>
                    <?php echo $partner_id > 0 ? "Chat with <span style='color:#43b77d'>" . htmlspecialchars($partner_name) . "</span>" : "Select a user"; ?>
                <?php else: ?>
                    Messaging <span style="color:#4e54c8;">Admin</span>
                <?php endif; ?>
            </div>
            <div class="chat-messages" id="chat-messages" style="min-height:350px;">
                <?php
                foreach ($messages as $msg) {
                    $me = ($is_admin && $msg['from_id']==0) || (!$is_admin && $msg['from_id']==$sender_id);
                    $row_class = $me ? "me" : "them";
                    $bubble = htmlspecialchars($msg['message']);
                    $meta = date('M j g:i a', strtotime($msg['sent_at']));
                    echo "<div class='msg-row $row_class'><div class='msg-bubble'>$bubble</div></div>";
                    echo "<div class='msg-row $row_class'><div class='msg-meta' style='margin-bottom:7px;'>$meta</div></div>";
                }
                ?>
            </div>
            <?php if (($is_admin && $partner_id > 0) || !$is_admin): ?>
            <form id="chat-form" method="POST" action="messages.php<?php echo $is_admin ? '?user=' . intval($partner_id) : ''; ?>" onsubmit="return sendMsg(event);">
                <div class="chat-input-bar">
                    <input type="text" id="chat-input" name="message" placeholder="Type a message..." autocomplete="off" required>
                    <button type="submit">Send</button>
                </div>
            </form>
            <?php endif; ?>
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