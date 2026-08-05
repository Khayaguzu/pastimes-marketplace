<?php
/**
 * Admin Communication Page
 * Admin can send messages to sellers and buyers
 */

session_start();
require_once 'DBConn.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$error = '';
$success = '';

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $to_user_id = (int)$_POST['to_user_id'];
    $subject = mysqli_real_escape_string($conn, trim($_POST['subject']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    $order_id = isset($_POST['order_id']) && $_POST['order_id'] > 0 ? (int)$_POST['order_id'] : 'NULL';
    
    if ($to_user_id > 0 && !empty($message)) {
        $sql = "INSERT INTO tblMessages (order_id, from_user_id, to_user_id, message, is_read, created_at) 
                VALUES ($order_id, 1, $to_user_id, '$message', 0, NOW())";
        
        if (mysqli_query($conn, $sql)) {
            $success = "Message sent successfully!";
        } else {
            $error = "Failed to send message: " . mysqli_error($conn);
        }
    } else {
        $error = "Please select a user and enter a message";
    }
}

// Get selected user's messages
$selected_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$selected_user = null;
$messages = [];

if ($selected_user_id > 0) {
    $user_query = "SELECT user_id, username, full_name, email, phone, address, status FROM tblUser WHERE user_id = $selected_user_id";
    $user_result = mysqli_query($conn, $user_query);
    $selected_user = mysqli_fetch_assoc($user_result);
    
    // Get messages between admin (user_id=1) and this user
    $msg_query = "SELECT m.*, 
                  CASE WHEN m.from_user_id = 1 THEN 'Admin' ELSE u.username END as sender_name
                  FROM tblMessages m
                  LEFT JOIN tblUser u ON m.from_user_id = u.user_id
                  WHERE (m.from_user_id = 1 AND m.to_user_id = $selected_user_id)
                     OR (m.from_user_id = $selected_user_id AND m.to_user_id = 1)
                  ORDER BY m.created_at ASC";
    $msg_result = mysqli_query($conn, $msg_query);
    while ($row = mysqli_fetch_assoc($msg_result)) {
        $messages[] = $row;
    }
}

// Get all users for dropdown (both sellers and buyers)
$users_query = "SELECT user_id, username, full_name, email, status, 
                (SELECT COUNT(*) FROM tblClothes WHERE seller_id = user_id) as product_count,
                (SELECT COUNT(*) FROM tblAorder WHERE buyer_id = user_id) as order_count
                FROM tblUser 
                WHERE user_id != 1 
                ORDER BY created_at DESC";
$users_result = mysqli_query($conn, $users_query);
$users = [];
while ($row = mysqli_fetch_assoc($users_result)) {
    $users[] = $row;
}

// Get recent orders for reference
$orders_query = "SELECT o.order_id, o.total_amount, o.order_status, o.order_date,
                 u.username, u.full_name
                 FROM tblAorder o
                 JOIN tblUser u ON o.buyer_id = u.user_id
                 ORDER BY o.order_date DESC LIMIT 20";
$orders_result = mysqli_query($conn, $orders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Communication - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        h1, h2 { color: #0a2a28; margin-bottom: 20px; }
        .communication-layout {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }
        .users-list {
            width: 300px;
            background: white;
            border-radius: 20px;
            padding: 20px;
            height: 600px;
            overflow-y: auto;
        }
        .chat-area {
            flex: 1;
            background: white;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            height: 600px;
        }
        .chat-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            background: #0a2a28;
            color: white;
            border-radius: 20px 20px 0 0;
        }
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #faf9f8;
        }
        .message-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            margin-bottom: 15px;
        }
        .message-admin {
            background: #0a2a28;
            color: white;
            align-self: flex-end;
            margin-left: auto;
        }
        .message-user {
            background: #e9ecef;
            color: #333;
            align-self: flex-start;
        }
        .message-time {
            font-size: 10px;
            margin-top: 5px;
            opacity: 0.7;
        }
        .chat-input {
            padding: 15px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }
        .chat-input input, .chat-input textarea {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 10px;
            resize: none;
        }
        .send-btn {
            background: #e57e5c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
        }
        .user-item {
            padding: 12px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background 0.2s;
            border-radius: 10px;
        }
        .user-item:hover {
            background: #f0f2f5;
        }
        .user-item.active {
            background: #e57e5c20;
            border-left: 3px solid #e57e5c;
        }
        .user-name {
            font-weight: 600;
            color: #0a2a28;
        }
        .user-email {
            font-size: 11px;
            color: #888;
        }
        .user-type {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            display: inline-block;
            margin-top: 5px;
        }
        .badge-seller {
            background: #d1e7dd;
            color: #0f5132;
        }
        .badge-buyer {
            background: #cfe2ff;
            color: #084298;
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .order-select {
            margin-bottom: 10px;
        }
        .order-select select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 15px;
        }
        .stats {
            display: flex;
            gap: 10px;
            margin-top: 8px;
            font-size: 11px;
            color: #666;
        }
        @media (max-width: 800px) {
            .communication-layout { flex-direction: column; }
            .users-list { width: 100%; height: 300px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><i class="fas fa-comments"></i> Admin Communication Center</h1>
            <p>Communicate with sellers and buyers about orders, deliveries, and item conditions</p>
            <a href="admin_dashboard.php" class="btn-back">← Back to Dashboard</a>
            
            <?php if ($success): ?>
                <div class="success-msg">✓ <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error-msg">⚠ <?php echo $error; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="communication-layout">
            <!-- Users List -->
            <div class="users-list">
                <h3><i class="fas fa-users"></i> Users</h3>
                <?php foreach ($users as $user): ?>
                    <div class="user-item <?php echo $selected_user_id == $user['user_id'] ? 'active' : ''; ?>"
                         onclick="window.location.href='admin_communication.php?user_id=<?php echo $user['user_id']; ?>'">
                        <div class="user-name">
                            <?php echo htmlspecialchars($user['full_name']); ?>
                            <span class="user-type <?php echo $user['product_count'] > 0 ? 'badge-seller' : 'badge-buyer'; ?>">
                                <?php echo $user['product_count'] > 0 ? 'Seller' : 'Buyer'; ?>
                            </span>
                        </div>
                        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                        <div class="stats">
                            <span><i class="fas fa-tag"></i> <?php echo $user['product_count']; ?> products</span>
                            <span><i class="fas fa-shopping-cart"></i> <?php echo $user['order_count']; ?> orders</span>
                            <span class="status-badge status-<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Chat Area -->
            <div class="chat-area">
                <?php if ($selected_user): ?>
                    <div class="chat-header">
                        <h3>
                            <i class="fas fa-user-circle"></i> 
                            <?php echo htmlspecialchars($selected_user['full_name']); ?>
                            <small style="font-size: 12px;">(@<?php echo htmlspecialchars($selected_user['username']); ?>)</small>
                        </h3>
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($selected_user['email']); ?></p>
                    </div>
                    
                    <div class="chat-messages" id="chatMessages">
                        <?php if (count($messages) > 0): ?>
                            <?php foreach ($messages as $msg): ?>
                                <div class="message-bubble <?php echo $msg['from_user_id'] == 1 ? 'message-admin' : 'message-user'; ?>">
                                    <strong><?php echo htmlspecialchars($msg['sender_name']); ?></strong>
                                    <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                    <div class="message-time">
                                        <?php echo date('d M Y H:i', strtotime($msg['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 50px; color: #999;">
                                <i class="fas fa-comments" style="font-size: 48px;"></i>
                                <p>No messages yet. Start a conversation with this user.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" action="" class="chat-input">
                        <input type="hidden" name="to_user_id" value="<?php echo $selected_user_id; ?>">
                        <div style="flex: 1;">
                            <div class="order-select">
                                <select name="order_id">
                                    <option value="0">-- Related to order (optional) --</option>
                                    <?php 
                                    $user_orders = mysqli_query($conn, "SELECT order_id, total_amount, order_status FROM tblAorder WHERE buyer_id = $selected_user_id ORDER BY order_date DESC");
                                    while ($order = mysqli_fetch_assoc($user_orders)):
                                    ?>
                                        <option value="<?php echo $order['order_id']; ?>">
                                            Order #<?php echo $order['order_id']; ?> - R<?php echo number_format($order['total_amount'], 2); ?> (<?php echo $order['order_status']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <input type="text" name="subject" placeholder="Subject (e.g., Order #123 - Delivery Update)" style="width: 100%; margin-bottom: 8px;">
                            <textarea name="message" rows="2" placeholder="Type your message here... Discuss delivery, item condition, shipping, etc." required></textarea>
                        </div>
                        <button type="submit" name="send_message" class="send-btn">
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
                    </form>
                <?php else: ?>
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #999;">
                        <div style="text-align: center;">
                            <i class="fas fa-comment-dots" style="font-size: 64px; margin-bottom: 20px;"></i>
                            <h3>Select a User to Start Chatting</h3>
                            <p>Choose a seller or buyer from the left panel to communicate about orders, deliveries, and item conditions.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions Section -->
        <div class="card">
            <h3><i class="fas fa-bullhorn"></i> Quick Communication Templates</h3>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button class="send-btn" style="background: #6c757d;" onclick="setMessage('Your order has been shipped and is on its way! Tracking number will be provided soon.')">
                    📦 Order Shipped
                </button>
                <button class="send-btn" style="background: #6c757d;" onclick="setMessage('We have verified the item condition. It looks good and will be delivered as described.')">
                    ✓ Item Verified
                </button>
                <button class="send-btn" style="background: #6c757d;" onclick="setMessage('Please provide additional photos of the item for verification before approval.')">
                    📸 Request Photos
                </button>
                <button class="send-btn" style="background: #6c757d;" onclick="setMessage('Your item listing has been approved and is now live in the shop!')">
                    ✅ Listing Approved
                </button>
                <button class="send-btn" style="background: #6c757d;" onclick="setMessage('Please confirm delivery of the item to proceed with payment release.')">
                    🚚 Confirm Delivery
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-scroll to bottom of chat
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function setMessage(message) {
            const messageInput = document.querySelector('textarea[name="message"]');
            if (messageInput) {
                messageInput.value = message;
                messageInput.focus();
            }
        }
        
        // Auto-refresh messages every 15 seconds
        let lastMessageCount = <?php echo count($messages); ?>;
        function checkNewMessages() {
            if (<?php echo $selected_user_id; ?> > 0) {
                fetch('check_admin_messages.php?user_id=<?php echo $selected_user_id; ?>&last_count=' + lastMessageCount)
                    .then(response => response.json())
                    .then(data => {
                        if (data.new_messages > 0) {
                            location.reload();
                        }
                    })
                    .catch(error => console.log('Error checking messages:', error));
            }
        }
        // setInterval(checkNewMessages, 15000);
    </script>
</body>
</html>