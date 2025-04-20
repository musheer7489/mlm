<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Pagination
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($currentPage - 1) * $perPage;

// Filters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT o.*, u.username, u.full_name, p.name as product_name 
          FROM orders o
          JOIN users u ON o.user_id = u.id
          JOIN products p ON o.product_id = p.id
          WHERE 1=1";

$params = [];
$types = '';

if (!empty($statusFilter)) {
    $query .= " AND o.payment_status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if (!empty($dateFrom)) {
    $query .= " AND o.created_at >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}

if (!empty($dateTo)) {
    $query .= " AND o.created_at <= ?";
    $params[] = $dateTo . ' 23:59:59';
    $types .= 's';
}

if (!empty($searchQuery)) {
    $query .= " AND (u.username LIKE ? OR u.full_name LIKE ? OR o.payment_id LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

// Count total for pagination
$countStmt = $pdo->prepare(str_replace('o.*, u.username, u.full_name, p.name as product_name', 'COUNT(*) as total', $query));
if (!empty($params)) {
    $countStmt->execute($params);
}
$countStmt->execute();
$totalOrders = $countStmt->fetchColumn();
$totalPages = ceil($totalOrders / $perPage);

// Add sorting and pagination
$query .= " ORDER BY o.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $perPage;
$types .= 'ii';

// Get orders
$stmt = $pdo->prepare($query);
if (!empty($params)) {
    $stmt->execute($params);
}
$stmt->execute();
$orders = $stmt->fetchAll();
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
                        <h4 class="mb-0">Order Management</h4>
                        <div>
                            <span class="badge bg-light text-dark">
                                <?= number_format($totalOrders) ?> Orders
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="failed" <?= $statusFilter === 'failed' ? 'selected' : '' ?>>Failed</option>
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
                                    <input type="text" class="form-control" id="search" name="search" placeholder="ID, Name..." value="<?= htmlspecialchars($searchQuery) ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Orders Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['payment_id'] ?></td>
                                    <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <a href="view-distributor.php?id=<?= $order['user_id'] ?>">
                                            <?= htmlspecialchars($order['full_name']) ?>
                                            <small class="text-muted d-block">@<?= $order['username'] ?></small>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($order['product_name']) ?></td>
                                    <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $order['payment_status'] === 'completed' ? 'success' : 
                                            ($order['payment_status'] === 'pending' ? 'warning' : 'danger')
                                        ?>">
                                            <?= ucfirst($order['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="order-details.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-outline-secondary" onclick="printInvoice(<?= $order['id'] ?>)">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= 
                                    http_build_query(array_merge($_GET, ['page' => $currentPage - 1]))
                                ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= 
                                    http_build_query(array_merge($_GET, ['page' => $i]))
                                ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= 
                                    http_build_query(array_merge($_GET, ['page' => $currentPage + 1]))
                                ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                    <!-- Export Options -->
                    <div class="mt-4 text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-file-export me-2"></i>Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="export-orders.php?format=csv&<?= http_build_query($_GET) ?>">CSV</a></li>
                                <li><a class="dropdown-item" href="export-orders.php?format=excel&<?= http_build_query($_GET) ?>">Excel</a></li>
                                <li><a class="dropdown-item" href="export-orders.php?format=pdf&<?= http_build_query($_GET) ?>">PDF</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printInvoice(orderId) {
    window.open(`print-invoice.php?id=${orderId}`, '_blank');
}
</script>

<?php include '../includes/footer.php'; ?>