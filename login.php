<?php
/**
 * User Login Page
 * Filename: login.php
 * Purpose: Allows verified users to login to their account
 * Only users with status = 'verified' can login
 */

session_start();
require_once 'DBConn.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: shop.php');
    exit;
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Prepared statements keep user-controlled values separate from SQL code.
    $statement = mysqli_prepare($conn, 'SELECT * FROM tblUser WHERE username = ? OR email = ? LIMIT 1');
    mysqli_stmt_bind_param($statement, 'ss', $username, $email);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Support legacy MD5 demo records once, then replace them with a secure hash.
        $stored_hash = $user['password_hash'];
        $legacy_match = strlen($stored_hash) === 32 && hash_equals($stored_hash, md5($password));
        $password_matches = password_verify($password, $stored_hash) || $legacy_match;

        if (!$password_matches) {
            $error = "❌ Invalid username/email or password!";
        } elseif ($legacy_match) {
            $secure_hash = password_hash($password, PASSWORD_DEFAULT);
            $upgrade = mysqli_prepare($conn, 'UPDATE tblUser SET password_hash = ? WHERE user_id = ?');
            $user_id = (int) $user['user_id'];
            mysqli_stmt_bind_param($upgrade, 'si', $secure_hash, $user_id);
            mysqli_stmt_execute($upgrade);
        }
        
        // Check user status
        if (!$password_matches) {
            // The generic error above prevents account discovery.
        } elseif ($user['status'] === 'pending') {
            $error = "⏳ Your account is pending admin approval. Please wait for verification.";
        } elseif ($user['status'] === 'suspended') {
            $error = "🚫 Your account has been suspended. Please contact support.";
        } elseif ($user['status'] === 'verified') {
            // Login successful
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            header('Location: shop.php');
            exit;
        }
    } else {
        $error = "❌ Invalid username/email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a2a28 0%, #1a4a47 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container { max-width: 450px; width: 100%; }
        .login-card {
            background: white;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .logo {
            font-size: 32px;
            font-weight: 800;
            color: #0a2a28;
            text-align: center;
            margin-bottom: 10px;
        }
        .logo span { color: #e57e5c; }
        h2 { text-align: center; margin-bottom: 25px; color: #0a2a28; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 14px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #e57e5c;
        }
        .btn-primary {
            width: 100%;
            background: #0a2a28;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-primary:hover { background: #e57e5c; }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .register-link { text-align: center; margin-top: 20px; font-size: 14px; }
        .register-link a { color: #e57e5c; text-decoration: none; }
        .admin-link { text-align: center; margin-top: 15px; font-size: 12px; }
        .admin-link a { color: #888; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">pastimes<span>.</span></div>
            <h2>Welcome Back</h2>
            
            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Username</label>
                    <input type="text" name="username" required placeholder="Enter username">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" required placeholder="Enter email">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" required placeholder="Enter password">
                </div>
                <button type="submit" name="login" class="btn-primary">Login</button>
            </form>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
            <div class="admin-link">
                <a href="admin_login.php">Admin Login →</a>
            </div>
        </div>
    </div>
</body>
</html>
