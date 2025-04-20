<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isDistributor()) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);
$badges = getUserBadges($userId);
$payouts = getDistributorPayouts($userId);
$pendingCommissions = getPendingCommissionsTotal($userId);
$totalEarned = getTotalCommissionsEarned($userId);
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
                    <h4 class="mb-0">My Payouts</h4>
                </div>
                <div class="card-body">
                    <!-- Payout Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon">
                                            <i class="fas fa-rupee-sign"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">Total Earned</h6>
                                            <h3 class="mb-0">₹<?= number_format($totalEarned, 2) ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card bg-warning text-dark">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">Pending Payout</h6>
                                            <h3 class="mb-0">₹<?= number_format($pendingCommissions, 2) ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">Total Payouts</h6>
                                            <h3 class="mb-0"><?= count($payouts) ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payout History -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Payout History</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($payouts)): ?>
                                <div class="p-3 text-center text-muted">
                                    You haven't received any payouts yet. Payouts are processed monthly when your balance reaches ₹500.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Payout ID</th>
                                                <th>Period</th>
                                                <th>Date</th>
                                                <th>Commissions</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($payouts as $payout): ?>
                                            <tr>
                                                <td>#<?= str_pad($payout['payout_id'], 5, '0', STR_PAD_LEFT) ?></td>
                                                <td><?= ucfirst($payout['period']) ?></td>
                                                <td><?= date('M j, Y', strtotime($payout['paid_at'])) ?></td>
                                                <td><?= $payout['commission_count'] ?></td>
                                                <td>₹<?= number_format($payout['amount'], 2) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $payout['status'] === 'paid' ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($payout['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="payout-details.php?id=<?= $payout['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> Details
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <nav class="p-3">
                                    <ul class="pagination justify-content-center mb-0">
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
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Payout Information -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Payout Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>How Payouts Work</h6>
                                <ul class="mb-0">
                                    <li>Payouts are processed monthly on the 5th of each month</li>
                                    <li>Minimum payout amount is ₹500</li>
                                    <li>Commissions are paid via bank transfer to your registered account</li>
                                    <li>Please ensure your bank details are up to date in your profile</li>
                                </ul>
                            </div>
                            
                            <div class="text-end">
                                <a href="profile.php" class="btn btn-outline-primary">
                                    <i class="fas fa-user-edit me-2"></i>Update Bank Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>