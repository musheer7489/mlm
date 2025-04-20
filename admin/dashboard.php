<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get statistics
$totalDistributors = getTotalDistributors();
$totalSales = getTotalSales();
$pendingCommissions = getTotalPendingCommissions();
$recentSignups = getRecentSignups(5);
$recentOrders = getRecentOrders(5);
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
                    <h4 class="mb-0">Admin Dashboard</h4>
                </div>
                <div class="card-body">
                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">Distributors</h6>
                                            <h3 class="mb-0"><?= $totalDistributors ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">Total Sales</h6>
                                            <h3 class="mb-0">₹<?= number_format($totalSales, 2) ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-warning text-dark">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon">
                                            <i class="fas fa-rupee-sign"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">Pending Commissions</h6>
                                            <h3 class="mb-0">₹<?= number_format($pendingCommissions, 2) ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon">
                                            <i class="fas fa-exclamation-circle"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">Pending Payouts</h6>
                                            <h3 class="mb-0"><?= getPendingPayoutsCount() ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Recent Signups</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Join Date</th>
                                                    <th>Sponsor</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($recentSignups as $user): ?>
                                                <tr>
                                                    <td>
                                                        <img src="../assets/images/profile/<?= $user['image'] ?? 'default.jpg' ?>" width="30" class="rounded-circle me-2">
                                                        <?= $user['full_name'] ?>
                                                    </td>
                                                    <td><?= date('d M', strtotime($user['created_at'])) ?></td>
                                                    <td><?= getUserById($user['sponsor_id'])['full_name'] ?? 'N/A' ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Recent Orders</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($recentOrders as $order): ?>
                                                <tr>
                                                    <td>#<?= $order['payment_id'] ?></td>
                                                    <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $order['payment_status'] == 'completed' ? 'success' : 'warning' ?>">
                                                            <?= ucfirst($order['payment_status']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sales Chart -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Monthly Sales</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Sales Chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul' , 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Sales (₹)',
                data: [12000, 19000, 15000, 25000, 22000, 30000, 28000, 2000, 3000, 4500, 3550, 3450],
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>