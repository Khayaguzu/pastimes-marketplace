<?php
/**
 * User Registration Page
 * Filename: register.php
 * Purpose: Allows new users to create an account
 * ALL user data is saved to tblUser in phpMyAdmin
 * New users have status = 'pending' until admin approves
 */

// ============================================
// START SESSION AND ERROR REPORTING
// ============================================

session_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

// ============================================
// INCLUDE DATABASE CONNECTION
// ============================================

require_once 'DBConn.php';

// ============================================
// INITIALIZE VARIABLES
// ============================================

$error = '';      // Stores error messages
$success = '';    // Stores success messages

// ============================================
// PROCESS REGISTRATION FORM
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    
    // Get and sanitize form data
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Array to collect validation errors
    $errors = array();
    
    // ============================================
    // VALIDATION CHECKS
    // ============================================
    
    // Check required fields
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    if (empty($email)) {
        $errors[] = "Email address is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // Password validation
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    if (strlen($password) < 12) {
        $errors[] = "Password must be at least 12 characters";
    }
    
    // Check if username or email already exists in database
    if (empty($errors)) {
        $check_statement = mysqli_prepare($conn, 'SELECT user_id, username, email FROM tblUser WHERE username = ? OR email = ?');
        mysqli_stmt_bind_param($check_statement, 'ss', $username, $email);
        mysqli_stmt_execute($check_statement);
        $check_result = mysqli_stmt_get_result($check_statement);
        
        if (!$check_result) {
            $errors[] = "Database error: " . mysqli_error($conn);
        } elseif (mysqli_num_rows($check_result) > 0) {
            $existing = mysqli_fetch_assoc($check_result);
            if ($existing['username'] == $username) {
                $errors[] = "Username '$username' is already taken. Please choose another.";
            }
            if ($existing['email'] == $email) {
                $errors[] = "Email '$email' is already registered. Please use another email or login.";
            }
        }
    }
    
    // ============================================
    // INSERT USER INTO DATABASE
    // ============================================
    
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Current timestamp for created_at
        $created_at = date('Y-m-d H:i:s');
        
        // SQL INSERT statement - SAVES USER TO phpMyAdmin
        $insert_statement = mysqli_prepare(
            $conn,
            "INSERT INTO tblUser (full_name, email, username, password_hash, phone, address, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)"
        );
        mysqli_stmt_bind_param($insert_statement, 'sssssss', $full_name, $email, $username, $password_hash, $phone, $address, $created_at);

        if (mysqli_stmt_execute($insert_statement)) {
            // Get the auto-generated user ID
            $new_user_id = mysqli_insert_id($conn);
            
            // Success message
            $success = "✅ Registration successful! Your account (ID: $new_user_id) has been saved to our database.<br>
                       Your account is pending admin verification. You will be notified once approved.<br>
                       <strong>Username:</strong> $username | <strong>Password:</strong> [hidden]";
            
            // Clear form data
            $_POST = array();
            
            // Log the registration for debugging
            error_log("New user registered: $username (ID: $new_user_id) at " . date('Y-m-d H:i:s'));
        } else {
            // Database error
            $error = "❌ Registration failed. Please try again.";
            error_log("Registration failed for $username: " . mysqli_error($conn));
        }
    } else {
        // Validation errors
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a2a28 0%, #1a4a47 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            max-width: 550px;
            width: 100%;
            margin: 0 auto;
        }
        
        .register-card {
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
        
        .logo span {
            color: #e57e5c;
        }
        
        .tagline {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 25px;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #0a2a28;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #e57e5c;
            box-shadow: 0 0 0 3px rgba(229,126,92,0.1);
        }
        
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #dc3545;
        }
        
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #28a745;
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
            transition: all 0.2s;
            margin-top: 10px;
        }
        
        .btn-primary:hover {
            background: #e57e5c;
            transform: translateY(-2px);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .login-link a {
            color: #e57e5c;
            text-decoration: none;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .required {
            color: #dc3545;
        }
        
        .info-box {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
            font-size: 13px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }
        
        .info-box i {
            color: #e57e5c;
            margin-right: 8px;
        }
        
        .password-hint {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
        
        @media (max-width: 600px) {
            .register-card {
                padding: 25px;
            }
        }
        
        .row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        @media (max-width: 500px) {
            .row-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="logo">pastimes<span>.</span></div>
            <div class="tagline">Sustainable Fashion Marketplace</div>
            <h2>Create Account</h2>
            
            <!-- Display error message -->
            <?php if ($error): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Display success message -->
            <?php if ($success): ?>
                <div class="success-msg">
                    <i class="fas fa-check-circle"></i> 
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <!-- Registration Form -->
            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" required 
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                           placeholder="Enter your full name">
                </div>
                
                <div class="row-2">
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               placeholder="your@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label>Username <span class="required">*</span></label>
                        <input type="text" name="username" required 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               placeholder="Choose a username">
                    </div>
                </div>
                
                <div class="row-2">
                    <div class="form-group">
                        <label>Password <span class="required">*</span></label>
                        <input type="password" name="password" id="password" required 
                               placeholder="Min. 6 characters">
                        <div class="password-hint">Minimum 6 characters</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm Password <span class="required">*</span></label>
                        <input type="password" name="confirm_password" id="confirm_password" required 
                               placeholder="Re-enter password">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                           placeholder="Optional - for delivery updates">
                </div>
                
                <div class="form-group">
                    <label>Delivery Address</label>
                    <textarea name="address" rows="3" 
                              placeholder="Optional - Your default delivery address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" required> 
                        I agree to the <a href="#" style="color:#e57e5c;">Terms and Conditions</a> and 
                        <a href="#" style="color:#e57e5c;">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" name="register" class="btn-primary">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
            
            <div class="info-box">
                <i class="fas fa-database"></i> 
                <strong>Your data is saved to our MySQL database!</strong><br>
                After registration, an admin must verify your account before you can log in.
                You will receive an email notification once approved.
            </div>
        </div>
    </div>
    
    <script>
        // Real-time password match validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (password !== confirm) {
                alert('❌ Passwords do not match!');
                e.preventDefault();
                return false;
            }
            
            if (password.length < 6) {
                alert('❌ Password must be at least 6 characters!');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
        
        // Real-time password match indicator
        const password = document.getElementById('password');
        const confirm = document.getElementById('confirm_password');
        
        function checkPasswordMatch() {
            if (password.value !== confirm.value) {
                confirm.style.borderColor = '#dc3545';
            } else if (confirm.value.length > 0) {
                confirm.style.borderColor = '#28a745';
            } else {
                confirm.style.borderColor = '#ddd';
            }
        }
        
        password.addEventListener('keyup', checkPasswordMatch);
        confirm.addEventListener('keyup', checkPasswordMatch);
    </script>
</body>
</html>
