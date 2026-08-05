<?php
/**
 * Checkout Page (checkout.php)
 * Collects delivery address information before payment
 */

ob_start();
session_start();
require_once 'DBConn.php';

// Get cart items from POST or session
$cart_items = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_data'])) {
    $_SESSION['checkout_cart'] = $_POST['cart_data'];
    $cart_items = json_decode($_POST['cart_data'], true);
} elseif (isset($_SESSION['checkout_cart'])) {
    $cart_items = json_decode($_SESSION['checkout_cart'], true);
}

// If no cart items, redirect to shop
if (empty($cart_items)) {
    header('Location: shop.php');
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * ($item['quantity'] ?? 1);
}
$delivery_fee = 55;
$total = $subtotal + $delivery_fee;

// Handle address form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_address'])) {
    $address_data = [
        'full_name' => $_POST['full_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address_line1' => $_POST['address_line1'] ?? '',
        'address_line2' => $_POST['address_line2'] ?? '',
        'city' => $_POST['city'] ?? '',
        'postal_code' => $_POST['postal_code'] ?? '',
        'province' => $_POST['province'] ?? '',
        'delivery_instructions' => $_POST['delivery_instructions'] ?? ''
    ];
    
    $_SESSION['delivery_address'] = $address_data;
    
    // Redirect to payment page
    header('Location: payment.php');
    exit;
}

require_once 'includes/header.php';
?>

<style>
    .checkout-container {
        display: flex;
        gap: 40px;
        margin: 40px 0;
        flex-wrap: wrap;
    }
    
    .checkout-form {
        flex: 2;
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: var(--shadow);
    }
    
    .order-summary {
        flex: 1;
        background: white;
        padding: 25px;
        border-radius: 20px;
        height: fit-content;
        position: sticky;
        top: 100px;
        box-shadow: var(--shadow);
    }
    
    .form-section {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .form-section h3 {
        color: var(--primary);
        margin-bottom: 20px;
        font-size: 18px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 15px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        font-size: 14px;
        color: #333;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(229,126,92,0.1);
    }
    
    .required::after {
        content: " *";
        color: red;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }
    
    .summary-total {
        display: flex;
        justify-content: space-between;
        padding: 15px 0;
        font-size: 20px;
        font-weight: 700;
        color: var(--primary);
        border-top: 2px solid #eee;
        margin-top: 10px;
    }
    
    .cart-item {
        display: flex;
        gap: 15px;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .cart-item-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
    }
    
    .cart-item-details {
        flex: 1;
    }
    
    .cart-item-name {
        font-weight: 500;
        font-size: 14px;
    }
    
    .cart-item-price {
        color: var(--accent);
        font-weight: 600;
        font-size: 14px;
    }
    
    .continue-btn {
        width: 100%;
        background: var(--accent);
        color: white;
        padding: 15px;
        border: none;
        border-radius: 40px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 20px;
    }
    
    .continue-btn:hover {
        background: #c96a4a;
        transform: translateY(-2px);
    }
    
    .back-link {
        display: inline-block;
        margin-top: 20px;
        color: #666;
        text-decoration: none;
    }
    
    .back-link:hover {
        color: var(--accent);
    }
    
    .progress-steps {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border-radius: 50px;
    }
    
    .step {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #ccc;
    }
    
    .step.active {
        color: var(--accent);
    }
    
    .step.completed {
        color: var(--primary);
    }
    
    .step-number {
        width: 30px;
        height: 30px;
        background: #f0eeeb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
    }
    
    .step.active .step-number {
        background: var(--accent);
        color: white;
    }
    
    .step.completed .step-number {
        background: var(--primary);
        color: white;
    }
    
    @media (max-width: 768px) {
        .checkout-container {
            flex-direction: column;
        }
        .form-row {
            grid-template-columns: 1fr;
        }
        .progress-steps {
            flex-wrap: wrap;
        }
    }
</style>

<div class="progress-steps">
    <div class="step completed">
        <span class="step-number">1</span>
        <span>Cart</span>
    </div>
    <div class="step active">
        <span class="step-number">2</span>
        <span>Delivery</span>
    </div>
    <div class="step">
        <span class="step-number">3</span>
        <span>Payment</span>
    </div>
    <div class="step">
        <span class="step-number">4</span>
        <span>Confirm</span>
    </div>
</div>

<div class="checkout-container">
    <!-- Delivery Form -->
    <div class="checkout-form">
        <form method="POST" action="" id="deliveryForm">
            <div class="form-section">
                <h3>Contact Information</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Full Name</label>
                        <input type="text" name="full_name" required value="<?php echo isset($_SESSION['delivery_address']['full_name']) ? htmlspecialchars($_SESSION['delivery_address']['full_name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="required">Email Address</label>
                        <input type="email" name="email" required value="<?php echo isset($_SESSION['delivery_address']['email']) ? htmlspecialchars($_SESSION['delivery_address']['email']) : ''; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="required">Phone Number</label>
                    <input type="tel" name="phone" required placeholder="071 234 5678" value="<?php echo isset($_SESSION['delivery_address']['phone']) ? htmlspecialchars($_SESSION['delivery_address']['phone']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-section">
                <h3>Delivery Address</h3>
                <div class="form-group">
                    <label class="required">Address Line 1</label>
                    <input type="text" name="address_line1" required placeholder="Street address, P.O. Box" value="<?php echo isset($_SESSION['delivery_address']['address_line1']) ? htmlspecialchars($_SESSION['delivery_address']['address_line1']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Address Line 2 (Optional)</label>
                    <input type="text" name="address_line2" placeholder="Apartment, suite, unit, etc." value="<?php echo isset($_SESSION['delivery_address']['address_line2']) ? htmlspecialchars($_SESSION['delivery_address']['address_line2']) : ''; ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">City</label>
                        <input type="text" name="city" required value="<?php echo isset($_SESSION['delivery_address']['city']) ? htmlspecialchars($_SESSION['delivery_address']['city']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="required">Postal Code</label>
                        <input type="text" name="postal_code" required value="<?php echo isset($_SESSION['delivery_address']['postal_code']) ? htmlspecialchars($_SESSION['delivery_address']['postal_code']) : ''; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="required">Province</label>
                    <select name="province" required>
                        <option value="">Select Province</option>
                        <option value="Western Cape" <?php echo (isset($_SESSION['delivery_address']['province']) && $_SESSION['delivery_address']['province'] == 'Western Cape') ? 'selected' : ''; ?>>Western Cape</option>
                        <option value="Gauteng" <?php echo (isset($_SESSION['delivery_address']['province']) && $_SESSION['delivery_address']['province'] == 'Gauteng') ? 'selected' : ''; ?>>Gauteng</option>
                        <option value="KwaZulu-Natal" <?php echo (isset($_SESSION['delivery_address']['province']) && $_SESSION['delivery_address']['province'] == 'KwaZulu-Natal') ? 'selected' : ''; ?>>KwaZulu-Natal</option>
                        <option value="Eastern Cape" <?php echo (isset($_SESSION['delivery_address']['province']) && $_SESSION['delivery_address']['province'] == 'Eastern Cape') ? 'selected' : ''; ?>>Eastern Cape</option>
                        <option value="Free State" <?php echo (isset($_SESSION['delivery_address']['province']) && $_SESSION['delivery_address']['province'] == 'Free State') ? 'selected' : ''; ?>>Free State</option>
                        <option value="Mpumalanga" <?php echo (isset($_SESSION['delivery_address']['province']) && $_SESSION['delivery_address']['province'] == 'Mpumalanga') ? 'selected' : ''; ?>>Mpumalanga</option>
                        <option value="Limpopo" <?php echo (isset($_SESSION['delivery_address']['province']) && $_SESSION['delivery_address']['province'] == 'Limpopo') ? 'selected' : ''; ?>>Limpopo</option>
                        <option value="North West" <?php echo (isset($_SESSION['delivery_address']['province']) && $_SESSION['delivery_address']['province'] == 'North West') ? 'selected' : ''; ?>>North West</option>
                        <option value="Northern Cape" <?php echo (isset($_SESSION['delivery_address']['province']) && $_SESSION['delivery_address']['province'] == 'Northern Cape') ? 'selected' : ''; ?>>Northern Cape</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Delivery Instructions (Optional)</label>
                    <textarea name="delivery_instructions" rows="3" placeholder="Gate code, landmark, special instructions..."><?php echo isset($_SESSION['delivery_address']['delivery_instructions']) ? htmlspecialchars($_SESSION['delivery_address']['delivery_instructions']) : ''; ?></textarea>
                </div>
            </div>
            
            <input type="hidden" name="save_address" value="1">
            <button type="submit" class="continue-btn">Continue to Payment →</button>
        </form>
        <a href="cart.php" class="back-link">← Back to Cart</a>
    </div>
    
    <!-- Order Summary -->
    <div class="order-summary">
        <h3 style="margin-bottom: 20px;">Order Summary</h3>
        <?php foreach ($cart_items as $item): ?>
            <div class="cart-item">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" class="cart-item-img" onerror="this.src='https://images.pexels.com/photos/1598507/jeans-fashion-style-clothing-1598507.jpg?w=100'">
                <div class="cart-item-details">
                    <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div class="cart-item-price">R<?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity'] ?? 1; ?></div>
                </div>
                <div>R<?php echo number_format($item['price'] * ($item['quantity'] ?? 1), 2); ?></div>
            </div>
        <?php endforeach; ?>
        
        <div class="summary-item">
            <span>Subtotal</span>
            <span>R<?php echo number_format($subtotal, 2); ?></span>
        </div>
        <div class="summary-item">
            <span>Delivery Fee</span>
            <span>R<?php echo number_format($delivery_fee, 2); ?></span>
        </div>
        <div class="summary-total">
            <span>Total</span>
            <span>R<?php echo number_format($total, 2); ?></span>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 12px;">
            <small><i class="fas fa-truck"></i> Estimated delivery: 3-5 business days</small>
        </div>
    </div>
</div>

<?php 
require_once 'includes/footer.php';
ob_end_flush();
?>