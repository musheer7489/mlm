<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle product updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_product'])) {
        $productId = $_POST['product_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        
        // Handle image upload
        $image = $_POST['current_image'];
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = '../assets/images/product/';
            $uploadFile = $uploadDir . basename($_FILES['image']['name']);
            
            // Check if file is an image
            $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
            if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                    $image = basename($_FILES['image']['name']);
                }
            }
        }
        
        updateProduct($productId, $name, $description, $price, $stock, $image);
        $_SESSION['success'] = 'Product updated successfully';
        header('Location: products.php');
        exit;
    }
}

$product = getSingleProduct();
?>

<?php include '../includes/header.php'; ?>

<div class="admin-container">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Product Management</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="current_image" value="<?= $product['image'] ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price (₹)</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?= $product['price'] ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="stock" class="form-label">Stock Quantity</label>
                                    <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?= $product['stock'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Product Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    
                                    <?php if ($product['image']): ?>
                                        <div class="mt-2">
                                            <img src="../assets/images/product/<?= $product['image'] ?>" class="img-thumbnail" width="150">
                                            <small class="text-muted d-block">Current image</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($product['description']) ?></textarea>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" name="update_product" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Product
                            </button>
                        </div>
                    </form>
                    
                    <!-- SEO Section -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">SEO Settings</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="update-seo.php">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                
                                <div class="mb-3">
                                    <label for="meta_title" class="form-label">Meta Title</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title" value="<?= htmlspecialchars($product['meta_title'] ?? '') ?>" maxlength="60">
                                    <small class="text-muted">Recommended: 50-60 characters</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="meta_description" class="form-label">Meta Description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description" rows="3" maxlength="160"><?= htmlspecialchars($product['meta_description'] ?? '') ?></textarea>
                                    <small class="text-muted">Recommended: 150-160 characters</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="<?= htmlspecialchars($product['meta_keywords'] ?? '') ?>">
                                    <small class="text-muted">Comma-separated keywords</small>
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search me-2"></i>Update SEO
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>