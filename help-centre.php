<?php
session_start();
require_once 'DBConn.php';
require_once 'includes/header.php';
?>

<style>
    .help-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .page-header h1 {
        font-size: 42px;
        color: #0a2a28;
        margin-bottom: 15px;
    }
    
    .help-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-bottom: 40px;
    }
    
    .help-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        border: 1px solid #eee;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .help-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .help-icon {
        width: 70px;
        height: 70px;
        background: #f0f2f5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    
    .help-icon i {
        font-size: 35px;
        color: #e57e5c;
    }
    
    .help-card h3 {
        font-size: 20px;
        color: #0a2a28;
        margin-bottom: 10px;
    }
    
    .help-card p {
        color: #666;
    }
    
    @media (max-width: 900px) {
        .help-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 600px) {
        .help-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="help-container">
    <div class="page-header">
        <h1>Help Centre</h1>
        <p>Find answers to your questions</p>
    </div>
    
    <div class="help-grid">
        <div class="help-card" onclick="window.location.href='how-it-works.php'">
            <div class="help-icon"><i class="fas fa-question-circle"></i></div>
            <h3>How It Works</h3>
            <p>Learn how to buy and sell on Pastimes</p>
        </div>
        
        <div class="help-card" onclick="window.location.href='shipping-guide.php'">
            <div class="help-icon"><i class="fas fa-truck"></i></div>
            <h3>Shipping Guide</h3>
            <p>Everything about delivery</p>
        </div>
        
        <div class="help-card" onclick="window.location.href='returns-policy.php'">
            <div class="help-icon"><i class="fas fa-undo-alt"></i></div>
            <h3>Returns Policy</h3>
            <p>Our 7-day return policy</p>
        </div>
    </div>
    
    <div class="help-grid">
        <div class="help-card" onclick="window.location.href='selling-tips.php'">
            <div class="help-icon"><i class="fas fa-chart-line"></i></div>
            <h3>Selling Tips</h3>
            <p>Maximize your sales</p>
        </div>
        
        <div class="help-card" onclick="window.location.href='contact.php'">
            <div class="help-icon"><i class="fas fa-envelope"></i></div>
            <h3>Contact Us</h3>
            <p>Get in touch with support</p>
        </div>
        
        <div class="help-card" onclick="window.location.href='safety-tips.php'">
            <div class="help-icon"><i class="fas fa-shield-alt"></i></div>
            <h3>Safety Tips</h3>
            <p>Stay safe while trading</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
