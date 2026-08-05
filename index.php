<?php
/**
 * Homepage (index.php)
 * Landing page for Pastimes marketplace
 * Displays hero section and featured products
 */

// Start output buffering to prevent header errors
ob_start();

// Start session and include database connection
session_start();
require_once 'DBConn.php';
require_once 'includes/header.php';
?>

<style>
    /* Hero Section Styles */
    .hero {
        background: linear-gradient(135deg, #0a2a28 0%, #1a4a47 100%);
        border-radius: 40px;
        padding: 80px 60px;
        color: #ffffff;
        margin: 30px 0 50px;
    }
    
    .hero h1 {
        font-size: 48px;
        font-weight: 800;
        margin-bottom: 20px;
        line-height: 1.2;
    }
    
    .hero p {
        font-size: 18px;
        margin-bottom: 30px;
        opacity: 0.9;
    }
    
    .hero-buttons {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .btn-outline-light {
        background: transparent;
        border: 2px solid white;
        color: white;
        padding: 14px 34px;
        border-radius: 40px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
    }
    
    .btn-outline-light:hover {
        background: rgba(255,255,255,0.1);
        transform: translateY(-2px);
    }
    
    /* Trust Bar Styles */
    .trust-bar {
        display: flex;
        justify-content: space-around;
        background: white;
        border-radius: 60px;
        padding: 20px 30px;
        margin: 40px 0;
        flex-wrap: wrap;
        gap: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .trust-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 500;
        color: #1a1a1a;
    }
    
    .trust-item i {
        font-size: 18px;
        color: #0a2a28;
    }
    
    /* Featured Products Grid */
    .featured-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin: 50px 0;
    }
    
    .feature-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--shadow);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
    }
    
    .feature-card img {
        width: 100%;
        aspect-ratio: 1/1;
        object-fit: cover;
    }
    
    .feature-card .info {
        padding: 20px;
        text-align: center;
    }
    
    .feature-card h3 {
        color: var(--primary);
        margin-bottom: 8px;
        font-size: 20px;
    }
    
    .feature-card p {
        color: #666;
        font-size: 14px;
    }
    
    /* How It Works Section */
    .how-it-works {
        background: white;
        border-radius: 40px;
        padding: 50px;
        margin: 50px 0;
        text-align: center;
    }
    
    .section-title {
        font-size: 32px;
        margin-bottom: 40px;
        color: #0a2a28;
        font-weight: 700;
    }
    
    .steps-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 40px;
    }
    
    .step-card {
        text-align: center;
        padding: 20px;
    }
    
    .step-icon {
        width: 80px;
        height: 80px;
        background: #f5f3f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 32px;
        color: #e57e5c;
    }
    
    .step-card h3 {
        margin-bottom: 10px;
        color: #0a2a28;
        font-size: 20px;
    }
    
    .step-card p {
        color: #666;
        line-height: 1.5;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 900px) {
        .hero {
            padding: 50px 30px;
        }
        .hero h1 {
            font-size: 32px;
        }
        .featured-grid {
            grid-template-columns: 1fr;
        }
        .steps-grid {
            grid-template-columns: 1fr;
            gap: 30px;
        }
        .how-it-works {
            padding: 30px 20px;
        }
    }
</style>

<!-- Hero Banner Section -->
<div class="hero">
    <h1>Buy better brands for less</h1>
    <p>Sell what you don't wear · second‑hand from R50–R400</p>
    <div class="hero-buttons">
        <a href="shop.php" class="btn btn-primary">Start Shopping</a>
        <a href="register.php" class="btn-outline-light">Start Selling</a>
    </div>
</div>

<!-- Trust Indicators -->
<div class="trust-bar">
    <div class="trust-item"><i class="fas fa-check-circle"></i> <span>Verified Sellers</span></div>
    <div class="trust-item"><i class="fas fa-lock"></i> <span>Secure Payments</span></div>
    <div class="trust-item"><i class="fas fa-truck"></i> <span>Free Delivery Over R500</span></div>
    <div class="trust-item"><i class="fas fa-undo-alt"></i> <span>7-Day Returns</span></div>
</div>

<!-- Featured Collections -->
<h2 style="font-size: 28px; margin: 40px 0 20px; text-align: center;">✨ Featured Collections</h2>
<p style="text-align: center; color: #666; margin-bottom: 30px;">Discover curated second-hand fashion from trusted sellers</p>

<div class="featured-grid">
    <!-- Vintage Denim Collection -->
    <div class="feature-card" onclick="window.location.href='shop.php'">
        <img src="https://images.pexels.com/photos/1598507/jeans-fashion-style-clothing-1598507.jpg?w=400" alt="Vintage Jeans">
        <div class="info">
            <h3>Vintage Denim</h3>
            <p>Authentic 90s styles from R150</p>
        </div>
    </div>
    
    <!-- Summer Dresses Collection -->
    <div class="feature-card" onclick="window.location.href='shop.php'">
        <img src="https://images.pexels.com/photos/1034465/pexels-photo-1034465.jpeg?w=400" alt="Summer Dresses">
        <div class="info">
            <h3>Summer Dresses</h3>
            <p>Floral prints & casual styles</p>
        </div>
    </div>
    
    <!-- Pre-loved Sneakers Collection -->
    <div class="feature-card" onclick="window.location.href='shop.php'">
        <img src="https://images.pexels.com/photos/2529148/pexels-photo-2529148.jpeg?w=400" alt="Sneakers">
        <div class="info">
            <h3>Pre-loved Sneakers</h3>
            <p>Nike, Adidas & more from R200</p>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div class="how-it-works">
    <h2 class="section-title">How It Works</h2>
    <div class="steps-grid">
        <div class="step-card">
            <div class="step-icon"><i class="fas fa-upload"></i></div>
            <h3>1. List for Free</h3>
            <p>Upload your pre-loved clothes with photos and price - no listing fees!</p>
        </div>
        <div class="step-card">
            <div class="step-icon"><i class="fas fa-shopping-cart"></i></div>
            <h3>2. Buy Securely</h3>
            <p>Browse thousands of second-hand items from trusted sellers across SA</p>
        </div>
        <div class="step-card">
            <div class="step-icon"><i class="fas fa-truck-fast"></i></div>
            <h3>3. Delivered to Door</h3>
            <p>Fast courier delivery anywhere in South Africa</p>
        </div>
    </div>
</div>

<?php
// Include footer and flush output buffer
require_once 'includes/footer.php';
ob_end_flush();
?>