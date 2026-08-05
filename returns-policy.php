<?php
session_start();
require_once 'DBConn.php';
require_once 'includes/header.php';
?>

<style>
    .policy-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .page-header h1 {
        font-size: 42px;
        color: #0a2a28;
        margin-bottom: 15px;
    }
    
    .policy-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    .policy-card h2 {
        color: #0a2a28;
        margin: 25px 0 15px;
    }
    
    .policy-card h2:first-child {
        margin-top: 0;
    }
    
    .policy-card p {
        color: #666;
        line-height: 1.6;
        margin-bottom: 15px;
    }
    
    .policy-card ul {
        padding-left: 20px;
        margin-bottom: 20px;
    }
    
    .policy-card li {
        color: #666;
        margin-bottom: 8px;
    }
</style>

<div class="policy-container">
    <div class="page-header">
        <h1>Returns Policy</h1>
        <p>Our commitment to your satisfaction</p>
    </div>
    
    <div class="policy-card">
        <h2>7-Day Return Policy</h2>
        <p>We want you to be completely satisfied with your purchase. If you're not happy with your item, you can return it within 7 days of delivery.</p>
        
        <h2>Return Conditions</h2>
        <ul>
            <li>Items must be unworn and unwashed</li>
            <li>Original tags must still be attached</li>
            <li>Items must be in original packaging</li>
            <li>Proof of purchase is required</li>
        </ul>
        
        <h2>How to Return an Item</h2>
        <ul>
            <li>Contact our support team within 7 days of delivery</li>
            <li>Provide your order number and reason for return</li>
            <li>Pack the item securely in original packaging</li>
            <li>Ship the item back using tracked shipping</li>
        </ul>
        
        <h2>Refunds</h2>
        <p>Once we receive and inspect your return, we will process your refund within 5-7 business days. Refunds will be issued to your original payment method.</p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>