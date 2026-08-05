<?php
// Start output buffering
ob_start();
session_start();
require_once 'DBConn.php';

// Get cart and delivery info
$cart_items = isset($_SESSION['checkout_cart']) ? json_decode($_SESSION['checkout_cart'], true) : [];
$delivery_address = isset($_SESSION['delivery_address']) ? $_SESSION['delivery_address'] : [];

// If no cart items, redirect to shop
if (empty($cart_items)) {
    header('Location: shop.php');
    exit;
}

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * ($item['quantity'] ?? 1);
}
$delivery_fee = 55;
$total = $subtotal + $delivery_fee;

$error = '';
$payment_success = false;
$order_id = null;

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $payment_method = $_POST['payment_method'];
    $card_number = isset($_POST['card_number']) ? $_POST['card_number'] : '';
    $card_expiry = isset($_POST['card_expiry']) ? $_POST['card_expiry'] : '';
    $card_cvv = isset($_POST['card_cvv']) ? $_POST['card_cvv'] : '';
    $card_name = isset($_POST['card_name']) ? $_POST['card_name'] : '';
    $bank_name = isset($_POST['bank_name']) ? $_POST['bank_name'] : '';
    $account_number = isset($_POST['account_number']) ? $_POST['account_number'] : '';
    
    // Validate payment details
    $payment_errors = array();
    
    if ($payment_method == 'card') {
        if (empty($card_number) || strlen(preg_replace('/\s+/', '', $card_number)) < 15) {
            $payment_errors[] = "Please enter a valid card number";
        }
        if (empty($card_expiry)) {
            $payment_errors[] = "Please enter card expiry date";
        }
        if (empty($card_cvv) || strlen($card_cvv) < 3) {
            $payment_errors[] = "Please enter valid CVV";
        }
        if (empty($card_name)) {
            $payment_errors[] = "Please enter cardholder name";
        }
    } elseif ($payment_method == 'eft') {
        if (empty($bank_name)) {
            $payment_errors[] = "Please select your bank";
        }
        if (empty($account_number)) {
            $payment_errors[] = "Please enter your account number";
        }
    }
    
    if (empty($payment_errors)) {
        // Save order to database
        if (isset($_SESSION['user_id'])) {
            $buyer_id = $_SESSION['user_id'];
            $total_amount = $total;
            $delivery_addr = $delivery_address['address_line1'] . ', ' . $delivery_address['city'] . ', ' . $delivery_address['postal_code'];
            
            // Insert into orders table
            $sql = "INSERT INTO tblAorder (buyer_id, total_amount, delivery_address, delivery_city, delivery_postal, payment_method, order_status) 
                    VALUES ('$buyer_id', '$total_amount', '$delivery_addr', '{$delivery_address['city']}', '{$delivery_address['postal_code']}', '$payment_method', 'delivered')";
            
            if (mysqli_query($conn, $sql)) {
                $order_id = mysqli_insert_id($conn);
                
                // Insert each item into order items table
                foreach ($cart_items as $item) {
                    $clothes_id = $item['id'];
                    $quantity = $item['quantity'] ?? 1;
                    $price = $item['price'];
                    
                    $sql = "INSERT INTO tblOrderItems (order_id, clothes_id, quantity, price_at_time) 
                            VALUES ('$order_id', '$clothes_id', '$quantity', '$price')";
                    mysqli_query($conn, $sql);
                    
                    // Get seller_id from clothes table
                    $seller_sql = "SELECT seller_id FROM tblClothes WHERE clothes_id = $clothes_id";
                    $seller_result = mysqli_query($conn, $seller_sql);
                    if ($seller_result && mysqli_num_rows($seller_result) > 0) {
                        $seller = mysqli_fetch_assoc($seller_result);
                        $seller_id = $seller['seller_id'];
                        
                        // Create initial message about the order
                        $message = "Order #$order_id has been placed for your item: {$item['name']}. The buyer will contact you soon.";
                        $msg_sql = "INSERT INTO tblMessages (order_id, from_user_id, to_user_id, message, is_read, created_at) 
                                    VALUES ('$order_id', '$buyer_id', '$seller_id', '$message', 0, NOW())";
                        mysqli_query($conn, $msg_sql);
                    }
                }
                
                $payment_success = true;
                
                // Clear cart and session
                unset($_SESSION['checkout_cart']);
                unset($_SESSION['delivery_address']);
            } else {
                $error = "Failed to save order: " . mysqli_error($conn);
            }
        } else {
            // Guest checkout - still mark as success
            $payment_success = true;
            unset($_SESSION['checkout_cart']);
            unset($_SESSION['delivery_address']);
        }
    } else {
        $error = implode("<br>", $payment_errors);
    }
}

// Include header AFTER all redirects
include 'includes/header.php';
?>

<style>
    .payment-container {
        display: flex;
        gap: 40px;
        margin: 40px 0;
        flex-wrap: wrap;
    }
    
    .payment-methods {
        flex: 2;
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .payment-summary {
        flex: 1;
        background: white;
        padding: 25px;
        border-radius: 20px;
        height: fit-content;
        position: sticky;
        top: 100px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .method-card {
        border: 2px solid #eee;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .method-card:hover {
        border-color: #e57e5c;
        background: #fef9f7;
    }
    
    .method-card.selected {
        border-color: #e57e5c;
        background: #fef9f7;
    }
    
    .method-radio {
        width: 20px;
        height: 20px;
        accent-color: #e57e5c;
    }
    
    .method-icon {
        width: 50px;
        height: 50px;
        background: #f0eeeb;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    
    .method-details {
        flex: 1;
    }
    
    .method-name {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .method-desc {
        font-size: 12px;
        color: #888;
    }
    
    .payment-details {
        margin-top: 20px;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 16px;
        display: none;
    }
    
    .payment-details.active {
        display: block;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #333;
    }
    
    .form-group input, .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
    }
    
    .form-group input:focus, .form-group select:focus {
        outline: none;
        border-color: #e57e5c;
        box-shadow: 0 0 0 3px rgba(229,126,92,0.1);
    }
    
    .pay-btn {
        width: 100%;
        background: #0a2a28;
        color: white;
        padding: 16px;
        border: none;
        border-radius: 40px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 25px;
    }
    
    .pay-btn:hover {
        background: #e57e5c;
        transform: translateY(-2px);
    }
    
    .security-badges {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    
    .security-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #666;
    }
    
    .success-container {
        text-align: center;
        padding: 60px 40px;
        background: white;
        border-radius: 20px;
        margin: 40px 0;
    }
    
    .success-icon {
        width: 80px;
        height: 80px;
        background: #4caf50;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 40px;
        color: white;
    }
    
    .error-msg {
        background: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 20px;
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
        color: #e57e5c;
    }
    
    .step.completed {
        color: #0a2a28;
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
    
    .step.completed .step-number {
        background: #0a2a28;
        color: white;
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
        color: #0a2a28;
        border-top: 2px solid #eee;
        margin-top: 10px;
    }
    
    .message-seller {
        margin-top: 20px;
        text-align: center;
    }
    
    .message-seller-btn {
        background: #0a2a28;
        color: white;
        padding: 10px 20px;
        border-radius: 30px;
        text-decoration: none;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    @media (max-width: 768px) {
        .payment-container {
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

<?php if ($payment_success): ?>
    <!-- Order Success Page -->
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h2>Payment Successful! Order Confirmed!</h2>
        <p style="margin: 15px 0; color: #666;">Thank you for shopping at Pastimes. Your order has been confirmed and is marked as delivered.</p>
        <div style="background: #f9f9f9; padding: 20px; border-radius: 12px; margin: 20px auto; text-align: left; max-width: 400px;">
            <p><strong>Order Number:</strong> #<?php echo str_pad($order_id, 8, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Total Amount:</strong> R<?php echo number_format($total, 2); ?></p>
            <p><strong>Payment Method:</strong> <?php echo strtoupper($payment_method); ?></p>
            <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($delivery_address['address_line1'] . ', ' . $delivery_address['city']); ?></p>
            <p><strong>Order Status:</strong> <span style="color: green;">Delivered ✓</span></p>
        </div>
        <p>A confirmation email has been sent to your email address.</p>
        <div class="message-seller">
            <a href="messages.php" class="message-seller-btn">
                <i class="fas fa-comment-dots"></i> Message Seller About Your Order
            </a>
        </div>
        <div style="margin-top: 30px;">
            <button class="btn btn-primary" onclick="window.location.href='shop.php'">Continue Shopping</button>
            <button class="btn btn-outline" onclick="window.location.href='order_history.php'" style="margin-left: 10px;">View My Orders</button>
        </div>
    </div>
    <script>
        localStorage.removeItem('pastimes_cart');
    </script>
<?php else: ?>
    <div class="progress-steps">
        <div class="step completed">
            <span class="step-number">1</span>
            <span>Cart</span>
        </div>
        <div class="step completed">
            <span class="step-number">2</span>
            <span>Delivery</span>
        </div>
        <div class="step active">
            <span class="step-number">3</span>
            <span>Payment</span>
        </div>
        <div class="step">
            <span class="step-number">4</span>
            <span>Confirm</span>
        </div>
    </div>

    <div class="payment-container">
        <div class="payment-methods">
            <h2 style="margin-bottom: 25px;">Select Payment Method</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="paymentForm">
                <!-- Credit/Debit Card Option -->
                <div class="method-card" onclick="selectMethod('card')">
                    <input type="radio" name="payment_method" value="card" id="card" class="method-radio" checked>
                    <div class="method-icon"><i class="fab fa-cc-visa"></i></div>
                    <div class="method-details">
                        <div class="method-name">Credit/Debit Card</div>
                        <div class="method-desc">Visa, Mastercard, American Express accepted</div>
                    </div>
                    <div class="method-fee">No fee</div>
                </div>
                
                <!-- Card Details Section -->
                <div id="cardDetails" class="payment-details active">
                    <h4 style="margin-bottom: 15px;">Card Details</h4>
                    <div class="form-group">
                        <label>Card Number *</label>
                        <input type="text" name="card_number" id="card_number" placeholder="1234 5678 9012 3456" class="form-control">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Expiry Date *</label>
                            <input type="text" name="card_expiry" id="card_expiry" placeholder="MM/YY" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>CVV *</label>
                            <input type="text" name="card_cvv" id="card_cvv" placeholder="123" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Cardholder Name *</label>
                        <input type="text" name="card_name" id="card_name" placeholder="Name on card" class="form-control">
                    </div>
                </div>
                
                <!-- Instant EFT Option -->
                <div class="method-card" onclick="selectMethod('eft')">
                    <input type="radio" name="payment_method" value="eft" id="eft" class="method-radio">
                    <div class="method-icon"><i class="fas fa-university"></i></div>
                    <div class="method-details">
                        <div class="method-name">Instant EFT</div>
                        <div class="method-desc">Pay directly from your bank account</div>
                    </div>
                    <div class="method-fee">No fee</div>
                </div>
                
                <!-- EFT Details Section -->
                <div id="eftDetails" class="payment-details">
                    <h4 style="margin-bottom: 15px;">Bank Account Details</h4>
                    <div class="form-group">
                        <label>Select Your Bank *</label>
                        <select name="bank_name" id="bank_name" class="form-control">
                            <option value="">-- Select Bank --</option>
                            <option value="absa">ABSA Bank</option>
                            <option value="fnb">FNB (First National Bank)</option>
                            <option value="standard">Standard Bank</option>
                            <option value="nedbank">Nedbank</option>
                            <option value="capitec">Capitec Bank</option>
                            <option value="african">African Bank</option>
                            <option value="discovery">Discovery Bank</option>
                            <option value="tyme">TymeBank</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Account Number *</label>
                        <input type="text" name="account_number" id="account_number" placeholder="Your bank account number" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Account Holder Name</label>
                        <input type="text" name="account_holder" id="account_holder" placeholder="Name on account" class="form-control">
                    </div>
                </div>
                
                <!-- Payflex Option -->
                <div class="method-card" onclick="selectMethod('payflex')">
                    <input type="radio" name="payment_method" value="payflex" id="payflex" class="method-radio">
                    <div class="method-icon"><i class="fas fa-credit-card"></i></div>
                    <div class="method-details">
                        <div class="method-name">Payflex</div>
                        <div class="method-desc">Pay in 4 interest-free instalments</div>
                    </div>
                    <div class="method-fee">0% interest</div>
                </div>
                
                <!-- Cash on Delivery Option -->
                <div class="method-card" onclick="selectMethod('cod')">
                    <input type="radio" name="payment_method" value="cod" id="cod" class="method-radio">
                    <div class="method-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="method-details">
                        <div class="method-name">Cash on Delivery</div>
                        <div class="method-desc">Pay when you receive your order</div>
                    </div>
                    <div class="method-fee">R20 fee</div>
                </div>
                
                <input type="hidden" name="process_payment" value="1">
                <button type="submit" class="pay-btn" onclick="return validatePayment()">
                    <i class="fas fa-lock"></i> Pay R<?php echo number_format($total, 2); ?>
                </button>
                
                <div class="security-badges">
                    <div class="security-badge"><i class="fas fa-shield-alt"></i> SSL Secure</div>
                    <div class="security-badge"><i class="fas fa-lock"></i> PCI Compliant</div>
                    <div class="security-badge"><i class="fas fa-check-circle"></i> Verified</div>
                </div>
            </form>
        </div>
        
        <div class="payment-summary">
            <h3 style="margin-bottom: 20px;">Order Summary</h3>
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
                <small><i class="fas fa-map-marker-alt"></i> Delivering to:</small>
                <p style="margin-top: 8px; font-size: 13px;">
                    <?php echo htmlspecialchars($delivery_address['full_name'] ?? 'Guest'); ?><br>
                    <?php echo htmlspecialchars($delivery_address['address_line1'] ?? ''); ?><br>
                    <?php echo htmlspecialchars(($delivery_address['city'] ?? '') . ', ' . ($delivery_address['postal_code'] ?? '')); ?>
                </p>
            </div>
        </div>
    </div>

    <script>
        function selectMethod(method) {
            // Update radio buttons
            document.getElementById('card').checked = (method === 'card');
            document.getElementById('eft').checked = (method === 'eft');
            document.getElementById('payflex').checked = (method === 'payflex');
            document.getElementById('cod').checked = (method === 'cod');
            
            // Show/hide payment details
            const cardDetails = document.getElementById('cardDetails');
            const eftDetails = document.getElementById('eftDetails');
            
            if (method === 'card') {
                cardDetails.classList.add('active');
                eftDetails.classList.remove('active');
            } else if (method === 'eft') {
                cardDetails.classList.remove('active');
                eftDetails.classList.add('active');
            } else {
                cardDetails.classList.remove('active');
                eftDetails.classList.remove('active');
            }
            
            // Update selected styling
            document.querySelectorAll('.method-card').forEach(card => {
                card.classList.remove('selected');
            });
            if (event && event.currentTarget) {
                event.currentTarget.classList.add('selected');
            }
        }
        
        function validatePayment() {
            // Get the selected payment method
            let paymentMethod = '';
            if (document.getElementById('card').checked) paymentMethod = 'card';
            else if (document.getElementById('eft').checked) paymentMethod = 'eft';
            else if (document.getElementById('payflex').checked) paymentMethod = 'payflex';
            else if (document.getElementById('cod').checked) paymentMethod = 'cod';
            
            if (paymentMethod === 'card') {
                const cardNumber = document.getElementById('card_number').value;
                const cardExpiry = document.getElementById('card_expiry').value;
                const cardCvv = document.getElementById('card_cvv').value;
                const cardName = document.getElementById('card_name').value;
                
                if (!cardNumber || cardNumber.replace(/\s/g, '').length < 15) {
                    alert('Please enter a valid card number');
                    return false;
                }
                if (!cardExpiry) {
                    alert('Please enter card expiry date');
                    return false;
                }
                if (!cardCvv || cardCvv.length < 3) {
                    alert('Please enter valid CVV');
                    return false;
                }
                if (!cardName) {
                    alert('Please enter cardholder name');
                    return false;
                }
            } else if (paymentMethod === 'eft') {
                const bankName = document.getElementById('bank_name').value;
                const accountNumber = document.getElementById('account_number').value;
                
                if (!bankName) {
                    alert('Please select your bank');
                    return false;
                }
                if (!accountNumber) {
                    alert('Please enter your account number');
                    return false;
                }
            }
            
            // Allow form to submit
            return true;
        }
        
        // Initialize - show card details by default
        document.getElementById('cardDetails').classList.add('active');
        
        // Add click handlers to method cards
        document.querySelectorAll('.method-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking on the radio button directly
                if (e.target.type !== 'radio') {
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;
                }
            });
        });
    </script>
<?php endif; ?>

<?php 
require_once 'includes/footer.php';
ob_end_flush();
?>