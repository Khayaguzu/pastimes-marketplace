<?php
/**
 * Admin Dashboard
 * Filename: admin_dashboard.php
 * Purpose: Complete admin control panel for managing users, items, and orders
 * Features: View/Add/Edit/Delete users, Approve items, View statistics
 * Updated: Added Admin Quick Actions, Edit User links, Manage Clothing, Communication Center
 */

session_start();
require_once 'DBConn.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

// ============================================
// ADMIN: ADD NEW USER
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $errors = [];
    
    // Validation
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    
    // Check for existing user
    $check_sql = "SELECT * FROM tblUser WHERE username = '$username' OR email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "Username or email already exists!";
    }
    
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $verified_at = ($status == 'verified') ? 'NOW()' : 'NULL';
        
        $insert_sql = "INSERT INTO tblUser (full_name, email, username, password_hash, phone, address, status, verified_at, created_at) 
                       VALUES ('$full_name', '$email', '$username', '$password_hash', '$phone', '$address', '$status', $verified_at, NOW())";
        
        if (mysqli_query($conn, $insert_sql)) {
            $success = "✅ User '$username' has been created successfully!";
        } else {
            $error = "Failed to create user: " . mysqli_error($conn);
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

// Handle user verification
if (isset($_GET['verify_user'])) {
    $user_id = (int)$_GET['verify_user'];
    $sql = "UPDATE tblUser SET status = 'verified', verified_at = NOW() WHERE user_id = $user_id";
    mysqli_query($conn, $sql);
    $success = "User has been verified successfully!";
}

// Handle user suspension
if (isset($_GET['suspend_user'])) {
    $user_id = (int)$_GET['suspend_user'];
    $sql = "UPDATE tblUser SET status = 'suspended' WHERE user_id = $user_id";
    mysqli_query($conn, $sql);
    $success = "User has been suspended!";
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $user_id = (int)$_GET['delete_user'];
    $sql = "DELETE FROM tblUser WHERE user_id = $user_id";
    mysqli_query($conn, $sql);
    $success = "User has been deleted!";
}

// Handle item approval
if (isset($_GET['approve_item'])) {
    $item_id = (int)$_GET['approve_item'];
    $sql = "UPDATE tblClothes SET status = 'approved' WHERE clothes_id = $item_id";
    mysqli_query($conn, $sql);
    $success = "Item has been approved and will appear in the shop!";
}

// Handle item rejection
if (isset($_GET['reject_item'])) {
    $item_id = (int)$_GET['reject_item'];
    $sql = "UPDATE tblClothes SET status = 'rejected' WHERE clothes_id = $item_id";
    mysqli_query($conn, $sql);
    $success = "Item has been rejected!";
}

// ============================================
// GET STATISTICS
// ============================================

// User stats
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM tblUser");
$stats['total_users'] = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) as pending FROM tblUser WHERE status = 'pending'");
$stats['pending_users'] = mysqli_fetch_assoc($result)['pending'];

$result = mysqli_query($conn, "SELECT COUNT(*) as verified FROM tblUser WHERE status = 'verified'");
$stats['verified_users'] = mysqli_fetch_assoc($result)['verified'];

$result = mysqli_query($conn, "SELECT COUNT(*) as suspended FROM tblUser WHERE status = 'suspended'");
$stats['suspended_users'] = mysqli_fetch_assoc($result)['suspended'];

// Item stats
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM tblClothes");
$stats['total_items'] = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) as pending FROM tblClothes WHERE status = 'pending'");
$stats['pending_items'] = mysqli_fetch_assoc($result)['pending'];

$result = mysqli_query($conn, "SELECT COUNT(*) as approved FROM tblClothes WHERE status = 'approved'");
$stats['approved_items'] = mysqli_fetch_assoc($result)['approved'];

// Order stats
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM tblAorder");
$stats['total_orders'] = mysqli_fetch_assoc($result)['total'];

// Get data for tables
$pending_users = mysqli_query($conn, "SELECT * FROM tblUser WHERE status = 'pending' ORDER BY created_at DESC");
$all_users = mysqli_query($conn, "SELECT * FROM tblUser ORDER BY created_at DESC");
$pending_items = mysqli_query($conn, "SELECT c.*, u.username as seller_name FROM tblClothes c JOIN tblUser u ON c.seller_id = u.user_id WHERE c.status = 'pending' ORDER BY c.created_at DESC");
$approved_items = mysqli_query($conn, "SELECT c.*, u.username as seller_name FROM tblClothes c JOIN tblUser u ON c.seller_id = u.user_id WHERE c.status = 'approved' ORDER BY c.created_at DESC LIMIT 10");
$recent_orders = mysqli_query($conn, "SELECT o.*, u.username as buyer_name FROM tblAorder o JOIN tblUser u ON o.buyer_id = u.user_id ORDER BY o.order_date DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }
        
        /* Header */
        .admin-header {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .admin-logo {
            font-size: 24px;
            font-weight: 800;
            color: #0a2a28;
        }
        .admin-logo span { color: #e57e5c; }
        .admin-user { display: flex; gap: 15px; align-items: center; }
        .welcome-text {
            background: #f0f2f5;
            padding: 8px 20px;
            border-radius: 40px;
        }
        .admin-btn {
            padding: 8px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-logout { background: #dc3545; color: white; }
        .btn-view-site { background: #0a2a28; color: white; }
        
        /* Container */
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            cursor: pointer;
            transition: transform 0.2s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .stat-icon {
            width: 50px;
            height: 50px;
            background: #f0f2f5;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .stat-icon i { font-size: 24px; color: #e57e5c; }
        .stat-value { font-size: 32px; font-weight: 800; color: #0a2a28; }
        .stat-label { color: #666; font-size: 14px; }
        .stat-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 10px;
        }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-verified { background: #d4edda; color: #155724; }
        
        /* Quick Actions Card */
        .quick-actions-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .quick-actions-card h3 {
            margin-bottom: 15px;
            color: #0a2a28;
            font-size: 18px;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .action-btn-primary {
            background: #0a2a28;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .action-btn-primary:hover {
            background: #e57e5c;
            transform: translateY(-2px);
        }
        .action-btn-accent {
            background: #e57e5c;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .action-btn-accent:hover {
            background: #c96a4a;
            transform: translateY(-2px);
        }
        .action-btn-info {
            background: #17a2b8;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .action-btn-info:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        
        /* Section Cards */
        .section-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .btn-add-user {
            background: #28a745;
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            font-weight: 500;
        }
        .btn-add-user:hover {
            background: #218838;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 550px;
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .close-modal {
            float: right;
            font-size: 28px;
            cursor: pointer;
            color: #aaa;
        }
        .close-modal:hover { color: #333; }
        
        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
            display: block;
        }
        .data-table th, .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .data-table th { background: #f8f9fa; font-weight: 600; }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-verified { background: #d4edda; color: #155724; }
        .status-suspended { background: #f8d7da; color: #721c24; }
        
        .action-btn {
            padding: 5px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
            margin: 2px;
        }
        .btn-verify { background: #28a745; color: white; }
        .btn-verify:hover { background: #218838; }
        .btn-suspend { background: #ffc107; color: #333; }
        .btn-suspend:hover { background: #e0a800; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; }
        .btn-approve { background: #28a745; color: white; }
        .btn-approve:hover { background: #218838; }
        .btn-reject { background: #ffc107; color: #333; }
        .btn-reject:hover { background: #e0a800; }
        .btn-edit {
            background: #17a2b8;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
            margin: 2px;
        }
        .btn-edit:hover { background: #138496; }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .btn-submit {
            width: 100%;
            background: #0a2a28;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-submit:hover {
            background: #e57e5c;
        }
        
        .item-preview-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .data-table th, .data-table td { padding: 8px; font-size: 12px; }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="admin-logo">pastimes<span>.</span> <span style="font-size:14px">Admin Panel</span></div>
        <div class="admin-user">
            <div class="welcome-text">
                <i class="fas fa-user-shield"></i> 
                Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
            </div>
            <a href="?logout=1" class="admin-btn btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <a href="index.php" class="admin-btn btn-view-site"><i class="fas fa-eye"></i> View Site</a>
        </div>
    </div>
    
    <div class="admin-container">
        <?php if (isset($success)): ?>
            <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card" onclick="scrollToSection('all-users-section')">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Total Users</div>
                <span class="stat-badge badge-verified"><?php echo $stats['verified_users']; ?> Verified</span>
            </div>
            <div class="stat-card" onclick="scrollToSection('pending-users-section')">
                <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
                <div class="stat-value"><?php echo $stats['pending_users']; ?></div>
                <div class="stat-label">Pending Verification</div>
                <span class="stat-badge badge-pending">Needs Review</span>
            </div>
            <div class="stat-card" onclick="scrollToSection('pending-items-section')">
                <div class="stat-icon"><i class="fas fa-tshirt"></i></div>
                <div class="stat-value"><?php echo $stats['total_items']; ?></div>
                <div class="stat-label">Total Items</div>
                <span class="stat-badge badge-pending"><?php echo $stats['pending_items']; ?> Pending</span>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        
        <!-- Admin Quick Actions - NEW -->
        <div class="quick-actions-card">
            <h3><i class="fas fa-cog"></i> Admin Quick Actions</h3>
            <div class="action-buttons">
                <a href="admin_manage_clothing.php" class="action-btn-primary">
                    <i class="fas fa-tshirt"></i> Manage Clothing (Add/Edit/Delete)
                </a>
                <a href="admin_communication.php" class="action-btn-accent">
                    <i class="fas fa-comments"></i> Communication Center
                </a>
                <a href="admin_dashboard.php#all-users-section" class="action-btn-info">
                    <i class="fas fa-user-edit"></i> Edit Users (Click Edit below)
                </a>
            </div>
        </div>
        
        <!-- Pending Items -->
        <div id="pending-items-section" class="section-card">
            <div class="section-header">
                <h2><i class="fas fa-tshirt"></i> 📦 Pending Items (Awaiting Approval)</h2>
                <span class="stat-badge badge-pending"><?php echo $stats['pending_items']; ?> Items</span>
            </div>
            <?php if (mysqli_num_rows($pending_items) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Seller</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($pending_items)): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($item['image']); ?>" class="item-preview-img" onerror="this.src='https://placehold.co/50x50/0a2a28/white?text=No+Image'"></td>
                            <td><strong><?php echo htmlspecialchars($item['name']); ?></strong><br><small><?php echo htmlspecialchars($item['brand']); ?></small></td>
                            <td><?php echo htmlspecialchars($item['seller_name']); ?></td>
                            <td>R<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['category']; ?></td>
                            <td>
                                <a href="?approve_item=<?php echo $item['clothes_id']; ?>" class="action-btn btn-approve" onclick="return confirm('Approve this item?')">Approve</a>
                                <a href="?reject_item=<?php echo $item['clothes_id']; ?>" class="action-btn btn-reject" onclick="return confirm('Reject this item?')">Reject</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745;"></i>
                    <p>No pending items! All items have been reviewed.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pending Users -->
        <div id="pending-users-section" class="section-card">
            <div class="section-header">
                <h2><i class="fas fa-user-check"></i> 👥 Pending User Verifications</h2>
                <span class="stat-badge badge-pending"><?php echo $stats['pending_users']; ?> Users</span>
            </div>
            <?php if (mysqli_num_rows($pending_users) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = mysqli_fetch_assoc($pending_users)): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="?verify_user=<?php echo $user['user_id']; ?>" class="action-btn btn-verify" onclick="return confirm('Verify this user?')">Verify</a>
                                <a href="?suspend_user=<?php echo $user['user_id']; ?>" class="action-btn btn-suspend" onclick="return confirm('Suspend this user?')">Suspend</a>
                                <a href="?delete_user=<?php echo $user['user_id']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete this user permanently?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745;"></i>
                    <p>No pending users! All users are verified.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- All Users Management with Edit Links -->
        <div id="all-users-section" class="section-card">
            <div class="section-header">
                <h2><i class="fas fa-users"></i> 👤 All Users Management</h2>
                <button class="btn-add-user" onclick="openAddUserModal()">
                    <i class="fas fa-user-plus"></i> Add New User
                </button>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($all_users)): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><span class="status-badge status-<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                        <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <!-- EDIT USER LINK - NEW -->
                            <a href="admin_edit_user.php?id=<?php echo $user['user_id']; ?>" class="action-btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <?php if ($user['status'] != 'verified'): ?>
                                <a href="?verify_user=<?php echo $user['user_id']; ?>" class="action-btn btn-verify">Verify</a>
                            <?php endif; ?>
                            <?php if ($user['status'] != 'suspended'): ?>
                                <a href="?suspend_user=<?php echo $user['user_id']; ?>" class="action-btn btn-suspend">Suspend</a>
                            <?php endif; ?>
                            <a href="?delete_user=<?php echo $user['user_id']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete this user?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Recent Orders -->
        <div class="section-card">
            <div class="section-header">
                <h2><i class="fas fa-shopping-cart"></i> 📦 Recent Orders</h2>
                <a href="admin_communication.php" class="action-btn-accent" style="padding: 5px 15px;">
                    <i class="fas fa-comment"></i> Message Buyers
                </a>
            </div>
            <?php if (mysqli_num_rows($recent_orders) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Buyer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                            <td>R<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo strtoupper($order['payment_method']); ?></td>
                            <td><span class="status-badge status-<?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart" style="font-size: 48px; color: #999;"></i>
                    <p>No orders yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeAddUserModal()">&times;</span>
            <h3><i class="fas fa-user-plus"></i> Add New User</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required placeholder="Enter full name">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required placeholder="Enter email address">
                </div>
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required placeholder="Choose username">
                </div>
                <div class="form-group">
                    <label>Password * (min 6 chars)</label>
                    <input type="password" name="password" required placeholder="Minimum 6 characters">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" placeholder="Optional">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="2" placeholder="Optional"></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="pending">Pending (Needs verification)</option>
                        <option value="verified">Verified (Can login immediately)</option>
                        <option value="suspended">Suspended (Blocked)</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn-submit">
                    <i class="fas fa-save"></i> Create User
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'block';
        }
        function closeAddUserModal() {
            document.getElementById('addUserModal').style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target == document.getElementById('addUserModal')) {
                closeAddUserModal();
            }
        }
        function scrollToSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    </script>
</body>
</html>
