<?php
/**
 * Website Footer Component
 * This file contains the footer and closing HTML tags
 * Included at the bottom of every page
 */
?>
    </main>
    
    <!-- Footer Section -->
    <footer class="pastimes-footer">
        <div class="footer-container">
            <!-- Footer Grid - 5 Columns -->
            <div class="footer-grid">
                <!-- Column 1: Brand Info -->
                <div class="footer-col">
                    <h3>pastimes<span>.</span></h3>
                    <p class="footer-about">South Africa's trusted marketplace for pre-loved fashion. Give your clothes a second life.</p>
                    <div class="footer-contact">
                        <p><i class="fas fa-envelope"></i> hello@pastimes.co.za</p>
                        <p><i class="fas fa-phone"></i> +27 (0) 21 123 4567</p>
                    </div>
                </div>

                <!-- Column 2: Shop Links (UPDATED with gender filters) -->
                <div class="footer-col">
                    <h4>Shop</h4>
                    <ul>
                        <li><a href="shop.php">🛍️ All Items</a></li>
                        <li><a href="shop.php?gender=men">👨 Men's Fashion</a></li>
                        <li><a href="shop.php?gender=women">👩 Women's Fashion</a></li>
                        <li><a href="shop.php?gender=unisex">🔄 Vintage Collection</a></li>
                    </ul>
                </div>

                <!-- Column 3: Sell Links -->
                <div class="footer-col">
                    <h4>Sell</h4>
                    <ul>
                        <li><a href="sell.php">💰 Start Selling</a></li>
                        <li><a href="how-it-works.php">❓ How It Works</a></li>
                        <li><a href="selling-tips.php">💡 Selling Tips</a></li>
                        <li><a href="shipping-guide.php">📦 Shipping Guide</a></li>
                    </ul>
                </div>

                <!-- Column 4: Support Links -->
                <div class="footer-col">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="help-centre.php">📚 Help Centre</a></li>
                        <li><a href="contact.php">📧 Contact Us</a></li>
                        <li><a href="returns-policy.php">🔄 Returns Policy</a></li>
                        <li><a href="safety-tips.php">🛡️ Safety Tips</a></li>
                    </ul>
                </div>

                <!-- Column 5: Social Connect -->
                <div class="footer-col">
                    <h4>Connect</h4>
                    <div class="social-links">
                        <a href="https://www.instagram.com/" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.facebook.com/" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.tiktok.com/" target="_blank"><i class="fab fa-tiktok"></i></a>
                        <a href="https://twitter.com/" target="_blank"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>

            <!-- Copyright Bottom Bar -->
            <div class="footer-bottom">
                <p>&copy; 2026 Pastimes. All rights reserved.</p>
                <div class="footer-links">
                    <a href="terms.php">Terms</a>
                    <a href="privacy.php">Privacy</a>
                    <a href="cookies.php">Cookies</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Footer Styles -->
    <style>
        .pastimes-footer {
            background: #0a2a28;
            color: #ffffff;
            padding: 50px 0 20px;
            margin-top: 60px;
        }
        .footer-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 30px;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 30px;
            margin-bottom: 40px;
            padding-bottom: 40px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        @media (max-width: 1000px) {
            .footer-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 700px) {
            .footer-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 500px) {
            .footer-grid { grid-template-columns: 1fr; }
        }
        .footer-col h3 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .footer-col h3 span { color: #e57e5c; }
        .footer-col h4 {
            font-size: 18px;
            font-weight: 600;
            color: #e57e5c;
            margin-bottom: 20px;
        }
        .footer-about {
            color: #b0b0b0;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .footer-contact p {
            color: #b0b0b0;
            font-size: 13px;
            margin: 8px 0;
        }
        .footer-contact i {
            color: #e57e5c;
            width: 25px;
        }
        .footer-col ul {
            list-style: none;
            padding: 0;
        }
        .footer-col ul li {
            margin-bottom: 12px;
        }
        .footer-col ul li a {
            color: #b0b0b0;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .footer-col ul li a:hover {
            color: #e57e5c;
            padding-left: 5px;
        }
        .social-links {
            display: flex;
            gap: 15px;
        }
        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.2s;
        }
        .social-links a:hover {
            background: #e57e5c;
            transform: translateY(-3px);
        }
        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            padding-top: 10px;
        }
        .footer-bottom p {
            color: #8a8a8a;
            font-size: 12px;
        }
        .footer-links {
            display: flex;
            gap: 25px;
        }
        .footer-links a {
            color: #8a8a8a;
            text-decoration: none;
            font-size: 12px;
        }
        .footer-links a:hover {
            color: #e57e5c;
        }
        @media (max-width: 768px) {
            .footer-container {
                padding: 0 20px;
            }
            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>

    <!-- JavaScript Functions -->
    <script>
        /**
         * Add item to shopping cart (localStorage)
         * @param {number} id - Product ID
         * @param {string} name - Product name
         * @param {number} price - Product price
         * @param {string} image - Product image URL
         */
        function addToCart(id, name, price, image) {
            // Get existing cart from localStorage or initialize empty array
            let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');
            
            // Check if product already exists in cart
            let existing = cart.find(item => item.id === id);
            
            if (existing) {
                // Increment quantity if product exists
                existing.quantity = (existing.quantity || 1) + 1;
            } else {
                // Add new product to cart
                cart.push({
                    id: id,
                    name: name,
                    price: price,
                    image: image,
                    quantity: 1
                });
            }
            
            // Save updated cart back to localStorage
            localStorage.setItem('pastimes_cart', JSON.stringify(cart));
            
            // Show success notification
            showNotification('✓ Added to cart!', 'success');
            
            // Update cart count badge
            updateCartCount();
        }
        
        /**
         * Update cart count displayed on the cart icon
         */
        function updateCartCount() {
            let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');
            let count = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
            let cartCount = document.getElementById('cartCount');
            if (cartCount) {
                cartCount.textContent = count;
                cartCount.style.display = count > 0 ? 'flex' : 'none';
            }
        }
        
        /**
         * Show temporary notification message
         * @param {string} message - Message to display
         * @param {string} type - 'success' or 'info'
         */
        function showNotification(message, type) {
            let notification = document.createElement('div');
            let bgColor = type === 'success' ? '#4caf50' : '#2196f3';
            notification.innerHTML = '<i class="fas fa-check-circle"></i> ' + message;
            notification.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: ' + bgColor + '; color: white; padding: 12px 20px; border-radius: 10px; z-index: 9999; animation: slideIn 0.3s ease; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
            document.body.appendChild(notification);
            
            // Remove notification after 3 seconds
            setTimeout(() => notification.remove(), 3000);
        }
        
        /**
         * Initialize search functionality on the global search input
         */
        function initSearch() {
            const searchInput = document.getElementById('globalSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const items = document.querySelectorAll('.product-card, .item-card');
                    items.forEach(item => {
                        const title = item.querySelector('.product-name, .item-title')?.innerText.toLowerCase() || '';
                        item.style.display = title.includes(searchTerm) ? 'block' : 'none';
                    });
                });
            }
        }
        
        /**
         * Clear all filters and reload the page
         */
        function clearFilters() {
            window.location.href = window.location.pathname;
        }
        
        // Initialize when DOM is fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
            initSearch();
        });
    </script>
</body>
</html>