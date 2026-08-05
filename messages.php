<?php
session_start();
require_once 'DBConn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=please_login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $to_user_id = (int)$_POST['to_user_id'];
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    
    if (!empty($message) && $to_user_id > 0 && $to_user_id != $user_id) {
        $sql = "INSERT INTO tblMessages (from_user_id, to_user_id, message, product_id, created_at) 
                VALUES ('$user_id', '$to_user_id', '$message', " . ($product_id ? "'$product_id'" : "NULL") . ", NOW())";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: messages.php?user_id=$to_user_id&sent=1");
            exit;
        }
    }
}

// Get all users the current user has interacted with (including sellers from products)
$conversations = [];

// First, get users from messages table
$conv_query = "
    SELECT DISTINCT 
        u.user_id, 
        u.username, 
        u.full_name,
        (
            SELECT message FROM tblMessages 
            WHERE (from_user_id = u.user_id AND to_user_id = $user_id) 
               OR (from_user_id = $user_id AND to_user_id = u.user_id) 
            ORDER BY created_at DESC LIMIT 1
        ) as last_message,
        (
            SELECT created_at FROM tblMessages 
            WHERE (from_user_id = u.user_id AND to_user_id = $user_id) 
               OR (from_user_id = $user_id AND to_user_id = u.user_id) 
            ORDER BY created_at DESC LIMIT 1
        ) as last_time,
        (
            SELECT COUNT(*) FROM tblMessages 
            WHERE from_user_id = u.user_id AND to_user_id = $user_id AND is_read = 0
        ) as unread_count
    FROM tblUser u
    WHERE u.user_id IN (
        SELECT DISTINCT from_user_id FROM tblMessages WHERE to_user_id = $user_id
        UNION
        SELECT DISTINCT to_user_id FROM tblMessages WHERE from_user_id = $user_id
    )
    AND u.user_id != $user_id
    ORDER BY last_time DESC
";

$conv_result = mysqli_query($conn, $conv_query);
if ($conv_result && mysqli_num_rows($conv_result) > 0) {
    while ($row = mysqli_fetch_assoc($conv_result)) {
        $conversations[] = $row;
    }
}

// Also get sellers from products the user has viewed/interacted with
$seller_query = "
    SELECT DISTINCT 
        u.user_id, u.username, u.full_name,
        NULL as last_message,
        NULL as last_time,
        0 as unread_count
    FROM tblUser u
    JOIN tblClothes c ON c.seller_id = u.user_id
    WHERE c.status = 'approved'
    AND u.user_id != $user_id
    AND u.user_id NOT IN (SELECT user_id FROM (
        SELECT DISTINCT from_user_id as user_id FROM tblMessages WHERE to_user_id = $user_id
        UNION
        SELECT DISTINCT to_user_id as user_id FROM tblMessages WHERE from_user_id = $user_id
    ) as existing)
    LIMIT 10
";

$seller_result = mysqli_query($conn, $seller_query);
if ($seller_result && mysqli_num_rows($seller_result) > 0) {
    while ($row = mysqli_fetch_assoc($seller_result)) {
        $conversations[] = $row;
    }
}

// Get messages for selected conversation
$selected_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$messages = [];
$user_info = null;
$product_info = null;

if ($selected_user_id > 0 && $selected_user_id != $user_id) {
    // Mark messages as read
    mysqli_query($conn, "UPDATE tblMessages SET is_read = 1 WHERE from_user_id = $selected_user_id AND to_user_id = $user_id");
    
    // Get messages between users
    $msg_query = "
        SELECT * FROM tblMessages 
        WHERE (from_user_id = $user_id AND to_user_id = $selected_user_id) 
           OR (from_user_id = $selected_user_id AND to_user_id = $user_id)
        ORDER BY created_at ASC
    ";
    $msg_result = mysqli_query($conn, $msg_query);
    if ($msg_result && mysqli_num_rows($msg_result) > 0) {
        while ($row = mysqli_fetch_assoc($msg_result)) {
            $messages[] = $row;
        }
    }
    
    // Get user info for the conversation partner
    $user_query = "SELECT user_id, username, full_name, email FROM tblUser WHERE user_id = $selected_user_id";
    $user_result = mysqli_query($conn, $user_query);
    if ($user_result && mysqli_num_rows($user_result) > 0) {
        $user_info = mysqli_fetch_assoc($user_result);
    }
    
    // Get product info if product_id is provided
    if ($product_id > 0) {
        $prod_query = "SELECT clothes_id, name, price, image FROM tblClothes WHERE clothes_id = $product_id";
        $prod_result = mysqli_query($conn, $prod_query);
        if ($prod_result && mysqli_num_rows($prod_result) > 0) {
            $product_info = mysqli_fetch_assoc($prod_result);
        }
    }
}

include 'includes/header.php';
?>

<style>
    .messaging-container {
        display: flex;
        background: white;
        border-radius: 20px;
        overflow: hidden;
        height: 650px;
        margin: 30px 0;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        border: 1px solid #eee;
    }
    
    .conversations-list {
        width: 35%;
        border-right: 1px solid #eee;
        overflow-y: auto;
        background: #fcfaf7;
    }
    
    .conversation-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .conversation-item:hover {
        background: #f0eeeb;
    }
    
    .conversation-item.active {
        background: #fff;
        border-left: 4px solid #e57e5c;
    }
    
    .conversation-avatar {
        width: 50px;
        height: 50px;
        background: #e57e5c;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 18px;
    }
    
    .conversation-info {
        flex: 1;
    }
    
    .conversation-name {
        font-weight: 600;
        color: #0a2a28;
    }
    
    .conversation-last-msg {
        font-size: 12px;
        color: #888;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 180px;
    }
    
    .conversation-time {
        font-size: 11px;
        color: #aaa;
    }
    
    .unread-badge {
        background: #e57e5c;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
    }
    
    .chat-area {
        width: 65%;
        display: flex;
        flex-direction: column;
    }
    
    .chat-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        background: white;
    }
    
    .chat-header h3 {
        color: #0a2a28;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .product-reference {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 12px;
        margin-top: 10px;
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    .product-reference img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .product-reference .product-name {
        font-weight: 600;
        font-size: 14px;
    }
    
    .product-reference .product-price {
        font-size: 14px;
        color: #e57e5c;
    }
    
    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 15px;
        background: #faf9f8;
    }
    
    .message-bubble {
        max-width: 70%;
        padding: 12px 18px;
        border-radius: 20px;
        position: relative;
        word-wrap: break-word;
    }
    
    .message-sent {
        background: #0a2a28;
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 5px;
    }
    
    .message-received {
        background: #f0eeeb;
        color: #333;
        align-self: flex-start;
        border-bottom-left-radius: 5px;
    }
    
    .message-time {
        font-size: 10px;
        margin-top: 5px;
        opacity: 0.7;
    }
    
    .message-sent .message-time {
        text-align: right;
    }
    
    .chat-input-area {
        padding: 20px;
        border-top: 1px solid #eee;
        display: flex;
        gap: 10px;
        background: white;
    }
    
    .chat-input {
        flex: 1;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 30px;
        outline: none;
        font-size: 14px;
    }
    
    .chat-input:focus {
        border-color: #e57e5c;
    }
    
    .send-btn {
        background: #e57e5c;
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 30px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
    }
    
    .send-btn:hover {
        background: #c96a4a;
        transform: translateY(-2px);
    }
    
    .quick-messages {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }
    
    .quick-msg-btn {
        background: #f0eeeb;
        border: none;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .quick-msg-btn:hover {
        background: #e57e5c;
        color: white;
    }
    
    .no-conversation {
        text-align: center;
        padding: 60px;
        color: #999;
    }
    
    .empty-messages {
        text-align: center;
        padding: 60px;
        color: #999;
    }
    
    .success-message {
        background: #d4edda;
        color: #155724;
        padding: 10px 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        text-align: center;
    }
    
    @media (max-width: 768px) {
        .messaging-container {
            flex-direction: column;
            height: auto;
        }
        .conversations-list, .chat-area {
            width: 100%;
        }
        .conversations-list {
            max-height: 300px;
        }
        .chat-area {
            height: 500px;
        }
    }
</style>

<div class="container">
    <h1 style="margin: 30px 0 0;"><i class="fas fa-comment-dots"></i> Messages</h1>
    <p style="color: #666; margin-bottom: 20px;">Chat with sellers about products, ask questions, and negotiate prices</p>
    
    <?php if (isset($_GET['sent']) && $_GET['sent'] == 1): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i> Message sent successfully! The seller will respond shortly.
        </div>
    <?php endif; ?>
    
    <div class="messaging-container">
        <div class="conversations-list">
            <?php if (count($conversations) > 0): ?>
                <?php foreach ($conversations as $conv): ?>
                    <div class="conversation-item <?php echo $selected_user_id == $conv['user_id'] ? 'active' : ''; ?>" 
                         onclick="window.location.href='messages.php?user_id=<?php echo $conv['user_id']; ?>'">
                        <div class="conversation-avatar">
                            <?php echo strtoupper(substr($conv['username'], 0, 1)); ?>
                        </div>
                        <div class="conversation-info">
                            <div class="conversation-name"><?php echo htmlspecialchars($conv['full_name']); ?></div>
                            <div class="conversation-last-msg"><?php echo htmlspecialchars(substr($conv['last_message'] ?? 'Click to start conversation', 0, 50)); ?></div>
                        </div>
                        <div>
                            <?php if ($conv['last_time']): ?>
                                <div class="conversation-time"><?php echo date('H:i', strtotime($conv['last_time'])); ?></div>
                            <?php endif; ?>
                            <?php if ($conv['unread_count'] > 0): ?>
                                <div class="unread-badge"><?php echo $conv['unread_count']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-conversation">
                    <i class="fas fa-comments" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <p>No conversations yet.</p>
                    <p style="font-size: 12px;">Browse products and click "Message Seller" to ask questions!</p>
                    <p style="margin-top: 15px;">
                        <a href="shop.php" class="btn btn-primary" style="font-size: 12px; padding: 8px 20px;">Browse Products</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="chat-area">
            <?php if ($selected_user_id > 0 && $user_info): ?>
                <div class="chat-header">
                    <h3>
                        <i class="fas fa-user-circle"></i> 
                        <?php echo htmlspecialchars($user_info['full_name']); ?>
                    </h3>
                    <small style="color: #888;">@<?php echo htmlspecialchars($user_info['username']); ?></small>
                </div>
                
                <?php if ($product_info): ?>
                    <div class="product-reference">
                        <img src="<?php echo htmlspecialchars($product_info['image']); ?>" alt="Product">
                        <div>
                            <div class="product-name"><?php echo htmlspecialchars($product_info['name']); ?></div>
                            <div class="product-price">R<?php echo number_format($product_info['price'], 2); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="chat-messages" id="chatMessages">
                    <?php if (count($messages) > 0): ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message-bubble <?php echo $msg['from_user_id'] == $user_id ? 'message-sent' : 'message-received'; ?>">
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                <div class="message-time">
                                    <?php echo date('H:i, d M Y', strtotime($msg['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-messages">
                            <i class="fas fa-comment" style="font-size: 36px; margin-bottom: 15px;"></i>
                            <p>No messages yet. Start the conversation!</p>
                            <div class="quick-messages">
                                <button class="quick-msg-btn" onclick="setQuickMessage('Is this still available?')">❓ Is this still available?</button>
                                <button class="quick-msg-btn" onclick="setQuickMessage('Can you tell me more about the condition?')">📝 Tell me about condition</button>
                                <button class="quick-msg-btn" onclick="setQuickMessage('What is the size? Is it true to size?')">📏 Size question</button>
                                <button class="quick-msg-btn" onclick="setQuickMessage('Can you ship to my location?')">🚚 Shipping question</button>
                                <button class="quick-msg-btn" onclick="setQuickMessage('Is the price negotiable?')">💰 Price negotiation</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <form method="POST" action="messages.php?user_id=<?php echo $selected_user_id; ?>" class="chat-input-area" id="messageForm">
                    <input type="hidden" name="to_user_id" value="<?php echo $selected_user_id; ?>">
                    <input type="hidden" name="send_message" value="1">
                    <?php if ($product_id > 0): ?>
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <?php endif; ?>
                    <input type="text" name="message" id="messageInput" class="chat-input" placeholder="Type your message here... Ask about availability, condition, price, etc." required autocomplete="off">
                    <button type="submit" class="send-btn" id="sendBtn">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                </form>
                
                <script>
                    // Auto-scroll to bottom of chat
                    const chatMessages = document.getElementById('chatMessages');
                    if (chatMessages) {
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                    
                    // Focus on message input
                    const messageInput = document.getElementById('messageInput');
                    if (messageInput) {
                        messageInput.focus();
                    }
                    
                    function setQuickMessage(message) {
                        const messageInput = document.getElementById('messageInput');
                        if (messageInput) {
                            messageInput.value = message;
                            messageInput.focus();
                        }
                    }
                    
                    // Prevent empty messages
                    const messageForm = document.getElementById('messageForm');
                    if (messageForm) {
                        messageForm.addEventListener('submit', function(e) {
                            const messageInput = document.getElementById('messageInput');
                            if (!messageInput.value.trim()) {
                                e.preventDefault();
                                alert('Please enter a message before sending.');
                                return false;
                            }
                        });
                    }
                </script>
            <?php else: ?>
                <div class="no-conversation" style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
                    <i class="fas fa-inbox" style="font-size: 64px; margin-bottom: 20px;"></i>
                    <h3>Select a conversation</h3>
                    <p>Choose a seller from the left panel to start messaging</p>
                    <p style="margin-top: 15px; font-size: 13px;">
                        <i class="fas fa-info-circle"></i> 
                        You can also go to any product and click "Message Seller" to ask questions!
                    </p>
                    <p style="margin-top: 10px;">
                        <a href="shop.php" class="btn btn-primary" style="font-size: 12px; padding: 8px 20px;">Browse Products to Message Sellers</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Auto-refresh messages every 10 seconds
    let lastMessageCount = <?php echo count($messages); ?>;
    
    function checkNewMessages() {
        if (<?php echo $selected_user_id; ?> > 0) {
            fetch('check_messages.php?user_id=<?php echo $selected_user_id; ?>&last_count=' + lastMessageCount)
                .then(response => response.json())
                .then(data => {
                    if (data.new_messages > 0) {
                        location.reload();
                    }
                })
                .catch(error => console.log('Error checking messages:', error));
        }
    }
    
    // Check for new messages every 15 seconds
    // setInterval(checkNewMessages, 15000);
</script>

<?php require_once 'includes/footer.php'; ?>