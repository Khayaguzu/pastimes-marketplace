<?php
session_start();
require_once 'DBConn.php';

// Check if user is logged in and verified
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=please_login');
    exit;
}

// Check if user is verified
$user_id = $_SESSION['user_id'];
$check_sql = "SELECT status FROM tblUser WHERE user_id = $user_id";
$check_result = mysqli_query($conn, $check_sql);
$user = mysqli_fetch_assoc($check_result);

if ($user['status'] !== 'verified') {
    header('Location: pending_approval.php');
    exit;
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id == 0) {
    header('Location: shop.php');
    exit;
}

// Get product details
$sql = "SELECT c.*, u.username as seller_name, u.user_id as seller_id 
        FROM tblClothes c 
        JOIN tblUser u ON c.seller_id = u.user_id 
        WHERE c.clothes_id = $product_id AND c.status = 'approved'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header('Location: shop.php');
    exit;
}

$product = mysqli_fetch_assoc($result);

// Get similar products
$similar_sql = "SELECT c.*, u.username as seller_name 
                FROM tblClothes c 
                JOIN tblUser u ON c.seller_id = u.user_id 
                WHERE c.category = '{$product['category']}' 
                AND c.clothes_id != $product_id 
                AND c.status = 'approved' 
                LIMIT 4";
$similar_result = mysqli_query($conn, $similar_sql);
$similar_products = mysqli_fetch_all($similar_result, MYSQLI_ASSOC);

include 'includes/header.php';
?>

<style>
    .detail-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .product-detail {
        display: flex;
        gap: 50px;
        background: white;
        border-radius: 30px;
        padding: 40px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        margin-bottom: 50px;
        flex-wrap: wrap;
    }
    
    .product-gallery {
        flex: 1;
        min-width: 300px;
    }
    
    .main-image {
        width: 100%;
        border-radius: 20px;
        margin-bottom: 15px;
    }
    
    .product-info {
        flex: 1;
        min-width: 300px;
    }
    
    .product-title {
        font-size: 28px;
        font-weight: 700;
        color: #0a2a28;
        margin-bottom: 10px;
    }
    
    .product-brand {
        font-size: 16px;
        color: #888;
        margin-bottom: 15px;
    }
    
    .product-price {
        font-size: 36px;
        font-weight: 800;
        color: #0a2a28;
        margin-bottom: 20px;
    }
    
    .product-meta {
        display: flex;
        gap: 20px;
        padding: 15px 0;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        margin-bottom: 20px;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .meta-label {
        font-weight: 600;
        color: #555;
    }
    
    .meta-value {
        color: #888;
    }
    
    .gender-tag {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
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
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        background: #28a745;
        color: white;
    }
    
    .out-of-stock {
        background: #dc3545;
    }
    
    .product-description {
        margin: 20px 0;
        line-height: 1.6;
        color: #555;
    }
    
    .seller-info {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 16px;
        margin: 20px 0;
    }
    
    .seller-name {
        font-size: 18px;
        font-weight: 600;
        color: #0a2a28;
        margin-bottom: 5px;
    }
    
    .button-group {
        display: flex;
        gap: 15px;
        margin-top: 25px;
    }
    
    .btn-buy {
        flex: 1;
        background: #e57e5c;
        color: white;
        padding: 14px;
        border: none;
        border-radius: 40px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-buy:hover {
        background: #c96a4a;
        transform: translateY(-2px);
    }
    
    .btn-message {
        flex: 1;
        background: transparent;
        color: #0a2a28;
        padding: 14px;
        border: 2px solid #0a2a28;
        border-radius: 40px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-message:hover {
        background: #0a2a28;
        color: white;
        transform: translateY(-2px);
    }
    
    .similar-section {
        margin-top: 50px;
    }
    
    .similar-title {
        font-size: 24px;
        font-weight: 700;
        color: #0a2a28;
        margin-bottom: 25px;
    }
    
    .similar-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
    }
    
    .similar-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: 1px solid #eee;
    }
    
    .similar-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .similar-img {
        width: 100%;
        aspect-ratio: 1/1;
        object-fit: cover;
    }
    
    .similar-info {
        padding: 12px;
    }
    
    .similar-price {
        font-size: 18px;
        font-weight: 700;
        color: #0a2a28;
    }
    
    .similar-name {
        font-size: 13px;
        font-weight: 500;
        margin-top: 5px;
        color: #333;
    }
    
    @media (max-width: 900px) {
        .similar-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .product-detail {
            padding: 25px;
        }
    }
    
    @media (max-width: 550px) {
        .similar-grid {
            grid-template-columns: 1fr;
        }
        .button-group {
            flex-direction: column;
        }
    }
</style>

<div class="detail-container">
    <div class="product-detail">
        <div class="product-gallery">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" class="main-image" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='https://placehold.co/600x600/0a2a28/white?text=Pastimes'">
        </div>
        
        <div class="product-info">
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            <?php if (!empty($product['brand'])): ?>
                <div class="product-brand">by <?php echo htmlspecialchars($product['brand']); ?></div>
            <?php endif; ?>
            
            <div class="product-price">R<?php echo number_format($product['price'], 2); ?></div>
            
            <div class="product-meta">
                <div class="meta-item">
                    <span class="meta-label">Category:</span>
                    <span class="meta-value"><?php echo htmlspecialchars($product['category']); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Gender:</span>
                    <span class="gender-tag gender-<?php echo $product['gender']; ?>">
                        <?php echo ucfirst($product['gender']); ?>
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Stock:</span>
                    <span class="stock-badge <?php echo ($product['stock'] > 0) ? '' : 'out-of-stock'; ?>">
                        <?php echo ($product['stock'] > 0) ? $product['stock'] . ' available' : 'Out of Stock'; ?>
                    </span>
                </div>
            </div>
            
            <div class="product-description">
                <p><?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description provided.')); ?></p>
            </div>
            
            <div class="seller-info">
                <div class="seller-name">
                    <i class="fas fa-store"></i> Seller: <?php echo htmlspecialchars($product['seller_name']); ?>
                </div>
                <div style="font-size: 12px; color: #888; margin-top: 5px;">Member since 2024</div>
            </div>
            
            <div class="button-group">
                <button class="btn-buy" onclick="addToCartAndCheckout(<?php echo $product['clothes_id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo $product['image']; ?>')">
                    <i class="fas fa-shopping-cart"></i> Buy Now
                </button>
                <button class="btn-message" onclick="messageSeller(<?php echo $product['seller_id']; ?>, '<?php echo addslashes($product['seller_name']); ?>', <?php echo $product['clothes_id']; ?>)">
                    <i class="fas fa-comment-dots"></i> Message Seller
                </button>
            </div>
        </div>
    </div>
    
    <?php if (count($similar_products) > 0): ?>
        <div class="similar-section">
            <h2 class="similar-title">You May Also Like</h2>
            <div class="similar-grid">
                <?php foreach ($similar_products as $similar): ?>
                    <div class="similar-card" onclick="window.location.href='item.php?id=<?php echo $similar['clothes_id']; ?>'">
                        <img src="<?php echo htmlspecialchars($similar['image']); ?>" class="similar-img" alt="<?php echo htmlspecialchars($similar['name']); ?>">
                        <div class="similar-info">
                            <div class="similar-price">R<?php echo number_format($similar['price'], 2); ?></div>
                            <div class="similar-name"><?php echo htmlspecialchars($similar['name']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function messageSeller(sellerId, sellerName, productId) {
        // Redirect to messages page with seller and product info
        window.location.href = 'messages.php?user_id=' + sellerId + '&product_id=' + productId;
    }
    
    function addToCartAndCheckout(id, name, price, image) {
        // Add to cart
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
        
        // Redirect to checkout
        window.location.href = 'checkout.php';
    }
</script>

<?php require_once 'includes/footer.php'; ?>