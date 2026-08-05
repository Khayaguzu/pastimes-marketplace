<?php
/**
 * Shop Page - Displays all approved products
 * Filename: shop.php
 * Purpose: Shows all pre-approved products with images
 */

session_start();
require_once 'DBConn.php';

// Check if user is logged in and verified (optional - guest can view shop)
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

// Get filter values from URL
$selected_category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$selected_gender = isset($_GET['gender']) ? mysqli_real_escape_string($conn, $_GET['gender']) : '';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 1000;

// Build the SQL query to get approved products only
$sql = "SELECT c.*, u.username as seller_name, u.user_id as seller_id 
        FROM tblClothes c 
        JOIN tblUser u ON c.seller_id = u.user_id 
        WHERE c.status = 'approved'";

// Apply filters
if (!empty($selected_category)) {
    $sql .= " AND c.category = '$selected_category'";
}
if (!empty($selected_gender)) {
    $sql .= " AND c.gender = '$selected_gender'";
}
if ($min_price > 0) {
    $sql .= " AND c.price >= $min_price";
}
if ($max_price < 1000) {
    $sql .= " AND c.price <= $max_price";
}

$sql .= " ORDER BY c.created_at DESC";

$result = mysqli_query($conn, $sql);
$products = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

// Get unique categories for filter
$categories = ['Top', 'Bottom', 'Outerwear', 'Shoes', 'Accessory', 'Dress'];
$genders = ['men', 'women', 'unisex'];

// Function to get product image with fallback
function getProductImage($image_path, $product_name) {
    if (!empty($image_path) && file_exists($image_path)) {
        return htmlspecialchars($image_path);
    }
    // Return placeholder with product name
    $encoded_name = urlencode(substr($product_name, 0, 30));
    return "https://placehold.co/400x400/0a2a28/white?text=" . $encoded_name;
}

// Function to get gender badge class
function getGenderBadge($gender) {
    switch($gender) {
        case 'men': return 'gender-men';
        case 'women': return 'gender-women';
        default: return 'gender-unisex';
    }
}

// Function to get gender icon
function getGenderIcon($gender) {
    switch($gender) {
        case 'men': return '👨 Men';
        case 'women': return '👩 Women';
        default: return '🔄 Unisex';
    }
}

// Include header
require_once 'includes/header.php';
?>

<style>
    /* Shop Container */
    .shop-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 30px 20px;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .page-header h1 {
        font-size: 32px;
        color: #0a2a28;
    }
    
    .sell-btn {
        background: #e57e5c;
        color: white;
        padding: 12px 25px;
        border-radius: 40px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    
    .sell-btn:hover {
        background: #c96a4a;
        transform: translateY(-2px);
    }
    
    /* Gender Navigation */
    .gender-nav {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
        flex-wrap: wrap;
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
    }
    
    .gender-link {
        padding: 10px 25px;
        border-radius: 40px;
        text-decoration: none;
        font-weight: 600;
        background: #f0eeeb;
        color: #555;
        transition: all 0.2s;
    }
    
    .gender-link.active {
        background: #0a2a28;
        color: white;
    }
    
    .gender-link:hover:not(.active) {
        background: #e57e5c;
        color: white;
    }
    
    /* Layout */
    .products-layout {
        display: flex;
        gap: 30px;
        margin: 30px 0;
    }
    
    /* Filters Sidebar */
    .filters-sidebar {
        width: 280px;
        background: white;
        padding: 25px;
        border-radius: 20px;
        height: fit-content;
        position: sticky;
        top: 100px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    
    .filters-sidebar h3 {
        margin-bottom: 20px;
        color: #0a2a28;
        font-size: 20px;
        border-bottom: 2px solid #e57e5c;
        padding-bottom: 10px;
    }
    
    .filter-group {
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .filter-group h4 {
        margin-bottom: 15px;
        color: #0a2a28;
        font-size: 16px;
    }
    
    .filter-group label {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        cursor: pointer;
        color: #555;
    }
    
    .filter-group input[type="radio"] {
        width: 18px;
        height: 18px;
        accent-color: #e57e5c;
    }
    
    .price-inputs {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .price-inputs input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
    }
    
    .filter-btn {
        width: 100%;
        background: #0a2a28;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 30px;
        font-weight: 600;
        cursor: pointer;
    }
    
    .clear-btn {
        width: 100%;
        background: #f0eeeb;
        color: #555;
        padding: 10px;
        border: none;
        border-radius: 30px;
        margin-top: 10px;
        cursor: pointer;
    }
    
    /* Products Main */
    .products-main {
        flex: 1;
    }
    
    .products-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    
    .products-count {
        background: white;
        padding: 8px 20px;
        border-radius: 30px;
        color: #666;
        font-size: 14px;
    }
    
    /* Products Grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
    }
    
    .product-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        cursor: pointer;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .product-img {
        width: 100%;
        aspect-ratio: 1/1;
        object-fit: cover;
        background: #f5f5f5;
    }
    
    .product-info {
        padding: 16px;
    }
    
    .product-price {
        font-size: 24px;
        font-weight: 700;
        color: #0a2a28;
    }
    
    .product-name {
        font-weight: 600;
        margin: 8px 0 5px;
        font-size: 14px;
        color: #333;
    }
    
    .product-brand {
        font-size: 12px;
        color: #888;
        margin-bottom: 5px;
    }
    
    .product-seller {
        font-size: 12px;
        color: #aaa;
        border-top: 1px solid #eee;
        padding-top: 10px;
        margin-top: 8px;
    }
    
    .gender-tag {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        margin-left: 8px;
    }
    
    .gender-men {
        background: #cfe2ff;
        color: #084298;
    }
    
    .gender-women {
        background: #f8d7da;
        color: #721c24;
    }
    
    .gender-unisex {
        background: #d1e7dd;
        color: #0f5132;
    }
    
    .stock-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        background: #28a745;
        color: white;
    }
    
    .button-group {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }
    
    .buy-btn {
        flex: 1;
        background: #e57e5c;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 30px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .buy-btn:hover {
        background: #c96a4a;
    }
    
    .msg-btn {
        flex: 1;
        background: transparent;
        color: #0a2a28;
        padding: 10px;
        border: 1px solid #0a2a28;
        border-radius: 30px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .msg-btn:hover {
        background: #0a2a28;
        color: white;
    }
    
    .empty-products {
        text-align: center;
        padding: 60px;
        background: white;
        border-radius: 20px;
    }
    
    .empty-products i {
        font-size: 64px;
        color: #ccc;
        margin-bottom: 20px;
    }
    
    /* Responsive */
    @media (max-width: 1000px) {
        .products-layout {
            flex-direction: column;
        }
        .filters-sidebar {
            width: 100%;
            position: static;
        }
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 600px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
        .gender-nav {
            justify-content: center;
        }
    }
</style>

<div class="shop-container">
    <div class="page-header">
        <h1><i class="fas fa-store"></i> Shop Pre-Loved Fashion</h1>
        <a href="upload-item.php" class="sell-btn">
            <i class="fas fa-plus-circle"></i> Sell Your Clothes
        </a>
    </div>
    
    <!-- Gender Navigation -->
    <div class="gender-nav">
        <a href="shop.php" class="gender-link <?php echo empty($selected_gender) ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i> All
        </a>
        <a href="shop.php?gender=men" class="gender-link <?php echo $selected_gender == 'men' ? 'active' : ''; ?>">
            <i class="fas fa-mars"></i> Men
        </a>
        <a href="shop.php?gender=women" class="gender-link <?php echo $selected_gender == 'women' ? 'active' : ''; ?>">
            <i class="fas fa-venus"></i> Women
        </a>
        <a href="shop.php?gender=unisex" class="gender-link <?php echo $selected_gender == 'unisex' ? 'active' : ''; ?>">
            <i class="fas fa-sync-alt"></i> Unisex
        </a>
    </div>
    
    <div class="products-layout">
        <!-- Filters Sidebar -->
        <aside class="filters-sidebar">
            <h3><i class="fas fa-filter"></i> Filters</h3>
            
            <form method="GET" action="shop.php" id="filterForm">
                <?php if (!empty($selected_gender)): ?>
                    <input type="hidden" name="gender" value="<?php echo $selected_gender; ?>">
                <?php endif; ?>
                
                <div class="filter-group">
                    <h4><i class="fas fa-tag"></i> Category</h4>
                    <?php foreach ($categories as $cat): ?>
                        <label>
                            <input type="radio" name="category" value="<?php echo $cat; ?>" 
                                <?php echo $selected_category == $cat ? 'checked' : ''; ?> 
                                onchange="this.form.submit()">
                            <?php echo $cat; ?>
                        </label>
                    <?php endforeach; ?>
                    <label>
                        <input type="radio" name="category" value="" 
                            <?php echo empty($selected_category) ? 'checked' : ''; ?> 
                            onchange="this.form.submit()">
                        All Categories
                    </label>
                </div>
                
                <div class="filter-group">
                    <h4><i class="fas fa-rand"></i> Price Range</h4>
                    <div class="price-inputs">
                        <input type="number" name="min_price" placeholder="Min" value="<?php echo $min_price ?: ''; ?>">
                        <input type="number" name="max_price" placeholder="Max" value="<?php echo $max_price != 1000 ? $max_price : ''; ?>">
                    </div>
                    <button type="submit" class="filter-btn">Apply Price</button>
                </div>
            </form>
            
            <button class="clear-btn" onclick="clearFilters()">
                <i class="fas fa-trash-alt"></i> Clear All Filters
            </button>
        </aside>
        
        <!-- Products Main Content -->
        <div class="products-main">
            <div class="products-header">
                <div class="products-count">
                    <i class="fas fa-shopping-bag"></i> <?php echo count($products); ?> Products Found
                </div>
            </div>
            
            <?php if (count($products) > 0): ?>
                <div class="products-grid" id="productsGrid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" onclick="viewProduct(<?php echo $product['clothes_id']; ?>)">
                            <img src="<?php echo getProductImage($product['image'], $product['name']); ?>" 
                                 class="product-img" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='https://placehold.co/400x400/0a2a28/white?text=No+Image'">
                            <div class="product-info">
                                <div class="product-price">R<?php echo number_format($product['price'], 2); ?></div>
                                <?php if (!empty($product['brand'])): ?>
                                    <div class="product-brand"><?php echo htmlspecialchars($product['brand']); ?></div>
                                <?php endif; ?>
                                <div class="product-name">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                    <span class="gender-tag <?php echo getGenderBadge($product['gender']); ?>">
                                        <?php echo getGenderIcon($product['gender']); ?>
                                    </span>
                                </div>
                                <div class="product-seller">
                                    <i class="fas fa-user-circle"></i> Seller: <?php echo htmlspecialchars($product['seller_name']); ?>
                                </div>
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="stock-badge">In Stock (<?php echo $product['stock']; ?>)</span>
                                <?php endif; ?>
                                <div class="button-group" onclick="event.stopPropagation()">
                                    <button class="buy-btn" onclick="addToCart(<?php echo $product['clothes_id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo addslashes($product['image']); ?>')">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                    <button class="msg-btn" onclick="messageSeller(<?php echo $product['seller_id']; ?>, '<?php echo addslashes($product['seller_name']); ?>', <?php echo $product['clothes_id']; ?>)">
                                        <i class="fas fa-comment-dots"></i> Message
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-products">
                    <i class="fas fa-search"></i>
                    <h3>No Products Found</h3>
                    <p>Try adjusting your filters or check back later for new items!</p>
                    <button onclick="window.location.href='shop.php'" style="background:#0a2a28; color:white; padding:12px 30px; border:none; border-radius:30px; margin-top:15px; cursor:pointer;">
                        Clear All Filters
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Update cart count on page load
    function updateCartCount() {
        let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');
        let count = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
        let cartCountElement = document.getElementById('cartCount');
        if (cartCountElement) {
            cartCountElement.innerText = count;
        }
    }
    
    // Add to cart function
    function addToCart(id, name, price, image) {
        let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');
        let existing = cart.find(item => item.id === id);
        
        if (existing) {
            existing.quantity = (existing.quantity || 1) + 1;
        } else {
            cart.push({
                id: id,
                name: name,
                price: price,
                image: image,
                quantity: 1
            });
        }
        
        localStorage.setItem('pastimes_cart', JSON.stringify(cart));
        updateCartCount();
        showNotification('Added to cart!', 'success');
    }
    
    // Message seller function
    function messageSeller(sellerId, sellerName, productId) {
        <?php if ($is_logged_in): ?>
            window.location.href = 'messages.php?user_id=' + sellerId + '&product_id=' + productId;
        <?php else: ?>
            if (confirm('Please login to message the seller.')) {
                window.location.href = 'login.php';
            }
        <?php endif; ?>
    }
    
    // View product details
    function viewProduct(productId) {
        window.location.href = 'item.php?id=' + productId;
    }
    
    // Clear filters
    function clearFilters() {
        <?php if (!empty($selected_gender)): ?>
            window.location.href = 'shop.php?gender=<?php echo $selected_gender; ?>';
        <?php else: ?>
            window.location.href = 'shop.php';
        <?php endif; ?>
    }
    
    // Show notification
    function showNotification(message, type) {
        let notification = document.createElement('div');
        notification.style.position = 'fixed';
        notification.style.bottom = '20px';
        notification.style.right = '20px';
        notification.style.backgroundColor = type === 'success' ? '#28a745' : '#17a2b8';
        notification.style.color = 'white';
        notification.style.padding = '12px 20px';
        notification.style.borderRadius = '8px';
        notification.style.zIndex = '1000';
        notification.innerHTML = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 2000);
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateCartCount();
    });
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>