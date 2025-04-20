<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle commission level updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_commissions'])) {
    foreach ($_POST['levels'] as $levelId => $data) {
        updateCommissionLevel($levelId, $data['percentage'], $data['description']);
    }
    $_SESSION['success'] = 'Commission levels updated successfully';
    header('Location: commissions.php');
    exit;
}

$levels = getCommissionLevels();
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
                    <h4 class="mb-0">Commission Management</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <!-- Commission Levels Form -->
                    <form method="POST">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Level</th>
                                        <th>Commission Percentage</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($levels as $level): ?>
                                    <tr>
                                        <td>
                                            Level <?= $level['level'] ?>
                                            <input type="hidden" name="levels[<?= $level['id'] ?>][level]" value="<?= $level['level'] ?>">
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="levels[<?= $level['id'] ?>][percentage]" 
                                                    value="<?= $level['percentage'] ?>" step="0.01" min="0" max="100" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="levels[<?= $level['id'] ?>][description]" 
                                                value="<?= htmlspecialchars($level['description']) ?>">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-end mt-3">
                            <button type="submit" name="update_commissions" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                    
                    <!-- Payout Processing -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Process Payouts</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="process-payouts.php">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Select Payout Period</label>
                                            <select class="form-select" name="payout_period" required>
                                                <option value="">-- Select Period --</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="monthly">Monthly</option>
                                                <option value="custom">Custom Date Range</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="customDateRange" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">From Date</label>
                                                    <input type="date" class="form-control" name="from_date">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">To Date</label>
                                                    <input type="date" class="form-control" name="to_date">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Minimum Payout Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" class="form-control" name="min_amount" value="500" min="0" step="0.01" required>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <strong>Pending Commissions:</strong> ₹<?= number_format(getTotalPendingCommissions(), 2) ?>
                                    <br>
                                    <strong>Distributors Eligible:</strong> <?= getEligibleDistributorsCount() ?>
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-money-bill-wave me-2"></i>Process Payouts
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Recent Payouts -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Payouts</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Period</th>
                                            <th>Distributors</th>
                                            <th>Total Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach(getRecentPayouts() as $payout): ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($payout['created_at'])) ?></td>
                                            <td><?= ucfirst($payout['period']) ?></td>
                                            <td><?= $payout['distributor_count'] ?></td>
                                            <td>₹<?= number_format($payout['total_amount'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $payout['status'] == 'completed' ? 'success' : 'warning' ?>">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide custom date range
$(document).ready(function() {
    $('[name="payout_period"]').change(function() {
        if ($(this).val() === 'custom') {
            $('#customDateRange').show();
        } else {
            $('#customDateRange').hide();
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>