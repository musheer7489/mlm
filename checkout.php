<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=checkout');
    exit;
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);
$product = getSingleProduct();
$shippingMethods = getShippingMethods();

// Default values
$shippingAddress = $user['address'] ?? '';
$billingAddress = $user['address'] ?? '';
$shippingMethodId = 1; // Default to standard shipping
$paymentMethod = 'razorpay';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingAddress = $_POST['shipping_address'];
    $billingAddress = $_POST['billing_address'];
    $shippingMethodId = $_POST['shipping_method'];
    $paymentMethod = $_POST['payment_method'];
    $notes = $_POST['notes'] ?? '';
    
    // Get shipping method
    $shippingMethod = getShippingMethodById($shippingMethodId);
    if (!$shippingMethod) {
        $_SESSION['error'] = 'Invalid shipping method selected';
        header('Location: checkout.php');
        exit;
    }
    
    // Calculate total
    $subtotal = $product['price'];
    $shippingCost = $shippingMethod['cost'];
    $total = $subtotal + $shippingCost;
    
    // Generate order ID
    $orderId = 'ORD-' . strtoupper(uniqid());
    
    // Create order in database
    $orderData = [
        'user_id' => $userId,
        'product_id' => $product['id'],
        'quantity' => 1,
        'total_amount' => $total,
        'shipping_address' => $shippingAddress,
        'billing_address' => $billingAddress,
        'payment_method' => $paymentMethod,
        'notes' => $notes,
        'shipping_method_id' => $shippingMethodId
    ];
    
    $orderId = createOrder($orderData);
    
    if ($paymentMethod === 'razorpay') {
        // Redirect to Razorpay payment
        $_SESSION['order_id'] = $orderId;
        header('Location: process-payment.php');
        exit;
    } elseif ($paymentMethod === 'cod') {
        // Cash on Delivery - mark as pending
        updateOrderStatus($orderId, 'payment', 'pending', '',$user['full_name']);
        $_SESSION['order_success'] = $orderId;
        header('Location: order-confirmation.php');
        exit;
    }
}

// If coming from product page with quantity
$quantity = $_GET['quantity'] ?? 1;
$quantity = max(1, min(10, (int)$quantity));
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Checkout Process</h4>
                </div>
                <div class="card-body">
                    <form method="POST" id="checkoutForm">
                        <!-- Shipping Address -->
                        <div class="mb-4">
                            <h5 class="mb-3">Shipping Address</h5>
                            <div class="form-group mb-3">
                                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required><?= htmlspecialchars($shippingAddress) ?></textarea>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="same_as_billing">
                                <label class="form-check-label" for="same_as_billing">
                                    Billing address same as shipping address
                                </label>
                            </div>
                        </div>
                        
                        <!-- Billing Address (initially hidden if same as shipping) -->
                        <div class="mb-4" id="billingAddressSection">
                            <h5 class="mb-3">Billing Address</h5>
                            <div class="form-group">
                                <textarea class="form-control" id="billing_address" name="billing_address" rows="3"><?= htmlspecialchars($billingAddress) ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Shipping Method -->
                        <div class="mb-4">
                            <h5 class="mb-3">Shipping Method</h5>
                            <div class="list-group">
                                <?php foreach($shippingMethods as $method): ?>
                                <label class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <input class="form-check-input me-2" type="radio" name="shipping_method" 
                                                   value="<?= $method['id'] ?>" 
                                                   <?= $method['id'] == $shippingMethodId ? 'checked' : '' ?> required>
                                            <strong><?= $method['name'] ?></strong>
                                            <div class="text-muted"><?= $method['description'] ?></div>
                                            <small>Estimated delivery: <?= $method['estimated_days'] ?></small>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-bold">₹<?= number_format($method['cost'], 2) ?></span>
                                        </div>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="mb-4">
                            <h5 class="mb-3">Payment Method</h5>
                            <div class="list-group">
                                <label class="list-group-item">
                                    <input class="form-check-input me-2" type="radio" name="payment_method" 
                                           value="razorpay" <?= $paymentMethod === 'razorpay' ? 'checked' : '' ?> required>
                                    <strong>Credit/Debit Card, UPI, NetBanking (via Razorpay)</strong>
                                    <div class="text-muted">Secure online payment</div>
                                </label>
                                <label class="list-group-item">
                                    <input class="form-check-input me-2" type="radio" name="payment_method" 
                                           value="cod" <?= $paymentMethod === 'cod' ? 'checked' : '' ?>>
                                    <strong>Cash on Delivery</strong>
                                    <div class="text-muted">Pay when you receive the product</div>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Order Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Order Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-bag me-2"></i>Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Product:</span>
                        <span><?= $product['name'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Quantity:</span>
                        <span><?= $quantity ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>₹<?= number_format($product['price'] * $quantity, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span id="shippingCost">₹<?= number_format($shippingMethods[0]['cost'], 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span id="orderTotal">₹<?= number_format($product['price'] * $quantity + $shippingMethods[0]['cost'], 2) ?></span>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong><?= $user['full_name'] ?></strong></p>
                    <p class="mb-1"><?= $user['email'] ?></p>
                    <p class="mb-1"><?= $user['phone'] ?></p>
                    <p class="mb-0"><?= nl2br($user['address']) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle same as billing checkbox
    $('#same_as_billing').change(function() {
        if ($(this).is(':checked')) {
            $('#billingAddressSection').hide();
            $('#billing_address').val($('#shipping_address').val());
        } else {
            $('#billingAddressSection').show();
        }
    }).trigger('change');
    
    // Update shipping cost and total when shipping method changes
    $('input[name="shipping_method"]').change(function() {
        const shippingCost = parseFloat($(this).closest('label').find('.fw-bold').text().replace('₹', ''));
        const subtotal = parseFloat(<?= $product['price'] * $quantity ?>);
        const total = subtotal + shippingCost;
        
        $('#shippingCost').text('₹' + shippingCost.toFixed(2));
        $('#orderTotal').text('₹' + total.toFixed(2));
    });
});
</script>

<?php include 'includes/footer.php'; ?>