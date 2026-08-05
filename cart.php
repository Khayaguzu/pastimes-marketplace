<?php
/**
 * Shopping Cart Page (cart.php)
 * Displays items added to cart and allows quantity updates
 */

ob_start();
session_start();
require_once 'DBConn.php';
require_once 'includes/header.php';
?>

<style>
    .cart-container {
        display: flex;
        gap: 40px;
        margin: 40px 0;
        flex-wrap: wrap;
    }
    
    .cart-items {
        flex: 2;
    }
    
    .cart-summary {
        flex: 1;
        background: white;
        padding: 25px;
        border-radius: 20px;
        height: fit-content;
        position: sticky;
        top: 100px;
        box-shadow: var(--shadow);
    }
    
    .cart-item {
        display: flex;
        gap: 20px;
        background: white;
        padding: 20px;
        border-radius: 16px;
        margin-bottom: 15px;
        box-shadow: var(--shadow);
        align-items: center;
        flex-wrap: wrap;
    }
    
    .cart-item-img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 12px;
    }
    
    .cart-item-details {
        flex: 1;
    }
    
    .cart-item-name {
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 5px;
    }
    
    .cart-item-price {
        color: var(--accent);
        font-weight: 600;
        font-size: 18px;
    }
    
    .cart-item-quantity {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
    }
    
    .quantity-btn {
        width: 30px;
        height: 30px;
        background: #f0eeeb;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.2s;
    }
    
    .quantity-btn:hover {
        background: var(--accent);
        color: white;
    }
    
    .remove-btn {
        background: none;
        border: none;
        color: #ff6b6b;
        cursor: pointer;
        font-size: 20px;
        transition: color 0.2s;
    }
    
    .remove-btn:hover {
        color: #ff4444;
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
    
    .checkout-btn {
        width: 100%;
        background: var(--accent);
        color: white;
        padding: 15px;
        border: none;
        border-radius: 40px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 20px;
        transition: all 0.2s;
    }
    
    .checkout-btn:hover {
        background: #c96a4a;
        transform: translateY(-2px);
    }
    
    .clear-btn {
        width: 100%;
        background: #f0eeeb;
        padding: 12px;
        border: none;
        border-radius: 40px;
        cursor: pointer;
        margin-top: 10px;
        transition: all 0.2s;
    }
    
    .clear-btn:hover {
        background: #ddd;
    }
    
    .empty-cart {
        text-align: center;
        padding: 60px;
        background: white;
        border-radius: 20px;
        width: 100%;
    }
    
    .empty-cart i {
        font-size: 64px;
        color: #ccc;
        margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
        .cart-container {
            flex-direction: column;
        }
        .cart-item {
            flex-direction: column;
            text-align: center;
        }
        .cart-item-img {
            width: 150px;
            height: 150px;
        }
    }
</style>

<div class="cart-container" id="cartContainer">
    <!-- Cart items will be loaded here by JavaScript -->
</div>

<script>
    /**
     * Load and display cart items from localStorage
     */
    function loadCart() {
        let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');
        const container = document.getElementById('cartContainer');
        
        if (cart.length === 0) {
            container.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p>Looks like you haven't added anything to your cart yet.</p>
                    <button class="btn btn-primary" onclick="window.location.href='shop.php'">Continue Shopping</button>
                </div>
            `;
            return;
        }
        
        let subtotal = 0;
        let itemsHtml = '<div class="cart-items">';
        
        cart.forEach((item, index) => {
            const quantity = item.quantity || 1;
            const itemTotal = item.price * quantity;
            subtotal += itemTotal;
            
            itemsHtml += `
                <div class="cart-item">
                    <img src="${item.image}" class="cart-item-img" onerror="this.src='https://images.pexels.com/photos/1598507/jeans-fashion-style-clothing-1598507.jpg?w=100'">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${escapeHtml(item.name)}</div>
                        <div class="cart-item-price">R${item.price.toFixed(2)}</div>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">-</button>
                            <span>${quantity}</span>
                            <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div class="cart-item-price">R${itemTotal.toFixed(2)}</div>
                        <button class="remove-btn" onclick="removeFromCart(${index})"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `;
        });
        
        const deliveryFee = subtotal > 500 ? 0 : 55;
        const total = subtotal + deliveryFee;
        
        itemsHtml += '</div>';
        itemsHtml += `
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-item">
                    <span>Subtotal</span>
                    <span>R${subtotal.toFixed(2)}</span>
                </div>
                <div class="summary-item">
                    <span>Delivery Fee</span>
                    <span>${deliveryFee === 0 ? 'FREE' : 'R' + deliveryFee.toFixed(2)}</span>
                </div>
                ${subtotal > 500 ? '<div class="summary-item" style="color: green;"><span>✨ Free Delivery Applied</span><span>🎉</span></div>' : ''}
                <div class="summary-total">
                    <span>Total</span>
                    <span>R${total.toFixed(2)}</span>
                </div>
                <button class="checkout-btn" onclick="proceedToCheckout()">Proceed to Checkout →</button>
                <button class="clear-btn" onclick="clearCart()">Clear Cart</button>
            </div>
        `;
        
        container.innerHTML = itemsHtml;
        updateCartCount();
    }
    
    /**
     * Update item quantity in cart
     * @param {number} index - Index of item in cart array
     * @param {number} change - Change amount (+1 or -1)
     */
    function updateQuantity(index, change) {
        let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');
        if (cart[index]) {
            let newQuantity = (cart[index].quantity || 1) + change;
            if (newQuantity >= 1) {
                cart[index].quantity = newQuantity;
            } else {
                cart.splice(index, 1);
            }
            localStorage.setItem('pastimes_cart', JSON.stringify(cart));
            loadCart();
            updateCartCount();
            showNotification('Cart updated', 'success');
        }
    }
    
    /**
     * Remove item from cart
     * @param {number} index - Index of item to remove
     */
    function removeFromCart(index) {
        let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');
        cart.splice(index, 1);
        localStorage.setItem('pastimes_cart', JSON.stringify(cart));
        loadCart();
        updateCartCount();
        showNotification('Item removed from cart', 'info');
    }
    
    /**
     * Clear entire cart
     */
    function clearCart() {
        if (confirm('Are you sure you want to clear your cart?')) {
            localStorage.removeItem('pastimes_cart');
            loadCart();
            updateCartCount();
            showNotification('Cart cleared', 'info');
        }
    }
    
    /**
     * Proceed to checkout - redirect to checkout page
     */
    function proceedToCheckout() {
        let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');
        if (cart.length === 0) {
            showNotification('Your cart is empty!', 'info');
            return;
        }
        
        // Store cart in session via form submission
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = 'checkout.php';
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'cart_data';
        input.value = JSON.stringify(cart);
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
    
    /**
     * Escape HTML to prevent XSS
     * @param {string} str - String to escape
     * @returns {string} - Escaped string
     */
    function escapeHtml(str) {
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    // Load cart on page load
    document.addEventListener('DOMContentLoaded', loadCart);
</script>

<?php
require_once 'includes/footer.php';
ob_end_flush();
?>