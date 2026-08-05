<?php
/**
 * User Dashboard (dashboard.php)
 * Displays user account information
 */

ob_start();
session_start();
require_once 'DBConn.php';
require_once 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user info from database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM tblUser WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
?>

<style>
    .dashboard-container {
        max-width: 800px;
        margin: 50px auto;
    }
    
    .user-info-card {
        background: white;
        border-radius: 30px;
        padding: 40px;
        box-shadow: var(--shadow);
    }
    
    .user-info-card h3 {
        color: var(--primary);
        margin-bottom: 20px;
        font-size: 24px;
    }
    
    .user-info-card p {
        margin-bottom: 15px;
        padding: 10px;
        border-bottom: 1px solid var(--border);
    }
    
    .user-info-card strong {
        display: inline-block;
        width: 120px;
        color: #555;
    }
    
    .dashboard-actions {
        margin-top: 30px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
</style>

<div class="dashboard-container">
    <div class="user-info-card">
        <h3>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h3>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address'] ?? 'Not provided'); ?></p>
        <p><strong>Member since:</strong> <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
        <p><strong>Status:</strong> 
            <span class="status-badge status-<?php echo $user['status']; ?>">
                <?php echo ucfirst($user['status']); ?>
            </span>
        </p>
        
        <div class="dashboard-actions">
            <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
            <a href="order_history.php" class="btn btn-outline">View Orders</a>
            <a href="logout.php" class="btn btn-outline">Logout</a>
        </div>
    </div>
</div>

<?php 
require_once 'includes/footer.php';
ob_end_flush();
?>