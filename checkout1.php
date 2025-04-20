<?php
require_once 'includes/config.php';


if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$product = getSingleProduct();
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// Handle payment success callback
if (isset($_GET['payment_id']) && isset($_GET['order_id'])) {
    verifyPayment($_GET['payment_id'], $_GET['order_id']);
    header('Location: distributor/dashboard.php');
    exit;
}

// Generate order ID
$orderId = 'ORD-' . strtoupper(uniqid());

// Store order in database (pending status)
$stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, quantity, total_amount, payment_status, payment_id) 
                      VALUES (?, ?, ?, ?, 'pending', ?)");
$stmt->execute([
    $userId,
    $product['id'],
    1,
    $product['price'],
    $orderId
]);

$dbOrderId = $pdo->lastInsertId();
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Checkout</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Product Details</h5>
                            <div class="card mb-3">
                                <div class="row g-0">
                                    <div class="col-md-4">
                                        <img src="assets/images/product/<?= $product['image'] ?>" class="img-fluid rounded-start" alt="<?= $product['name'] ?>">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= $product['name'] ?></h6>
                                            <p class="card-text text-muted"><?= substr($product['description'], 0, 50) ?>...</p>
                                            <h5 class="text-primary">₹<?= number_format($product['price'], 2) ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>Payment Information</h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Product Price:</span>
                                        <span>₹<?= number_format($product['price'], 2) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax (5%):</span>
                                        <span>₹<?= number_format($product['price'] * 0.05, 2) ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total Amount:</span>
                                        <span>₹<?= number_format($product['price'] * 1.05, 2) ?></span>
                                    </div>
                                    
                                    <button id="rzp-button" class="btn btn-primary w-100 mt-3">
                                        <i class="fas fa-credit-card me-2"></i>Pay Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const options = {
    "key": "<?= RAZORPAY_KEY_ID ?>",
    "amount": "<?= $product['price'] * 1.05 * 100 ?>", // Amount is in currency subunits (5000 = ₹50)
    "currency": "INR",
    "name": "<?= SITE_NAME ?>",
    "description": "Purchase of <?= $product['name'] ?>",
    "image": "assets/images/logo.png",
    "order_id": "<?= $orderId ?>",
    "handler": function (response) {
        window.location.href = `checkout.php?payment_id=${response.razorpay_payment_id}&order_id=${response.razorpay_order_id}`;
    },
    "prefill": {
        "name": "<?= $user['full_name'] ?>",
        "email": "<?= $user['email'] ?>",
        "contact": "<?= $user['phone'] ?>"
    },
    "notes": {
        "address": "<?= $user['address'] ?>",
        "product_id": "<?= $product['id'] ?>"
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
</script>

<?php include 'includes/footer.php'; ?>