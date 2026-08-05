<?php
/**
 * Sell Page - Landing page for sellers
 * Filename: sell.php
 * Purpose: Directs sellers to the upload page or login
 */

session_start();
require_once 'DBConn.php';

// Include header
require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell on Pastimes</title>
    <style>
        .sell-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #0a2a28 0%, #1a4a47 100%);
            border-radius: 30px;
            padding: 60px;
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }
        
        .hero-section h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .hero-section p {
            font-size: 18px;
            margin-bottom: 30px;
        }
        
        .btn-large {
            background: #e57e5c;
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 18px;
            font-weight: 600;
            display: inline-block;
            transition: all 0.2s;
        }
        
        .btn-large:hover {
            background: #c96a4a;
            transform: translateY(-3px);
        }
        
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin: 50px 0;
        }
        
        .step-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .step-icon {
            width: 80px;
            height: 80px;
            background: #f0f2f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .step-icon i {
            font-size: 40px;
            color: #e57e5c;
        }
        
        .step-card h3 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #0a2a28;
        }
        
        .benefits-section {
            background: #f8f9fa;
            border-radius: 30px;
            padding: 40px;
            margin-top: 30px;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-top: 30px;
        }
        
        .benefit {
            text-align: center;
        }
        
        .benefit i {
            font-size: 36px;
            color: #e57e5c;
            margin-bottom: 15px;
        }
        
        @media (max-width: 900px) {
            .steps-grid, .benefits-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .hero-section h1 {
                font-size: 32px;
            }
        }
        
        @media (max-width: 600px) {
            .steps-grid, .benefits-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="sell-container">
    <div class="hero-section">
        <h1>Turn Your Pre-loved Clothes into Cash</h1>
        <p>Join thousands of sellers making money on Pastimes. List your items for free!</p>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="upload-item.php" class="btn-large">Start Selling Now →</a>
        <?php else: ?>
            <a href="register.php" class="btn-large">Create Free Account →</a>
        <?php endif; ?>
    </div>
    
    <h2 style="text-align: center; margin-bottom: 30px;">How Selling Works</h2>
    <div class="steps-grid">
        <div class="step-card">
            <div class="step-icon"><i class="fas fa-camera"></i></div>
            <h3>1. Take Photos</h3>
            <p>Take clear, well-lit photos of your item from multiple angles</p>
        </div>
        <div class="step-card">
            <div class="step-icon"><i class="fas fa-upload"></i></div>
            <h3>2. List Your Item</h3>
            <p>Add description, brand, size, condition, and set your price (R50-R400)</p>
        </div>
        <div class="step-card">
            <div class="step-icon"><i class="fas fa-truck"></i></div>
            <h3>3. Ship & Earn</h3>
            <p>Once sold, ship the item and get paid directly to your account</p>
        </div>
    </div>
    
    <div class="benefits-section">
        <h2 style="text-align: center;">Why Sell With Us?</h2>
        <div class="benefits-grid">
            <div class="benefit">
                <i class="fas fa-tag"></i>
                <h4>List for Free</h4>
                <p>No listing fees or hidden costs</p>
            </div>
            <div class="benefit">
                <i class="fas fa-shield-alt"></i>
                <h4>Seller Protection</h4>
                <p>Secure payments and dispute resolution</p>
            </div>
            <div class="benefit">
                <i class="fas fa-users"></i>
                <h4>Large Audience</h4>
                <p>Thousands of buyers across South Africa</p>
            </div>
            <div class="benefit">
                <i class="fas fa-chart-line"></i>
                <h4>Quick Sales</h4>
                <p>Items typically sell within 2 weeks</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>