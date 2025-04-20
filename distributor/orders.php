<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isDistributor()) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$orders = getUserOrders($userId);
$user = getUserById($userId);
$badges = getUserBadges($userId);
?>

<?php include '../includes/header.php'; ?>

<div class="dashboard-container">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">My Orders</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)) : ?>
                        <div class="alert alert-info">
                            You haven't placed any orders yet. <a href="../product.php" class="alert-link">Browse our product</a> to get started.
                        </div>
                    <?php else : ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Payment Status</th>
                                        <th>Shipping Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order) : ?>
                                        <tr>
                                            <td>#<?= $order['id'] ?></td>
                                            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                            <td><?= $order['quantity'] ?> item(s)</td>
                                            <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= getPaymentStatusColor($order['payment_status']) ?> me-1">
                                                    <?= ucfirst($order['payment_status']) ?>
                                                </span>
                                            <td>

                                                <span class="badge bg-<?= getShippingStatusColor($order['shipping_status']) ?>">
                                                    <?= ucfirst($order['shipping_status']) ?>
                                                </span>
                                            </td>
                                            </td>
                                            <td>
                                                <a href="order-details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <?php if ($order['payment_status'] === 'pending') : ?>
                                                    <a href="../checkout.php?order_id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-credit-card"></i> Pay
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>