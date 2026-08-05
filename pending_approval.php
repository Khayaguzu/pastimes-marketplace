<?php
/**
 * Pending Approval Page (pending_approval.php)
 * Shown to users whose accounts are pending admin verification
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

// Check user status
$user_id = $_SESSION['user_id'];
$sql = "SELECT status FROM tblUser WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// If already verified, redirect to shop
if ($user['status'] === 'verified') {
    header('Location: shop.php');
    exit;
}
?>

<style>
    .pending-container {
        max-width: 600px;
        margin: 80px auto;
        text-align: center;
        background: white;
        border-radius: 30px;
        padding: 50px;
        box-shadow: var(--shadow);
    }
    
    .pending-icon {
        width: 100px;
        height: 100px;
        background: #fff3cd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
    }
    
    .pending-icon i {
        font-size: 50px;
        color: #ffc107;
    }
    
    .pending-container h1 {
        color: var(--primary);
        margin-bottom: 15px;
        font-size: 28px;
    }
    
    .message {
        color: #666;
        line-height: 1.6;
        margin-bottom: 30px;
    }
    
    .highlight {
        background: #f0f2f5;
        padding: 15px;
        border-radius: 12px;
        margin: 20px 0;
        font-size: 14px;
        color: #555;
    }
    
    .btn-home {
        background: var(--primary);
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 40px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-home:hover {
        background: var(--accent);
    }
    
    .logout-link {
        display: block;
        margin-top: 20px;
        color: var(--accent);
        text-decoration: none;
    }
</style>

<div class="pending-container">
    <div class="pending-icon">
        <i class="fas fa-clock"></i>
    </div>
    <h1>Account Pending Approval</h1>
    <div class="message">
        <p>Thank you for registering with Pastimes!</p>
        <p>Your account is currently <strong>awaiting verification</strong> by our administrator.</p>
    </div>
    <div class="highlight">
        <i class="fas fa-info-circle"></i> What happens next?
        <p style="margin-top: 10px;">Our admin will review your account within 24 hours. Once verified, you'll receive an email notification and can start shopping immediately.</p>
    </div>
    <a href="index.php" class="btn-home">Return to Homepage</a>
    <a href="logout.php" class="logout-link">Logout</a>
</div>

<?php 
require_once 'includes/footer.php';
ob_end_flush();
?>