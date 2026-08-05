<?php
/**
 * Admin Manage Clothing Page
 * Admin can add, edit, and delete clothing items with IMAGE UPLOAD (not URL)
 */

session_start();
require_once 'DBConn.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$clothing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

// Create uploads directory if it doesn't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Handle Delete
if ($action == 'delete' && $clothing_id > 0) {
    // Get image path before deleting
    $img_query = "SELECT image FROM tblClothes WHERE clothes_id = $clothing_id";
    $img_result = mysqli_query($conn, $img_query);
    if ($img_result && mysqli_num_rows($img_result) > 0) {
        $img_data = mysqli_fetch_assoc($img_result);
        $image_path = $img_data['image'];
        // Delete the image file if it exists
        if (!empty($image_path) && file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    $sql = "DELETE FROM tblClothes WHERE clothes_id = $clothing_id";
    if (mysqli_query($conn, $sql)) {
        $success = "Clothing item deleted successfully!";
        $action = 'list';
    } else {
        $error = "Delete failed: " . mysqli_error($conn);
    }
}

// Handle Add/Edit with Image Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_clothing'])) {
    $seller_id = (int)$_POST['seller_id'];
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $brand = mysqli_real_escape_string($conn, trim($_POST['brand']));
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Handle image upload
    $image_path = '';
    $upload_error = '';
    
    // Check if a new image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        
        // Validate file type
        if (!in_array($file_type, $allowed_types)) {
            $upload_error = "Only JPG, JPEG, PNG, GIF, and WEBP images are allowed!";
        }
        // Validate file size
        elseif ($file_size > $max_size) {
            $upload_error = "Image size must be less than 5MB!";
        }
        else {
            // Generate unique filename
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_filename = 'product_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = 'uploads/' . $new_filename;
            
            // Upload the file
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $image_path = $upload_path;
            } else {
                $upload_error = "Failed to upload image. Please try again.";
            }
        }
    }
    
    // If editing and no new image, keep the old image
    if ($clothing_id > 0 && empty($image_path) && empty($upload_error)) {
        // Get existing image path
        $old_img_query = "SELECT image FROM tblClothes WHERE clothes_id = $clothing_id";
        $old_img_result = mysqli_query($conn, $old_img_query);
        if ($old_img_result && mysqli_num_rows($old_img_result) > 0) {
            $old_img = mysqli_fetch_assoc($old_img_result);
            $image_path = $old_img['image'];
        }
    }
    
    // Check for upload errors
    if (!empty($upload_error)) {
        $error = $upload_error;
    } else {
        if ($clothing_id > 0) {
            // Update existing clothing
            $sql = "UPDATE tblClothes SET 
                    seller_id = $seller_id,
                    name = '$name',
                    brand = '$brand',
                    category = '$category',
                    gender = '$gender',
                    price = $price,
                    stock = $stock,
                    description = '$description',
                    status = '$status'";
            
            // Add image path to update only if a new image was uploaded
            if (!empty($image_path)) {
                // Delete old image file
                $old_img_query = "SELECT image FROM tblClothes WHERE clothes_id = $clothing_id";
                $old_img_result = mysqli_query($conn, $old_img_query);
                if ($old_img_result && mysqli_num_rows($old_img_result) > 0) {
                    $old_img = mysqli_fetch_assoc($old_img_result);
                    if (!empty($old_img['image']) && file_exists($old_img['image'])) {
                        unlink($old_img['image']);
                    }
                }
                $sql .= ", image = '$image_path'";
            }
            
            $sql .= " WHERE clothes_id = $clothing_id";
            
            if (mysqli_query($conn, $sql)) {
                $success = "Clothing item updated successfully!";
            } else {
                $error = "Update failed: " . mysqli_error($conn);
            }
        } else {
            // Add new clothing
            if (empty($image_path)) {
                $error = "Please select an image for the product!";
            } else {
                $sql = "INSERT INTO tblClothes (seller_id, name, brand, category, gender, price, stock, description, status, image, created_at) 
                        VALUES ($seller_id, '$name', '$brand', '$category', '$gender', $price, $stock, '$description', '$status', '$image_path', NOW())";
                
                if (mysqli_query($conn, $sql)) {
                    $success = "Clothing item added successfully!";
                    $action = 'list';
                } else {
                    $error = "Add failed: " . mysqli_error($conn);
                    // Delete uploaded image if database insert fails
                    if (!empty($image_path) && file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
            }
        }
    }
}

// Get clothing item for edit
$clothing_item = null;
if (($action == 'edit' || $action == 'add') && $clothing_id > 0) {
    $sql = "SELECT * FROM tblClothes WHERE clothes_id = $clothing_id";
    $result = mysqli_query($conn, $sql);
    $clothing_item = mysqli_fetch_assoc($result);
}

// Get all sellers for dropdown
$sellers = mysqli_query($conn, "SELECT user_id, username, full_name FROM tblUser WHERE status = 'verified' ORDER BY username");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Clothing - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 { color: #0a2a28; margin-bottom: 20px; }
        h2 { color: #0a2a28; margin-bottom: 20px; font-size: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
        }
        .form-group input[type="file"] {
            padding: 10px;
            background: #f8f9fa;
        }
        .image-preview {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            text-align: center;
        }
        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            object-fit: cover;
        }
        .current-image {
            margin-top: 10px;
            padding: 10px;
            background: #e8f5e9;
            border-radius: 10px;
        }
        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .row-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px; }
        .btn-primary {
            background: #0a2a28;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background: #e57e5c;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-warning {
            background: #ffc107;
            color: #333;
            padding: 6px 12px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .data-table th {
            background: #0a2a28;
            color: white;
        }
        .action-buttons { display: flex; gap: 5px; flex-wrap: wrap; }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #0a2a28;
            text-decoration: none;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-approved { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .file-info {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
        .required {
            color: red;
        }
        @media (max-width: 800px) {
            .row-2, .row-3, .row-4 { grid-template-columns: 1fr; }
            .data-table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><i class="fas fa-tshirt"></i> Manage Clothing Items</h1>
            
            <?php if ($success): ?>
                <div class="success-msg"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error-msg"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($action == 'add' || $action == 'edit'): ?>
                <!-- Add/Edit Form with Image Upload -->
                <h2><?php echo $action == 'add' ? '➕ Add New Clothing' : '✏️ Edit Clothing'; ?></h2>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row-2">
                        <div class="form-group">
                            <label>Seller <span class="required">*</span></label>
                            <select name="seller_id" required>
                                <option value="">Select Seller</option>
                                <?php 
                                mysqli_data_seek($sellers, 0);
                                while ($seller = mysqli_fetch_assoc($sellers)): 
                                ?>
                                    <option value="<?php echo $seller['user_id']; ?>" 
                                        <?php echo ($clothing_item && $clothing_item['seller_id'] == $seller['user_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($seller['full_name'] . ' (@' . $seller['username'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Item Name <span class="required">*</span></label>
                            <input type="text" name="name" required value="<?php echo htmlspecialchars($clothing_item['name'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row-3">
                        <div class="form-group">
                            <label>Brand</label>
                            <input type="text" name="brand" value="<?php echo htmlspecialchars($clothing_item['brand'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Category <span class="required">*</span></label>
                            <select name="category" required>
                                <option value="Top" <?php echo ($clothing_item && $clothing_item['category'] == 'Top') ? 'selected' : ''; ?>>Top</option>
                                <option value="Bottom" <?php echo ($clothing_item && $clothing_item['category'] == 'Bottom') ? 'selected' : ''; ?>>Bottom</option>
                                <option value="Outerwear" <?php echo ($clothing_item && $clothing_item['category'] == 'Outerwear') ? 'selected' : ''; ?>>Outerwear</option>
                                <option value="Shoes" <?php echo ($clothing_item && $clothing_item['category'] == 'Shoes') ? 'selected' : ''; ?>>Shoes</option>
                                <option value="Accessory" <?php echo ($clothing_item && $clothing_item['category'] == 'Accessory') ? 'selected' : ''; ?>>Accessory</option>
                                <option value="Dress" <?php echo ($clothing_item && $clothing_item['category'] == 'Dress') ? 'selected' : ''; ?>>Dress</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Gender <span class="required">*</span></label>
                            <select name="gender" required>
                                <option value="men" <?php echo ($clothing_item && $clothing_item['gender'] == 'men') ? 'selected' : ''; ?>>Men</option>
                                <option value="women" <?php echo ($clothing_item && $clothing_item['gender'] == 'women') ? 'selected' : ''; ?>>Women</option>
                                <option value="unisex" <?php echo ($clothing_item && $clothing_item['gender'] == 'unisex') ? 'selected' : ''; ?>>Unisex</option>
                            </select>
                        </div>
                    </div>
                    <div class="row-3">
                        <div class="form-group">
                            <label>Price (R) <span class="required">*</span></label>
                            <input type="number" name="price" step="0.01" required value="<?php echo $clothing_item['price'] ?? ''; ?>">
                            <small style="color: #888;">Price must be between R50 and R400</small>
                        </div>
                        <div class="form-group">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock" value="<?php echo $clothing_item['stock'] ?? 1; ?>">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="pending" <?php echo ($clothing_item && $clothing_item['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo ($clothing_item && $clothing_item['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo ($clothing_item && $clothing_item['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Product Image <?php if ($action == 'add'): ?><span class="required">*</span><?php endif; ?></label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" 
                               <?php echo $action == 'add' ? 'required' : ''; ?>>
                        <div class="file-info">
                            <i class="fas fa-info-circle"></i> Allowed formats: JPG, JPEG, PNG, GIF, WEBP. Max size: 5MB
                        </div>
                        
                        <?php if ($action == 'edit' && !empty($clothing_item['image']) && file_exists($clothing_item['image'])): ?>
                            <div class="current-image">
                                <strong>Current Image:</strong><br>
                                <img src="<?php echo htmlspecialchars($clothing_item['image']); ?>" alt="Current Image" style="max-width: 150px; max-height: 150px; margin-top: 10px;">
                                <p style="font-size: 12px; color: #666; margin-top: 5px;">Leave empty to keep current image</p>
                            </div>
                        <?php endif; ?>
                        
                        <div id="imagePreview" class="image-preview" style="display: none;">
                            <strong>New Image Preview:</strong><br>
                            <img id="previewImg" src="" alt="Preview">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"><?php echo htmlspecialchars($clothing_item['description'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="save_clothing" class="btn-primary">
                        <i class="fas fa-save"></i> <?php echo $action == 'add' ? 'Add Clothing' : 'Update Clothing'; ?>
                    </button>
                    <a href="admin_manage_clothing.php" class="btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </form>
            <?php else: ?>
                <!-- List View -->
                <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
                    <a href="admin_manage_clothing.php?action=add" class="btn-primary">
                        <i class="fas fa-plus"></i> Add New Clothing
                    </a>
                    <a href="admin_dashboard.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                
                <?php
                $clothes_query = "SELECT c.*, u.username as seller_name 
                                  FROM tblClothes c 
                                  JOIN tblUser u ON c.seller_id = u.user_id 
                                  ORDER BY c.created_at DESC";
                $clothes_result = mysqli_query($conn, $clothes_query);
                ?>
                
                <?php if (mysqli_num_rows($clothes_result) > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Seller</th>
                                <th>Price</th>
                                <th>Category</th>
                                <th>Gender</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = mysqli_fetch_assoc($clothes_result)): ?>
                                <tr>
                                    <td><?php echo $item['clothes_id']; ?></td>
                                    <td>
                                        <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" class="product-img">
                                        <?php else: ?>
                                            <img src="https://placehold.co/60x60/0a2a28/white?text=No+Image" class="product-img">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                        <br><small style="color:#888;"><?php echo htmlspecialchars($item['brand']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['seller_name']); ?></td>
                                    <td><strong style="color:#e57e5c;">R<?php echo number_format($item['price'], 2); ?></strong></td>
                                    <td><?php echo $item['category']; ?></td>
                                    <td><?php echo ucfirst($item['gender']); ?></td>
                                    <td><?php echo $item['stock']; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $item['status']; ?>">
                                            <?php echo ucfirst($item['status']); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="admin_manage_clothing.php?action=edit&id=<?php echo $item['clothes_id']; ?>" class="btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="admin_manage_clothing.php?action=delete&id=<?php echo $item['clothes_id']; ?>" 
                                           class="btn-danger" 
                                           onclick="return confirm('Delete this item permanently? The image will also be deleted.')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state" style="text-align: center; padding: 50px;">
                        <i class="fas fa-tshirt" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
                        <h3>No Clothing Items Found</h3>
                        <p>Click "Add New Clothing" to add your first product.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
        </div>
    </div>
    
    <script>
        // Image preview functionality
        const imageInput = document.querySelector('input[type="file"]');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        
        if (imageInput) {
            imageInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.style.display = 'none';
                    previewImg.src = '';
                }
            });
        }
    </script>
</body>
</html>