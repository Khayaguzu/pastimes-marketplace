<?php
/**
 * Admin Login Page
 * Filename: admin_login.php
 * Purpose: Allows administrators to access the admin dashboard
 */

session_start();
require_once 'DBConn.php';

// Redirect if already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $statement = mysqli_prepare($conn, 'SELECT * FROM tblAdmin WHERE username = ? LIMIT 1');
    mysqli_stmt_bind_param($statement, 's', $username);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    
    if (mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        $stored_hash = $admin['password_hash'];
        $legacy_match = strlen($stored_hash) === 32 && hash_equals($stored_hash, md5($password));

        if (!password_verify($password, $stored_hash) && !$legacy_match) {
            $error = "Invalid admin credentials!";
        } else {
            if ($legacy_match) {
                $secure_hash = password_hash($password, PASSWORD_DEFAULT);
                $upgrade = mysqli_prepare($conn, 'UPDATE tblAdmin SET password_hash = ? WHERE admin_id = ?');
                $admin_id = (int) $admin['admin_id'];
                mysqli_stmt_bind_param($upgrade, 'si', $secure_hash, $admin_id);
                mysqli_stmt_execute($upgrade);
            }

            session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        
        // Update last login time
        mysqli_query($conn, "UPDATE tblAdmin SET last_login = NOW() WHERE admin_id = " . $admin['admin_id']);
        
        header('Location: admin_dashboard.php');
        exit;
        }
    } else {
        $error = "Invalid admin credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-login-container { max-width: 400px; width: 100%; margin: 20px; }
        .admin-login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .admin-icon {
            width: 80px;
            height: 80px;
            background: #0a2a28;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .admin-icon i { font-size: 40px; color: #ffd700; }
        h2 { color: #0a2a28; margin-bottom: 10px; }
        .subtitle { color: #666; font-size: 14px; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
        }
        .login-btn {
            width: 100%;
            background: #0a2a28;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .login-btn:hover { background: #e57e5c; }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-card">
            <div class="admin-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h2>Admin Portal</h2>
            <p class="subtitle">Enter your administrator credentials</p>
            
            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="login-btn">Login as Admin</button>
            </form>
            
            <a href="index.php" class="back-link">← Back to Website</a>
        </div>
    </div>
</body>
</html>
