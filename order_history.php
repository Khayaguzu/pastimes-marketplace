<?php
/**
 * Order History Page (order_history.php)
 * Displays user's past orders
 */

ob_start();
session_start();
require_once 'DBConn.php';
require_once 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=please_login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user orders with item counts
$sql = "SELECT o.*, 
        COUNT(oi.order_item_id) as item_count 
        FROM tblAorder o 
        LEFT JOIN tblOrderItems oi ON o.order_id = oi.order_id 
        WHERE o.buyer_id = $user_id 
        GROUP BY o.order_id 
        ORDER BY o.order_date DESC";
$result = mysqli_query($conn, $sql);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<style>
    .history-container {
        max-width: 1200px;
        margin: 40px auto;
    }
    
    .page-header {
        margin-bottom: 30px;
    }
    
    .page-header h1 {
        font-size: 32px;
        color: var(--primary);
        margin-bottom: 10px;
    }
    
    .page-header p {
        color: #666;
        font-size: 16px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 40px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 16px;
        text-align: center;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
    }
    
    .stat-card i {
        font-size: 32px;
        color: var(--accent);
        margin-bottom: 10px;
    }
    
    .stat-card .number {
        font-size: 28px;
        font-weight: 700;
        color: var(--primary);
    }
    
    .stat-card .label {
        color: #666;
        font-size: 14px;
        margin-top: 5px;
    }
    
    .orders-list {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--shadow);
    }
    
    .order-item {
        border-bottom: 1px solid #eee;
        padding: 25px;
        transition: background 0.3s;
    }
    
    .order-item:hover {
        background: #f9f9f9;
    }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .order-number {
        font-size: 18px;
        font-weight: 700;
        color: var(--primary);
    }
    
    .order-date {
        color: #888;
        font-size: 14px;
    }
    
    .order-status {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-pending { background: #fff3cd; color: #856404; }
    .status-processing { background: #cfe2ff; color: #084298; }
    .status-shipped { background: #cff4fc; color: #055160; }
    .status-delivered { background: #d1e7dd; color: #0f5132; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
    
    .order-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
    }
    
    .order-total {
        font-size: 20px;
        font-weight: 700;
        color: var(--primary);
    }
    
    .view-order-btn {
        background: var(--primary);
        color: white;
        padding: 8px 20px;
        border: none;
        border-radius: 30px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
    }
    
    .view-order-btn:hover {
        background: var(--accent);
        transform: translateY(-2px);
    }
    
    .empty-orders {
        text-align: center;
        padding: 60px;
        background: white;
        border-radius: 20px;
    }
    
    .empty-orders i {
        font-size: 64px;
        color: #ccc;
        margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .order-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .order-details {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="history-container">
    <div class="page-header">
        <h1><i class="fas fa-history"></i> My Order History</h1>
        <p>View all your past purchases and track your orders</p>
    </div>
    
    <?php if (count($orders) > 0): 
        $total_orders = count($orders);
        $total_spent = array_sum(array_column($orders, 'total_amount'));
        $delivered_orders = count(array_filter($orders, function($o) { return $o['order_status'] == 'delivered'; }));
        $pending_orders = count(array_filter($orders, function($o) { return $o['order_status'] == 'pending'; }));
    ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-shopping-bag"></i>
            <div class="number"><?php echo $total_orders; ?></div>
            <div class="label">Total Orders</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-rand"></i>
            <div class="number">R<?php echo number_format($total_spent, 2); ?></div>
            <div class="label">Total Spent</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-check-circle"></i>
            <div class="number"><?php echo $delivered_orders; ?></div>
            <div class="label">Delivered</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-clock"></i>
            <div class="number"><?php echo $pending_orders; ?></div>
            <div class="label">In Progress</div>
        </div>
    </div>
    
    <div class="orders-list">
        <?php foreach ($orders as $order): ?>
            <div class="order-item">
                <div class="order-header">
                    <div>
                        <div class="order-number">Order #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></div>
                        <div class="order-date">
                            <i class="far fa-calendar-alt"></i> 
                            <?php echo date('F d, Y', strtotime($order['order_date'])); ?>
                        </div>
                    </div>
                    <div>
                        <span class="order-status status-<?php echo $order['order_status']; ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="order-details">
                    <div>
                        <div><strong>Items:</strong> <?php echo $order['item_count']; ?> product(s)</div>
                        <div><strong>Payment:</strong> <?php echo strtoupper($order['payment_method']); ?></div>
                        <?php if ($order['tracking_number']): ?>
                            <div><strong>Tracking:</strong> <?php echo $order['tracking_number']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: right;">
                        <div class="order-total">Total: R<?php echo number_format($order['total_amount'], 2); ?></div>
                        <button class="view-order-btn" onclick="alert('Order #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?>\nFor details, check your email or contact support.')">
                            View Details →
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php else: ?>
    <div class="empty-orders">
        <i class="fas fa-shopping-bag"></i>
        <h3>No Orders Yet</h3>
        <p>You haven't placed any orders yet. Start shopping to see your order history here!</p>
        <button class="btn btn-primary" onclick="window.location.href='shop.php'">
            <i class="fas fa-shopping-cart"></i> Start Shopping Now
        </button>
    </div>
    <?php endif; ?>
</div>

<?php 
require_once 'includes/footer.php';
ob_end_flush();
?>