<?php
/**
 * Website Header Component
 * This file contains the navigation bar and opening HTML tags
 * Included at the top of every page
 */

// Start session if not already started (prevents duplicate session errors)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pastimes - South Africa's premier second-hand fashion marketplace">
    <title>Pastimes – Second-Hand Fashion Marketplace</title>
    
    <!-- Google Fonts for Inter font family -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* ============================================
           GLOBAL STYLES & CSS VARIABLES
           ============================================ */
        
        /* CSS Custom Properties (Variables) for consistent theming */
        :root {
            --primary: #0f3d3e;      /* Dark teal - main brand color */
            --primary-light: #1b5e5a; /* Lighter teal for hover states */
            --accent: #e57e5c;        /* Warm orange - accent color */
            --accent-light: #f8a68b;  /* Light accent for hover */
            --bg: #faf8f6;            /* Off-white background */
            --card-bg: #ffffff;       /* White card background */
            --border: #eae3dc;        /* Light border color */
            --text: #2a2a2a;          /* Dark gray text */
            --text-light: #6b625c;    /* Light gray text */
            --shadow: 0 10px 30px -10px rgba(0,0,0,0.08);
            --shadow-hover: 0 20px 35px -12px rgba(0,0,0,0.15);
        }

        /* Reset default browser styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f4f2f0;
            color: var(--text);
            min-height: 100vh;
            line-height: 1.5;
        }

        /* Main container - centers content with max width */
        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 30px;
            width: 100%;
        }

        /* ============================================
           NAVIGATION BAR STYLES
           ============================================ */
        
        .navbar {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 15px rgba(0,0,0,0.02);
        }

        .nav-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        /* Logo Styles */
        .logo {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: var(--primary);
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .logo:hover {
            opacity: 0.8;
        }

        .logo span {
            color: var(--accent);
            font-weight: 400;
        }

        /* Search Bar */
        .search-wrapper {
            background: #f0eeeb;
            border-radius: 40px;
            padding: 10px 22px;
            width: 350px;
            display: flex;
            align-items: center;
            border: 1px solid #e0d7cf;
            transition: all 0.2s;
        }

        .search-wrapper:focus-within {
            border-color: var(--accent);
            background: white;
            box-shadow: 0 0 0 4px rgba(229,126,92,0.1);
        }

        .search-wrapper i {
            color: #998e84;
            margin-right: 12px;
            font-size: 1rem;
        }

        .search-wrapper input {
            border: none;
            background: transparent;
            width: 100%;
            outline: none;
            font-size: 0.9rem;
        }

        /* Navigation Actions (buttons & links) */
        .nav-actions {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            font-size: 0.9rem;
            transition: color 0.2s;
            padding: 8px 12px;
            border-radius: 30px;
        }

        .nav-link:hover {
            color: var(--accent);
            background: #f0eeeb;
        }

        .nav-link.btn-link {
            background: var(--primary);
            color: white;
            padding: 8px 22px;
        }

        .nav-link.btn-link:hover {
            background: var(--primary-light);
            color: white;
        }

        /* User Profile Badge */
        .user-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f0eeeb;
            padding: 6px 16px 6px 12px;
            border-radius: 40px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .user-badge:hover {
            background: #e0d7cf;
        }

        .user-badge i {
            font-size: 1.2rem;
            color: var(--primary);
        }

        /* Shopping Cart Icon */
        .cart-icon {
            position: relative;
            cursor: pointer;
            font-size: 20px;
            color: var(--text);
            padding: 8px;
            transition: color 0.2s;
        }

        .cart-icon:hover {
            color: var(--accent);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent);
            color: white;
            font-size: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Admin Link Special Style */
        .admin-link {
            background: #2c1810;
            color: #ffd700 !important;
            border: 1px solid #ffd700;
        }

        .admin-link:hover {
            background: #ffd700;
            color: #2c1810 !important;
        }

        /* Messages Icon with Notification Badge */
        .messages-link {
            position: relative;
        }
        
        .unread-badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: #e57e5c;
            color: white;
            font-size: 10px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* ============================================
           BUTTON STYLES
           ============================================ */
        
        .btn {
            padding: 14px 34px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            border: 1px solid var(--border);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        /* ============================================
           PRODUCT CARD STYLES
           ============================================ */
        
        .product-card, .item-card {
            background: var(--card-bg);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.25s ease;
            cursor: pointer;
            border: 1px solid var(--border);
        }

        .product-card:hover, .item-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-hover);
        }

        .product-img, .card-img {
            width: 100%;
            aspect-ratio: 1/1;
            object-fit: cover;
            background: #e5dbd2;
        }

        .product-info, .card-details {
            padding: 16px;
        }

        .product-price, .price-tag {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .product-name, .item-title {
            font-weight: 600;
            margin: 8px 0 5px;
            font-size: 0.95rem;
            color: #26221e;
        }

        /* ============================================
           GRID LAYOUTS
           ============================================ */
        
        .products-grid, .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }

        /* Responsive grid adjustments */
        @media (max-width: 1100px) {
            .products-grid, .grid-4 { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 800px) {
            .products-grid, .grid-4 { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 500px) {
            .products-grid, .grid-4 { grid-template-columns: 1fr; }
        }

        /* ============================================
           FORM STYLES
           ============================================ */
        
        .form-card {
            max-width: 550px;
            margin: 50px auto;
            background: white;
            padding: 45px;
            border-radius: 30px;
            box-shadow: var(--shadow);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 500;
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 18px;
            border: 1px solid #e0d7cf;
            border-radius: 30px;
            font-size: 0.9rem;
            background: #fcfaf8;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(229,126,92,0.1);
        }

        /* ============================================
           STATUS BADGES
           ============================================ */
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-block;
        }
        .status-pending { background: #ffecb3; color: #ff8f00; }
        .status-verified { background: #c8e6c9; color: #2e7d32; }
        .status-suspended { background: #ffcdd2; color: #c62828; }

        /* ============================================
           RESPONSIVE DESIGN
           ============================================ */
        
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            .nav-container {
                flex-direction: column;
            }
            .search-wrapper {
                width: 100%;
            }
            .form-card {
                padding: 30px 20px;
                margin: 30px 15px;
            }
        }

        /* ============================================
           ANIMATIONS
           ============================================ */
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-light);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container nav-container">
            <!-- Logo - clicks to homepage -->
            <div class="logo" onclick="window.location.href='index.php'">pastimes<span>.</span></div>
            
            <!-- Search Bar -->
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="globalSearch" placeholder="Search vintage, brands, size...">
            </div>
            
            <!-- Navigation Actions -->
            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- User is logged in - show user menu -->
                    <div class="user-badge" onclick="window.location.href='dashboard.php'">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </div>
                    <a href="order_history.php" class="nav-link">
                        <i class="fas fa-history"></i> My Orders
                    </a>
                    <!-- MESSAGES LINK - ADDED HERE -->
                    <a href="messages.php" class="nav-link messages-link">
                        <i class="fas fa-comment-dots"></i> Messages
                        <?php
                        // Get unread message count for logged-in user
                        if (isset($_SESSION['user_id'])) {
                            $user_id = $_SESSION['user_id'];
                            $unread_query = "SELECT COUNT(*) as unread FROM tblMessages WHERE to_user_id = $user_id AND is_read = 0";
                            $unread_result = mysqli_query($conn, $unread_query);
                            if ($unread_result && mysqli_num_rows($unread_result) > 0) {
                                $unread_data = mysqli_fetch_assoc($unread_result);
                                if ($unread_data['unread'] > 0) {
                                    echo '<span class="unread-badge">' . $unread_data['unread'] . '</span>';
                                }
                            }
                        }
                        ?>
                    </a>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <!-- User is not logged in - show login/register -->
                    <a href="login.php" class="nav-link">Log in</a>
                    <a href="register.php" class="nav-link btn-link">Register</a>
                <?php endif; ?>
                
                <!-- Admin Login Link -->
                <a href="admin_login.php" class="nav-link admin-link">
                    <i class="fas fa-user-shield"></i> Admin
                </a>
                
                <!-- Shopping Cart -->
                <div class="cart-icon" onclick="window.location.href='cart.php'">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Area -->
    <main class="container">
    <script>
        // Cart Functions
        let pastimesCart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');
        
        function addToCart(id, name, price, image) {
            let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');
            let existing = cart.find(item => item.id === id);
            
            if (existing) {
                existing.quantity = (existing.quantity || 1) + 1;
            } else {
                cart.push({
                    id: id,
                    name: name,
                    price: price,
                    image: image,
                    quantity: 1
                });
            }
            
            localStorage.setItem('pastimes_cart', JSON.stringify(cart));
            showNotification('✓ Added to cart!', 'success');
            updateCartCount();
        }
        
        function updateCartCount() {
            let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');
            let count = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
            let cartCount = document.getElementById('cartCount');
            if (cartCount) {
                cartCount.textContent = count;
                cartCount.style.display = count > 0 ? 'flex' : 'none';
            }
        }
        
        function showNotification(message, type) {
            let notification = document.createElement('div');
            let bgColor = type === 'success' ? '#4caf50' : '#2196f3';
            notification.innerHTML = '<i class="fas fa-check-circle"></i> ' + message;
            notification.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: ' + bgColor + '; color: white; padding: 12px 20px; border-radius: 10px; z-index: 9999; animation: slideIn 0.3s ease; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
        
        function initSearch() {
            const searchInput = document.getElementById('globalSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const items = document.querySelectorAll('.product-card, .item-card');
                    items.forEach(item => {
                        const title = item.querySelector('.product-name, .item-title')?.innerText.toLowerCase() || '';
                        item.style.display = title.includes(searchTerm) ? 'block' : 'none';
                    });
                });
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
            initSearch();
        });
    </script>