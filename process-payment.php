<?php
require_once 'includes/config.php';

if (!isLoggedIn() || !isset($_SESSION['order_id'])) {
    header('Location: checkout.php');
    exit;
}

$orderId = $_SESSION['order_id'];
$order = getOrderById($orderId);
$product = getSingleProduct();

if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    unset($_SESSION['order_id']);
    header('Location: checkout.php');
    exit;
}

$user = getUserById($_SESSION['user_id']);
//$product = getProductById($order['product_id']);
$shippingMethod = getShippingMethodById($order['shipping_method_id']);

// Handle Razorpay payment success callback
if (isset($_GET['payment_id'])) {
    $paymentId = $_GET['payment_id'];
    
    // Verify payment with Razorpay
    $paymentVerified = verifyRazorpayPayment($paymentId, $order['total_amount']);
    
    if ($paymentVerified) {
        // Update order status
        updateOrderPayment($orderId, $paymentId, 'completed');
        
        // Record status change
        addOrderStatusHistory($orderId, 'payment', 'pending', 'completed', 'Payment received via Razorpay');
        
        // Clear session and redirect to confirmation
        unset($_SESSION['order_id']);
        $_SESSION['order_success'] = $orderId;
        header('Location: order-confirmation.php');
        exit;
    } else {
        // Payment verification failed
        updateOrderPayment($orderId, $paymentId, 'failed');
        addOrderStatusHistory($orderId, 'payment', 'pending', 'failed', 'Razorpay payment verification failed');
        
        $_SESSION['error'] = 'Payment verification failed. Please try again.';
        header('Location: checkout.php');
        exit;
    }
}

// Generate Razorpay order ID
$razorpayOrderId = 'RP_' . uniqid();
updateOrderPaymentId($orderId, $razorpayOrderId);
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Complete Your Payment</h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <h5>Order #<?= $orderId ?></h5>
                        <h3 class="text-primary">₹<?= number_format($order['total_amount'], 2) ?></h3>
                        <p class="text-muted"><?= $product['name'] ?> × <?= $order['quantity'] ?></p>
                    </div>
                    
                    <button id="rzp-button" class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="fas fa-credit-card me-2"></i>Pay Now
                    </button>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> You will be redirected to Razorpay's secure payment page.
                    </div>
                    
                    <a href="checkout.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const options = {
    "key": "<?= RAZORPAY_KEY_ID ?>",
    "amount": "<?= $order['total_amount'] * 100 ?>", // Amount is in currency subunits (2000 = ₹20)
    "currency": "INR",
    "name": "<?= SITE_NAME ?>",
    "description": "Payment for Order #<?= $orderId ?>",
    "image": "assets/images/logo.png",
    "order_id": "<?= $razorpayOrderId ?>",
    "handler": function (response) {
        window.location.href = `process-payment.php?payment_id=${response.razorpay_payment_id}`;
    },
    "prefill": {
        "name": "<?= $user['full_name'] ?>",
        "email": "<?= $user['email'] ?>",
        "contact": "<?= $user['phone'] ?>"
    },
    "notes": {
        "address": "<?= str_replace("\n", " ", $user['address']) ?>",
        "order_id": "<?= $orderId ?>"
    },
    "theme": {
        "color": "#4e73df"
    }
};

const rzp = new Razorpay(options);
document.getElementById('rzp-button').onclick = function(e) {
    rzp.open();
    e.preventDefault();
}

// Handle payment failure (user closed the popup)
rzp.on('payment.failed', function(response) {
    console.log(response.error);
    // You can redirect to a failure page or show a message
    alert('Payment failed or was cancelled. Please try again.');
});
</script>

<?php include 'includes/footer.php'; ?>