<?php
require_once 'includes/config.php';

if (!isLoggedIn() || !isset($_SESSION['order_success'])) {
    header('Location: index.php');
    exit;
}

$orderId = $_SESSION['order_success'];
$order = getOrderById($orderId);

if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    unset($_SESSION['order_success']);
    header('Location: index.php');
    exit;
}

$product = getSingleProduct();
$user = getUserById($_SESSION['user_id']);
$shippingMethod = getShippingMethodById($order['shipping_method_id']);

// Clear the session after displaying
unset($_SESSION['order_success']);
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Order Confirmation</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-5">
                        <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                        <h2>Thank You for Your Order!</h2>
                        <p class="lead">Your order has been placed successfully.</p>
                        <div class="alert alert-success d-inline-block">
                            <strong>Order Number:</strong> <?= $orderId ?>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Order Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Product:</span>
                                        <span><?= $product['name'] ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Quantity:</span>
                                        <span><?= $order['quantity'] ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span>₹<?= number_format($product['price'] * $order['quantity'], 2) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Shipping:</span>
                                        <span>₹<?= number_format($shippingMethod['cost'], 2) ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total:</span>
                                        <span>₹<?= number_format($order['total_amount'], 2) ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span>Payment Method:</span>
                                        <span class="text-capitalize">
                                            <?php if ($order['payment_method'] === 'razorpay'): ?>
                                                <i class="fas fa-credit-card me-1"></i> Online Payment
                                            <?php elseif ($order['payment_method'] === 'cod'): ?>
                                                <i class="fas fa-money-bill-wave me-1"></i> Cash on Delivery
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Payment Status:</span>
                                        <span class="badge bg-<?= $order['payment_status'] === 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($order['payment_status']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Shipping Information</h5>
                                </div>
                                <div class="card-body">
                                    <h6>Shipping Address</h6>
                                    <p><?= nl2br($order['shipping_address']) ?></p>
                                    
                                    <h6 class="mt-4">Shipping Method</h6>
                                    <p>
                                        <strong><?= $shippingMethod['name'] ?></strong><br>
                                        <?= $shippingMethod['description'] ?><br>
                                        Estimated delivery: <?= $shippingMethod['estimated_days'] ?>
                                    </p>
                                    
                                    <?php if ($order['payment_method'] === 'cod'): ?>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i> 
                                        Please keep exact change ready for when your order is delivered.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <a href="distributor/orders.php" class="btn btn-primary me-2">
                            <i class="fas fa-clipboard-list me-2"></i>View Your Orders
                        </a>
                        <a href="product.php" class="btn btn-outline-secondary">
                            <i class="fas fa-shopping-cart me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>