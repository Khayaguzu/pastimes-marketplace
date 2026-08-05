<?php
session_start();
require_once 'DBConn.php';
require_once 'includes/header.php';
?>

<style>
    .how-it-works-container {
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
    
    .process-flow {
        display: flex;
        flex-direction: column;
        gap: 30px;
        margin-bottom: 50px;
    }
    
    .process-step {
        display: flex;
        gap: 30px;
        align-items: center;
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    .step-number {
        width: 60px;
        height: 60px;
        background: #e57e5c;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 700;
        color: white;
    }
    
    .step-content {
        flex: 1;
    }
    
    .step-content h3 {
        font-size: 24px;
        color: #0a2a28;
        margin-bottom: 10px;
    }
    
    .step-content p {
        color: #666;
        line-height: 1.6;
    }
    
    .faq-section {
        background: #f8f9fa;
        border-radius: 30px;
        padding: 40px;
        margin-top: 30px;
    }
    
    .faq-item {
        margin-bottom: 25px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 15px;
    }
    
    .faq-question {
        font-size: 18px;
        font-weight: 600;
        color: #0a2a28;
        margin-bottom: 10px;
        cursor: pointer;
    }
    
    .faq-answer {
        color: #666;
        line-height: 1.6;
        display: block;
    }
    
    @media (max-width: 768px) {
        .process-step {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<div class="how-it-works-container">
    <div class="page-header">
        <h1>How Pastimes Works</h1>
        <p>Your complete guide to buying and selling second-hand fashion</p>
    </div>
    
    <div class="process-flow">
        <div class="process-step">
            <div class="step-number">1</div>
            <div class="step-content">
                <h3>Create Your Account</h3>
                <p>Sign up for free as either a buyer or seller. It takes less than 2 minutes to get started.</p>
            </div>
        </div>
        
        <div class="process-step">
            <div class="step-number">2</div>
            <div class="step-content">
                <h3>Browse or List Items</h3>
                <p>Buyers can explore thousands of second-hand items. Sellers can list their pre-loved clothes with photos and prices.</p>
            </div>
        </div>
        
        <div class="process-step">
            <div class="step-number">3</div>
            <div class="step-content">
                <h3>Make a Purchase or Sale</h3>
                <p>Buyers add items to cart and checkout securely. Sellers receive orders and prepare for shipping.</p>
            </div>
        </div>
        
        <div class="process-step">
            <div class="step-number">4</div>
            <div class="step-content">
                <h3>Delivery & Payment</h3>
                <p>We offer secure payment processing and tracked delivery nationwide.</p>
            </div>
        </div>
    </div>
    
    <div class="faq-section">
        <h2 style="margin-bottom: 25px;">Frequently Asked Questions</h2>
        
        <div class="faq-item">
            <div class="faq-question">How much does it cost to sell?</div>
            <div class="faq-answer">Listing items is completely free! We only charge a small commission when your item sells.</div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">How do I get paid?</div>
            <div class="faq-answer">Payments are processed securely and paid directly to your bank account.</div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">How long does delivery take?</div>
            <div class="faq-answer">Delivery typically takes 3-5 business days depending on your location.</div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>