<?php
session_start();
require_once 'DBConn.php';
require_once 'includes/header.php';
?>

<style>
    .tips-container {
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
    
    .tips-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
    }
    
    .tip-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        border: 1px solid #eee;
    }
    
    .tip-icon {
        width: 60px;
        height: 60px;
        background: #f0f2f5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }
    
    .tip-icon i {
        font-size: 30px;
        color: #e57e5c;
    }
    
    .tip-card h3 {
        font-size: 22px;
        color: #0a2a28;
        margin-bottom: 15px;
    }
    
    .tip-card p {
        color: #666;
        line-height: 1.6;
        margin-bottom: 15px;
    }
    
    .tip-card ul {
        padding-left: 20px;
        color: #666;
    }
    
    .tip-card li {
        margin-bottom: 8px;
    }
    
    @media (max-width: 768px) {
        .tips-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="tips-container">
    <div class="page-header">
        <h1>Selling Tips & Tricks</h1>
        <p>Maximize your sales with these expert tips</p>
    </div>
    
    <div class="tips-grid">
        <div class="tip-card">
            <div class="tip-icon"><i class="fas fa-camera"></i></div>
            <h3>Take Great Photos</h3>
            <p>Quality photos sell items faster. Here's how:</p>
            <ul>
                <li>Use natural lighting</li>
                <li>Show multiple angles</li>
                <li>Include close-ups of details</li>
                <li>Use a plain background</li>
            </ul>
        </div>
        
        <div class="tip-card">
            <div class="tip-icon"><i class="fas fa-tag"></i></div>
            <h3>Price Your Items Right</h3>
            <p>Competitive pricing attracts buyers:</p>
            <ul>
                <li>Research similar items</li>
                <li>Consider item condition</li>
                <li>Price 10-20% below retail</li>
                <li>Offer bundle discounts</li>
            </ul>
        </div>
        
        <div class="tip-card">
            <div class="tip-icon"><i class="fas fa-pen"></i></div>
            <h3>Write Detailed Descriptions</h3>
            <p>Help buyers make informed decisions:</p>
            <ul>
                <li>Mention brand and size</li>
                <li>Describe condition honestly</li>
                <li>Include measurements</li>
                <li>Note any flaws</li>
            </ul>
        </div>
        
        <div class="tip-card">
            <div class="tip-icon"><i class="fas fa-rocket"></i></div>
            <h3>Ship Quickly</h3>
            <p>Fast shipping leads to repeat customers:</p>
            <ul>
                <li>Ship within 1-2 days</li>
                <li>Use tracked shipping</li>
                <li>Package items carefully</li>
                <li>Communicate tracking info</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>