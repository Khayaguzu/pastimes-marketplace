<?php
session_start();
require_once 'DBConn.php';
require_once 'includes/header.php';
?>

<style>
    .safety-container {
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
    
    .safety-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
    }
    
    .safety-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        border: 1px solid #eee;
    }
    
    .safety-icon {
        width: 60px;
        height: 60px;
        background: #f0f2f5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }
    
    .safety-icon i {
        font-size: 30px;
        color: #e57e5c;
    }
    
    .safety-card h3 {
        font-size: 22px;
        color: #0a2a28;
        margin-bottom: 15px;
    }
    
    .safety-card p {
        color: #666;
        line-height: 1.6;
        margin-bottom: 15px;
    }
    
    .safety-card ul {
        padding-left: 20px;
    }
    
    .safety-card li {
        color: #666;
        margin-bottom: 8px;
    }
    
    @media (max-width: 768px) {
        .safety-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="safety-container">
    <div class="page-header">
        <h1>Safety Tips</h1>
        <p>Stay safe while buying and selling</p>
    </div>
    
    <div class="safety-grid">
        <div class="safety-card">
            <div class="safety-icon"><i class="fas fa-user-check"></i></div>
            <h3>For Buyers</h3>
            <ul>
                <li>Always communicate through our platform</li>
                <li>Check seller ratings and reviews</li>
                <li>Never share personal banking details</li>
                <li>Use secure payment methods only</li>
                <li>Report suspicious listings immediately</li>
            </ul>
        </div>
        
        <div class="safety-card">
            <div class="safety-icon"><i class="fas fa-store"></i></div>
            <h3>For Sellers</h3>
            <ul>
                <li>Take clear, accurate photos of items</li>
                <li>Describe condition honestly</li>
                <li>Ship with tracking number</li>
                <li>Keep communication within the platform</li>
                <li>Verify buyer payment before shipping</li>
            </ul>
        </div>
    </div>
    
    <div class="safety-card" style="margin-top: 30px;">
        <h3>⚠️ Red Flags to Watch For</h3>
        <ul>
            <li>Buyers asking to pay outside the platform</li>
            <li>Sellers requesting personal information</li>
            <li>Deals that seem too good to be true</li>
            <li>Pressure to complete transactions quickly</li>
        </ul>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>