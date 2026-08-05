<?php
session_start();
require_once 'DBConn.php';
require_once 'includes/header.php';
?>

<style>
    .contact-container {
        max-width: 1000px;
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
    
    .contact-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 40px;
    }
    
    .contact-info {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    .info-item {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 25px;
        padding: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .info-icon {
        width: 50px;
        height: 50px;
        background: #f0f2f5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .info-icon i {
        font-size: 24px;
        color: #e57e5c;
    }
    
    .info-text h4 {
        color: #0a2a28;
        margin-bottom: 5px;
    }
    
    .info-text p {
        color: #666;
    }
    
    .contact-form {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 14px;
    }
    
    .btn-submit {
        background: #0a2a28;
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 40px;
        cursor: pointer;
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="contact-container">
    <div class="page-header">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you</p>
    </div>
    
    <div class="contact-grid">
        <div class="contact-info">
            <h3 style="margin-bottom: 20px;">Get in Touch</h3>
            
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="info-text">
                    <h4>Visit Us</h4>
                    <p>123 Main Street, Cape Town, 8001</p>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-phone"></i></div>
                <div class="info-text">
                    <h4>Call Us</h4>
                    <p>+27 (0) 21 123 4567</p>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                <div class="info-text">
                    <h4>Email Us</h4>
                    <p>hello@pastimes.co.za</p>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-clock"></i></div>
                <div class="info-text">
                    <h4>Business Hours</h4>
                    <p>Mon-Fri: 9am - 5pm</p>
                </div>
            </div>
        </div>
        
        <div class="contact-form">
            <h3 style="margin-bottom: 20px;">Send us a Message</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Your Email" required>
                </div>
                <div class="form-group">
                    <input type="text" name="subject" placeholder="Subject" required>
                </div>
                <div class="form-group">
                    <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
                </div>
                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>