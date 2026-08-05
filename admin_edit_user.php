<?php
/**
 * Admin Edit User Page
 * Allows admin to update existing user details
 */

session_start();
require_once 'DBConn.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

// Get user details
$sql = "SELECT * FROM tblUser WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header('Location: admin_dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_sql = "UPDATE tblUser SET 
                   full_name = '$full_name',
                   email = '$email',
                   username = '$username',
                   phone = '$phone',
                   address = '$address',
                   status = '$status'
                   WHERE user_id = $user_id";
    
    if (mysqli_query($conn, $update_sql)) {
        $success = "User updated successfully!";
        // Refresh user data
        $result = mysqli_query($conn, "SELECT * FROM tblUser WHERE user_id = $user_id");
        $user = mysqli_fetch_assoc($result);
    } else {
        $error = "Update failed: " . mysqli_error($conn);
    }
}

// Handle password reset
if (isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'];
    if (strlen($new_password) >= 6) {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE tblUser SET password_hash = '$password_hash' WHERE user_id = $user_id";
        if (mysqli_query($conn, $update_sql)) {
            $success = "Password reset successfully! New password: $new_password";
        } else {
            $error = "Password reset failed: " . mysqli_error($conn);
        }
    } else {
        $error = "Password must be at least 6 characters";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }
        .container { max-width: 800px; margin: 0 auto; }
        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        h1 { color: #0a2a28; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
        }
        .btn-primary {
            background: #0a2a28;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #0a2a28;
            text-decoration: none;
        }
        hr { margin: 20px 0; }
        @media (max-width: 600px) { .row-2 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><i class="fas fa-user-edit"></i> Edit User</h1>
            <p>Editing user: <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
            
            <?php if ($success): ?>
                <div class="success-msg">✓ <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error-msg">⚠ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row-2">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" required value="<?php echo htmlspecialchars($user['username']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="pending" <?php echo $user['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="verified" <?php echo $user['status'] == 'verified' ? 'selected' : ''; ?>>Verified</option>
                            <option value="suspended" <?php echo $user['status'] == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                <button type="submit" name="update_user" class="btn-primary">Update User</button>
                <a href="admin_dashboard.php" class="btn-secondary">Back to Dashboard</a>
            </form>
            
            <hr>
            
            <h3>Reset Password</h3>
            <form method="POST" action="" onsubmit="return confirm('Reset password for this user?')">
                <div class="form-group">
                    <label>New Password (min 6 characters)</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <button type="submit" name="reset_password" class="btn-danger">Reset Password</button>
            </form>
            
            <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
