<?php
session_start();
require_once 'DBConn.php';
require_once 'includes/header.php';
?>

<style>
    .shipping-container {
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
    
    .shipping-info {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    .shipping-info h2 {
        color: #0a2a28;
        margin-bottom: 20px;
    }
    
    .shipping-info p {
        color: #666;
        line-height: 1.6;
        margin-bottom: 15px;
    }
    
    .shipping-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
        margin: 30px 0;
    }
    
    .shipping-card {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
    }
    
    .shipping-card h3 {
        color: #0a2a28;
        margin-bottom: 15px;
    }
    
    .shipping-card ul {
        padding-left: 20px;
        color: #666;
    }
    
    .shipping-card li {
        margin-bottom: 8px;
    }
    
    @media (max-width: 768px) {
        .shipping-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="shipping-container">
    <div class="page-header">
        <h1>Shipping Guide</h1>
        <p>Everything you need to know about shipping</p>
    </div>
    
    <div class="shipping-info">
        <h2>📦 How Shipping Works</h2>
        <p>We partner with trusted courier services to ensure your items arrive safely and on time. Shipping is available nationwide across South Africa.</p>
    </div>
    
    <div class="shipping-grid">
        <div class="shipping-card">
            <h3>For Buyers</h3>
            <ul>
                <li>Free delivery on orders over R500</li>
                <li>Standard delivery: R55 (3-5 business days)</li>
                <li>Express delivery: R95 (1-2 business days)</li>
                <li>Track your order with tracking number</li>
            </ul>
        </div>
        
        <div class="shipping-card">
            <h3>For Sellers</h3>
            <ul>
                <li>Print shipping labels easily</li>
                <li>Schedule free pickups</li>
                <li>Track all your shipments</li>
                <li>Get paid after delivery confirmation</li>
            </ul>
        </div>
    </div>
    
    <div class="shipping-info">
        <h2>📍 Delivery Areas</h2>
        <p>We deliver to all major cities and towns across South Africa including:</p>
        <ul style="padding-left: 40px; margin-top: 15px;">
            <li>Cape Town, Johannesburg, Durban, Pretoria, Port Elizabeth</li>
            <li>Bloemfontein, East London, Kimberley, Polokwane, Nelspruit</li>
            <li>And all other major centers nationwide</li>
        </ul>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>