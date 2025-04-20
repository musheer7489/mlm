<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isDistributor()) {
    header('Location: ../login.php');
    exit;
}

$orderId = $_GET['id'] ?? 0;
$order = getOrderDetails($orderId);
$products = getOrderItemsById($orderId);
$userId = $_SESSION['user_id'];
$user = getUserById($userId);
$badges = getUserBadges($userId);
// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = $_POST['order_id'];
    $statusType = $_POST['status_type'];
    $newStatus = $_POST['new_status'];
    $notes = $_POST['notes'] ?? '';

    updateOrderStatus($orderId, $statusType, $newStatus, $notes, $_SESSION['user_id']);
    $_SESSION['success'] = 'Order status updated successfully';
    header('Location: orders.php');
    exit;
}

if (!$order) {
    $_SESSION['error'] = 'Order not found';
    header('Location: orders.php');
    exit;
}

$statusHistory = getOrderStatusHistory($orderId);

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
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Order #<?= $order['id'] ?></h4>
                        <div>
                            <span class="badge bg-light text-dark me-2">
                                <?= date('M j, Y', strtotime($order['created_at'])) ?>
                            </span>
                            <a href="orders.php" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back to Orders
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])) : ?>
                        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])) : ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <!-- Order Summary -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Order Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <td>#<?= $order['id'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Order Date</th>
                                                    <td><?= date('M j, Y H:i', strtotime($order['created_at'])) ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Payment Status</th>
                                                    <td>
                                                        <span class="badge bg-<?= getPaymentStatusColor($order['payment_status']) ?>">
                                                            <?= ucfirst($order['payment_status']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Shipping Status</th>
                                                    <td>
                                                        <span class="badge bg-<?= getShippingStatusColor($order['shipping_status']) ?>">
                                                            <?= ucfirst($order['shipping_status']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Payment Method</th>
                                                    <td><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Payment ID</th>
                                                    <td><?= $order['payment_id'] ?? 'N/A' ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Tracking Number</th>
                                                    <td><?= $order['tracking_number'] ?? 'Not shipped yet' ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Cancel Order</th>
                                                    <td><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#statusModal" data-order-id="<?= $order['id'] ?>" data-status-type="shipping" data-current-status="<?= $order['shipping_status'] ?>">
                                                            <span class="btn btn-danger"><i class="fas fa-trash"></i> Cancel Order</span>
                                                        </button></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Information -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Customer Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="../assets/images/profile/<?= $order['image'] ?? 'default.jpg' ?>" width="50" class="rounded-circle me-3">
                                        <div>
                                            <h6 class="mb-0"><?= $order['full_name'] ?></h6>
                                            <small class="text-muted">ID: <?= $order['username'] ?></small>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <th>Email</th>
                                                    <td><?= $order['email'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Phone</th>
                                                    <td><?= $order['phone'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Sponsor</th>
                                                    <td><?= $order['sponsor_name'] ?? 'N/A' ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Order Items</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Qty</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                              <?php foreach ($products as $product) : ?>
                                                    <tr>
                                                        <td>
                                                            <?= $product['name'] ?>
                                                            <?php if ($product['image']) : ?>
                                                                <img src="../assets/images/product/<?= $product['image'] ?>" width="40" class="ms-2">
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>₹<?= number_format($product['price'], 2) ?></td>
                                                        <td><?= $order['quantity'] ?></td>
                                                        <td>₹<?= number_format($product['price'] * $order['quantity'], 2) ?></td>
                                                    </tr>
                                              <?php endforeach ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3">Subtotal</th>
                                                    <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                                </tr>
                                                <tr>
                                                    <th colspan="3">Shipping</th>
                                                    <td>₹<?= number_format($order['shipping_cost'] ?? 0, 2) ?></td>
                                                </tr>
                                                <tr>
                                                    <th colspan="3">Tax</th>
                                                    <td>₹<?= number_format($order['tax_amount'] ?? 0, 2) ?></td>
                                                </tr>
                                                <tr class="table-active">
                                                    <th colspan="3">Total</th>
                                                    <td>₹<?= number_format($order['total_amount'] + ($order['shipping_cost'] ?? 0) + ($order['tax_amount'] ?? 0), 2) ?></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping Information -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Shipping Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <th>Shipping Method</th>
                                                    <td><?= $order['shipping_method'] ?? 'Standard Shipping' ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Shipping Address</th>
                                                    <td><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Billing Address</th>
                                                    <td><?= nl2br(htmlspecialchars($order['billing_address'])) ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Customer Notes</th>
                                                    <td><?= $order['notes'] ? nl2br(htmlspecialchars($order['notes'])) : 'None' ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status History -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Status History</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($statusHistory)) : ?>
                                <div class="alert alert-info mb-0">No status history available for this order.</div>
                            <?php else : ?>
                                <div class="timeline">
                                    <?php foreach ($statusHistory as $history) : ?>
                                        <div class="timeline-item">
                                            <div class="timeline-item-marker">
                                                <div class="timeline-item-marker-indicator bg-<?=
                                                                                                ($history['status_type'] === 'payment' ? 'primary' : 'success')
                                                                                                ?>"></div>
                                            </div>
                                            <div class="timeline-item-content">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="fw-bold">
                                                        <?= ucfirst($history['status_type']) ?> Status Changed
                                                    </span>
                                                    <small class="text-muted">
                                                        <?= date('M j, Y H:i', strtotime($history['created_at'])) ?>
                                                    </small>
                                                </div>
                                                <div class="mb-1">
                                                    <span class="badge bg-secondary"><?= ucfirst($history['old_status']) ?></span>
                                                    <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                                    <span class="badge bg-<?=
                                                                            ($history['status_type'] === 'payment' ?
                                                                                getPaymentStatusColor($history['new_status']) :
                                                                                getShippingStatusColor($history['new_status']))
                                                                            ?>">
                                                        <?= ucfirst($history['new_status']) ?>
                                                    </span>
                                                </div>
                                                <?php if ($history['notes']) : ?>
                                                    <div class="alert alert-light p-2 mb-0">
                                                        <?= htmlspecialchars($history['notes']) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <small class="text-muted">
                                                    Changed by: <?= getUserName($history['changed_by']) ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="update_status">
                <input type="hidden" name="order_id" id="statusOrderId">
                <input type="hidden" name="status_type" id="statusType">

                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalTitle">Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_status" class="form-label">New Status</label>
                        <select class="form-select" id="new_status" name="new_status" required>
                            <!-- Options will be populated by JavaScript -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="status_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    // Status Modal Handling
    document.getElementById('statusModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const orderId = button.getAttribute('data-order-id');
        const statusType = button.getAttribute('data-status-type');
        const currentStatus = button.getAttribute('data-current-status');

        const modal = this;
        modal.querySelector('#statusOrderId').value = orderId;
        modal.querySelector('#statusType').value = statusType;

        // Set modal title
        modal.querySelector('.modal-title').textContent = `Update ${statusType.charAt(0).toUpperCase() + statusType.slice(1)} Status`;

        // Populate status options
        const statusSelect = modal.querySelector('#new_status');
        statusSelect.innerHTML = '';

        const statusOptions = statusType === 'payment' ? [
            {
                value: 'refunded',
                label: 'Refunded'
            }
        ] : [
            {
                value: 'returned',
                label: 'Returned'
            }
        ];

        statusOptions.forEach(option => {
            const optElement = document.createElement('option');
            optElement.value = option.value;
            optElement.textContent = option.label;
            if (option.value === currentStatus) {
                optElement.selected = true;
            }
            statusSelect.appendChild(optElement);
        });
    });
</script>
<?php include '../includes/footer.php'; ?>