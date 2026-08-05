<?php
/**
 * Upload Item Page - Sellers can list their clothes for sale
 * Filename: upload-item.php
 * Purpose: Allows verified sellers to upload clothing items with images
 */

session_start();
require_once 'DBConn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=Please login to sell your clothes');
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if user is verified
$check_sql = "SELECT status, full_name, username FROM tblUser WHERE user_id = $user_id";
$check_result = mysqli_query($conn, $check_sql);
$user = mysqli_fetch_assoc($check_result);

// Only verified users can upload items
if ($user['status'] !== 'verified') {
    header('Location: pending_approval.php');
    exit;
}

$success = '';
$error = '';

// Create uploads directory if it doesn't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_item'])) {
    
    // Get form data and sanitize
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $brand = mysqli_real_escape_string($conn, trim($_POST['brand']));
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $price = floatval($_POST['price']);
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $stock = intval($_POST['stock']);
    
    $errors = array();
    
    // Validation
    if (empty($name)) {
        $errors[] = "Item name is required";
    }
    if (empty($category)) {
        $errors[] = "Category is required";
    }
    if (empty($gender)) {
        $errors[] = "Gender is required";
    }
    if ($price < 50) {
        $errors[] = "Price must be at least R50";
    }
    if ($price > 400) {
        $errors[] = "Price cannot exceed R400";
    }
    if ($stock < 1) {
        $errors[] = "Stock must be at least 1";
    }
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        
        // Validate file type
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only JPG, JPEG, PNG, GIF, and WEBP images are allowed!";
        }
        // Validate file size
        elseif ($file_size > $max_size) {
            $errors[] = "Image size must be less than 5MB!";
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
                $errors[] = "Failed to upload image. Please try again.";
            }
        }
    } else {
        $errors[] = "Please select an image for your item!";
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        $sql = "INSERT INTO tblClothes (seller_id, name, brand, category, gender, price, image, description, stock, status, created_at) 
                VALUES ('$user_id', '$name', '$brand', '$category', '$gender', '$price', '$image_path', '$description', '$stock', 'pending', NOW())";
        
        if (mysqli_query($conn, $sql)) {
            $success = "✅ Your item has been uploaded successfully! It will appear in the shop after admin approval.";
            // Clear form data
            $_POST = array();
        } else {
            $error = "Upload failed: " . mysqli_error($conn);
            // Delete uploaded image if database insert fails
            if (!empty($image_path) && file_exists($image_path)) {
                unlink($image_path);
            }
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

// Include header
require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Your Clothes - Pastimes</title>
    <style>
        .upload-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .upload-card {
            background: white;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .upload-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .upload-header h1 {
            font-size: 32px;
            color: #0a2a28;
            margin-bottom: 10px;
        }
        
        .upload-header p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s;
        }
        
        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            outline: none;
            border-color: #e57e5c;
            box-shadow: 0 0 0 3px rgba(229,126,92,0.1);
        }
        
        .form-group input[type="file"] {
            padding: 10px;
            background: #f8f9fa;
        }
        
        .image-preview {
            margin-top: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            text-align: center;
            display: none;
        }
        
        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            object-fit: cover;
        }
        
        .file-info {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
        
        .row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            width: 100%;
            background: #0a2a28;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 10px;
        }
        
        .btn-submit:hover {
            background: #e57e5c;
            transform: translateY(-2px);
        }
        
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        
        .info-box {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
            font-size: 13px;
        }
        
        .info-box i {
            color: #e57e5c;
            margin-right: 8px;
        }
        
        .required {
            color: #dc3545;
        }
        
        .price-hint {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
        
        @media (max-width: 600px) {
            .row-2, .row-3 {
                grid-template-columns: 1fr;
            }
            .upload-card {
                padding: 25px;
            }
        }
    </style>
</head>
<body>

<div class="upload-container">
    <div class="upload-card">
        <div class="upload-header">
            <h1><i class="fas fa-cloud-upload-alt"></i> Sell Your Clothes</h1>
            <p>List your pre-loved items and start earning money</p>
            <p style="font-size: 14px; color: #e57e5c; margin-top: 5px;">
                <i class="fas fa-user-check"></i> Selling as: <?php echo htmlspecialchars($user['full_name']); ?> (@<?php echo htmlspecialchars($user['username']); ?>)
            </p>
        </div>
        
        <?php if ($success): ?>
            <div class="success-msg">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <p style="margin-top: 10px;">
                    <a href="shop.php" style="color: #155724; font-weight: bold;">Browse Shop →</a>
                </p>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
            <!-- Item Name -->
            <div class="form-group">
                <label>Item Name <span class="required">*</span></label>
                <input type="text" name="name" required 
                       placeholder="e.g., Vintage Levi's 501 Jeans"
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
            
            <div class="row-2">
                <!-- Brand -->
                <div class="form-group">
                    <label>Brand</label>
                    <input type="text" name="brand" 
                           placeholder="e.g., Levi's, Zara, Nike"
                           value="<?php echo isset($_POST['brand']) ? htmlspecialchars($_POST['brand']) : ''; ?>">
                </div>
                
                <!-- Category -->
                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="Top" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Top') ? 'selected' : ''; ?>>👕 Top</option>
                        <option value="Bottom" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Bottom') ? 'selected' : ''; ?>>👖 Bottom</option>
                        <option value="Outerwear" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Outerwear') ? 'selected' : ''; ?>>🧥 Outerwear</option>
                        <option value="Shoes" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Shoes') ? 'selected' : ''; ?>>👟 Shoes</option>
                        <option value="Accessory" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Accessory') ? 'selected' : ''; ?>>🧢 Accessory</option>
                        <option value="Dress" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Dress') ? 'selected' : ''; ?>>👗 Dress</option>
                    </select>
                </div>
            </div>
            
            <div class="row-3">
                <!-- Gender -->
                <div class="form-group">
                    <label>Gender <span class="required">*</span></label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="men" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'men') ? 'selected' : ''; ?>>👨 Men</option>
                        <option value="women" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'women') ? 'selected' : ''; ?>>👩 Women</option>
                        <option value="unisex" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'unisex') ? 'selected' : ''; ?>>🔄 Unisex</option>
                    </select>
                </div>
                
                <!-- Price -->
                <div class="form-group">
                    <label>Price (R) <span class="required">*</span></label>
                    <input type="number" name="price" required 
                           step="1" min="50" max="400"
                           placeholder="50 - 400"
                           value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>">
                    <div class="price-hint">Price must be between R50 and R400</div>
                </div>
                
                <!-- Stock -->
                <div class="form-group">
                    <label>Quantity Available</label>
                    <input type="number" name="stock" 
                           min="1" max="100"
                           value="<?php echo isset($_POST['stock']) ? $_POST['stock'] : '1'; ?>">
                </div>
            </div>
            
            <!-- Image Upload -->
            <div class="form-group">
                <label>Product Image <span class="required">*</span></label>
                <input type="file" name="image" id="imageInput" 
                       accept="image/jpeg,image/png,image/gif,image/webp" required>
                <div class="file-info">
                    <i class="fas fa-info-circle"></i> 
                    Allowed formats: JPG, JPEG, PNG, GIF, WEBP. Max size: 5MB
                </div>
                <div id="imagePreview" class="image-preview">
                    <img id="previewImg" src="" alt="Image Preview">
                    <br>
                    <button type="button" class="remove-image" onclick="removeImage()" 
                            style="margin-top: 10px; background: #dc3545; color: white; border: none; padding: 5px 12px; border-radius: 20px; cursor: pointer;">
                        Remove Image
                    </button>
                </div>
            </div>
            
            <!-- Description -->
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="5" 
                          placeholder="Describe your item: condition, size, color, material, any flaws, why you're selling, etc."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" name="upload_item" class="btn-submit">
                <i class="fas fa-cloud-upload-alt"></i> List Item for Sale
            </button>
        </form>
        
        <div class="info-box">
            <i class="fas fa-info-circle"></i> 
            <strong>How it works:</strong> Your item will be reviewed by our admin team within 24 hours. 
            Once approved, it will appear in the shop for buyers to purchase. You'll earn money from every sale!
        </div>
        
        <div class="info-box" style="margin-top: 15px; background: #fff3cd;">
            <i class="fas fa-tips"></i> 
            <strong>Selling Tips:</strong> Take clear, well-lit photos. Be honest about condition. 
            Price competitively. Include measurements for clothing items.
        </div>
    </div>
</div>

<script>
    // Image preview functionality
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (imageInput) {
        imageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, GIF, or WEBP)');
                    this.value = '';
                    imagePreview.style.display = 'none';
                    return;
                }
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image size must be less than 5MB');
                    this.value = '';
                    imagePreview.style.display = 'none';
                    return;
                }
                
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
    
    function removeImage() {
        imageInput.value = '';
        imagePreview.style.display = 'none';
        previewImg.src = '';
    }
    
    // Form validation before submit
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        const name = document.querySelector('input[name="name"]').value.trim();
        const category = document.querySelector('select[name="category"]').value;
        const gender = document.querySelector('select[name="gender"]').value;
        const price = document.querySelector('input[name="price"]').value;
        const image = document.querySelector('input[name="image"]').files[0];
        
        if (!name) {
            alert('Please enter an item name');
            e.preventDefault();
            return false;
        }
        
        if (!category) {
            alert('Please select a category');
            e.preventDefault();
            return false;
        }
        
        if (!gender) {
            alert('Please select a gender');
            e.preventDefault();
            return false;
        }
        
        if (!price || price < 50 || price > 400) {
            alert('Price must be between R50 and R400');
            e.preventDefault();
            return false;
        }
        
        if (!image && !<?php echo isset($_POST['upload_item']) ? 'true' : 'false'; ?>) {
            alert('Please select an image for your item');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
</script>

<?php require_once 'includes/footer.php'; ?>