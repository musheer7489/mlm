<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

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

// Handle tracking number update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tracking'])) {
    $orderId = $_POST['order_id'];
    $trackingNumber = $_POST['tracking_number'];
    
    updateOrderTracking($orderId, $trackingNumber, $_SESSION['user_id']);
    $_SESSION['success'] = 'Tracking number updated successfully';
    header('Location: orders.php');
    exit;
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Get orders with filters
$orders = getOrdersWithFilters($statusFilter, $dateFrom, $dateTo, $searchQuery);
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
                        <h4 class="mb-0">Orders Management</h4>
                        <a href="orders-export.php" class="btn btn-light btn-sm">
                            <i class="fas fa-file-export me-1"></i> Export
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Orders</option>
                                        <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending Payment</option>
                                        <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Paid</option>
                                        <option value="shipped" <?= $statusFilter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="delivered" <?= $statusFilter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= $dateFrom ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= $dateTo ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="search" class="form-label">Search</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="search" name="search" placeholder="Order ID, Name" value="<?= htmlspecialchars($searchQuery) ?>">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Orders Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Shipping</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td>
                                        <?= $order['full_name'] ?>
                                        <small class="text-muted d-block"><?= $order['username'] ?></small>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                    <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= getPaymentStatusColor($order['payment_status']) ?>">
                                            <?= ucfirst($order['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= getShippingStatusColor($order['shipping_status']) ?>">
                                            <?= ucfirst($order['shipping_status']) ?>
                                        </span>
                                        <?php if ($order['tracking_number']): ?>
                                            <small class="d-block"><?= $order['tracking_number'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="orderActions" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="order-details.php?id=<?= $order['id'] ?>">
                                                        <i class="fas fa-eye me-2"></i>View Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#statusModal" 
                                                            data-order-id="<?= $order['id'] ?>" data-status-type="payment" 
                                                            data-current-status="<?= $order['payment_status'] ?>">
                                                        <i class="fas fa-money-bill-wave me-2"></i>Update Payment
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#statusModal" 
                                                            data-order-id="<?= $order['id'] ?>" data-status-type="shipping" 
                                                            data-current-status="<?= $order['shipping_status'] ?>">
                                                        <i class="fas fa-truck me-2"></i>Update Shipping
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#trackingModal" 
                                                            data-order-id="<?= $order['id'] ?>" data-current-tracking="<?= $order['tracking_number'] ?>">
                                                        <i class="fas fa-barcode me-2"></i>Update Tracking
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" onclick="confirmDelete(<?= $order['id'] ?>)">
                                                        <i class="fas fa-trash me-2"></i>Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (empty($orders)): ?>
                            <div class="alert alert-info text-center my-4">
                                No orders found matching your criteria.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
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
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tracking Update Modal -->
<div class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="update_tracking">
                <input type="hidden" name="order_id" id="trackingOrderId">
                
                <div class="modal-header">
                    <h5 class="modal-title">Update Tracking Number</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tracking_number" class="form-label">Tracking Number</label>
                        <input type="text" class="form-control" id="tracking_number" name="tracking_number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Tracking</button>
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
    
    const statusOptions = statusType === 'payment' ? 
        [
            {value: 'pending', label: 'Pending'},
            {value: 'completed', label: 'Completed'},
            {value: 'failed', label: 'Failed'},
            {value: 'refunded', label: 'Refunded'}
        ] : 
        [
            {value: 'pending', label: 'Pending'},
            {value: 'processing', label: 'Processing'},
            {value: 'shipped', label: 'Shipped'},
            {value: 'delivered', label: 'Delivered'},
            {value: 'returned', label: 'Returned'}
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

// Tracking Modal Handling
document.getElementById('trackingModal').addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const orderId = button.getAttribute('data-order-id');
    const currentTracking = button.getAttribute('data-current-tracking') || '';
    
    const modal = this;
    modal.querySelector('#trackingOrderId').value = orderId;
    modal.querySelector('#tracking_number').value = currentTracking;
});

// Delete confirmation
function confirmDelete(orderId) {
    if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
        window.location.href = `delete-order.php?id=${orderId}`;
    }
}
</script>

<?php include '../includes/footer.php'; ?>