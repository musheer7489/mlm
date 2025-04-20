<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle payout actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['process_payouts'])) {
        $period = $_POST['period'];
        $fromDate = $_POST['from_date'] ?? null;
        $toDate = $_POST['to_date'] ?? null;
        $minAmount = $_POST['min_amount'];
        
        processPayouts($period, $fromDate, $toDate, $minAmount);
        $_SESSION['success'] = 'Payouts processed successfully';
        header('Location: payouts.php');
        exit;
    } elseif (isset($_POST['mark_paid'])) {
        $payoutId = $_POST['payout_id'];
        markPayoutAsPaid($payoutId);
        $_SESSION['success'] = 'Payout marked as paid';
        header('Location: payouts.php');
        exit;
    }
}

// Get payout data
$pendingPayouts = getPendingPayouts();
$completedPayouts = getCompletedPayouts();
$eligibleDistributors = getEligibleDistributors();
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
                    <h4 class="mb-0">Payouts Management</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <!-- Process Payouts Form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Process New Payouts</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="period" class="form-label">Payout Period</label>
                                        <select class="form-select" id="period" name="period" required>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="custom">Custom Date Range</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4" id="customDateRange" style="display: none;">
                                        <label for="from_date" class="form-label">From Date</label>
                                        <input type="date" class="form-control" id="from_date" name="from_date">
                                    </div>
                                    <div class="col-md-4" id="customDateRange2" style="display: none;">
                                        <label for="to_date" class="form-label">To Date</label>
                                        <input type="date" class="form-control" id="to_date" name="to_date">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="min_amount" class="form-label">Minimum Payout Amount (₹)</label>
                                        <input type="number" class="form-control" id="min_amount" name="min_amount" value="500" min="0" step="0.01" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Eligible Distributors</label>
                                        <div class="alert alert-info mb-0">
                                            <strong><?= count($eligibleDistributors) ?></strong> distributors eligible for payout
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" name="process_payouts" class="btn btn-success">
                                        <i class="fas fa-money-bill-wave me-2"></i>Process Payouts
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Pending Payouts -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Pending Payouts</h5>
                            <span class="badge bg-warning"><?= count($pendingPayouts) ?> pending</span>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($pendingPayouts)): ?>
                                <div class="p-3 text-center text-muted">No pending payouts</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Payout ID</th>
                                                <th>Period</th>
                                                <th>Date Range</th>
                                                <th>Distributors</th>
                                                <th>Total Amount</th>
                                                <th>Created</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($pendingPayouts as $payout): ?>
                                            <tr>
                                                <td>#<?= str_pad($payout['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                                <td><?= ucfirst($payout['period']) ?></td>
                                                <td>
                                                    <?= $payout['from_date'] ? date('M j', strtotime($payout['from_date'])) : 'N/A' ?>
                                                    <?= $payout['to_date'] ? ' - ' . date('M j, Y', strtotime($payout['to_date'])) : '' ?>
                                                </td>
                                                <td><?= $payout['distributor_count'] ?></td>
                                                <td>₹<?= number_format($payout['total_amount'], 2) ?></td>
                                                <td><?= date('M j, Y', strtotime($payout['created_at'])) ?></td>
                                                <td>
                                                    <a href="payout-details.php?id=<?= $payout['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <form method="POST" style="display: inline-block;">
                                                        <input type="hidden" name="payout_id" value="<?= $payout['id'] ?>">
                                                        <button type="submit" name="mark_paid" class="btn btn-sm btn-outline-success" onclick="return confirm('Mark this payout as paid?')">
                                                            <i class="fas fa-check"></i> Mark Paid
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Completed Payouts -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Completed Payouts</h5>
                            <span class="badge bg-success"><?= count($completedPayouts) ?> completed</span>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($completedPayouts)): ?>
                                <div class="p-3 text-center text-muted">No completed payouts</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Payout ID</th>
                                                <th>Period</th>
                                                <th>Date Range</th>
                                                <th>Distributors</th>
                                                <th>Total Amount</th>
                                                <th>Completed</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($completedPayouts as $payout): ?>
                                            <tr>
                                                <td>#<?= str_pad($payout['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                                <td><?= ucfirst($payout['period']) ?></td>
                                                <td>
                                                    <?= $payout['from_date'] ? date('M j', strtotime($payout['from_date'])) : 'N/A' ?>
                                                    <?= $payout['to_date'] ? ' - ' . date('M j, Y', strtotime($payout['to_date'])) : '' ?>
                                                </td>
                                                <td><?= $payout['distributor_count'] ?></td>
                                                <td>₹<?= number_format($payout['total_amount'], 2) ?></td>
                                                <td><?= date('M j, Y', strtotime($payout['completed_at'])) ?></td>
                                                <td>
                                                    <a href="payout-details.php?id=<?= $payout['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
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
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Show/hide custom date range fields
    $('#period').change(function() {
        if ($(this).val() === 'custom') {
            $('#customDateRange, #customDateRange2').show();
        } else {
            $('#customDateRange, #customDateRange2').hide();
        }
    });
    
    // Initialize date range picker for custom period
    $('#from_date, #to_date').change(function() {
        if ($('#from_date').val() && $('#to_date').val()) {
            // Ensure to date is after from date
            if (new Date($('#from_date').val()) > new Date($('#to_date').val())) {
                alert('To date must be after from date');
                $(this).val('');
            }
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>