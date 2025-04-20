<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: payouts.php');
    exit;
}

$payoutId = $_GET['id'];
$payout = getPayoutById($payoutId);
$payoutItems = getPayoutItems($payoutId);

if (!$payout) {
    header('Location: payouts.php');
    exit;
}

// Handle mark as paid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'])) {
    markPayoutAsPaid($payoutId);
    $_SESSION['success'] = 'Payout marked as paid';
    header("Location: payout-details.php?id=$payoutId");
    exit;
}
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
                        <h4 class="mb-0">Payout Details #<?= str_pad($payout['id'], 5, '0', STR_PAD_LEFT) ?></h4>
                        <span class="badge bg-<?= $payout['status'] === 'completed' ? 'success' : 'warning' ?>">
                            <?= ucfirst($payout['status']) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Payout Summary</h5>
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <th>Payout ID</th>
                                                <td>#<?= str_pad($payout['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td>
                                                    <span class="badge bg-<?= $payout['status'] === 'completed' ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($payout['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Period</th>
                                                <td><?= ucfirst($payout['period']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Date Range</th>
                                                <td>
                                                    <?= $payout['from_date'] ? date('M j, Y', strtotime($payout['from_date'])) : 'N/A' ?>
                                                    <?= $payout['to_date'] ? ' - ' . date('M j, Y', strtotime($payout['to_date'])) : '' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Created</th>
                                                <td><?= date('M j, Y H:i', strtotime($payout['created_at'])) ?></td>
                                            </tr>
                                            <?php if ($payout['status'] === 'completed'): ?>
                                            <tr>
                                                <th>Completed</th>
                                                <td><?= date('M j, Y H:i', strtotime($payout['completed_at'])) ?></td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Amount Summary</h5>
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <th>Distributors Paid</th>
                                                <td><?= $payout['distributor_count'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Total Amount</th>
                                                <td>₹<?= number_format($payout['total_amount'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Average Payout</th>
                                                <td>₹<?= number_format($payout['total_amount'] / $payout['distributor_count'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Minimum Amount</th>
                                                <td>₹<?= number_format($payout['min_amount'], 2) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    <?php if ($payout['status'] !== 'completed'): ?>
                                    <form method="POST" class="text-end mt-3">
                                        <button type="submit" name="mark_paid" class="btn btn-success" onclick="return confirm('Mark this payout as paid?')">
                                            <i class="fas fa-check-circle me-2"></i>Mark as Paid
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Distributor Payouts</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Distributor ID</th>
                                            <th>Name</th>
                                            <th>Commissions</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($payoutItems as $item): ?>
                                        <tr>
                                            <td><?= $item['username'] ?></td>
                                            <td>
                                                <img src="../assets/images/profile/<?= $item['image'] ?? 'default.jpg' ?>" width="30" class="rounded-circle me-2">
                                                <?= $item['full_name'] ?>
                                            </td>
                                            <td><?= $item['commission_count'] ?></td>
                                            <td>₹<?= number_format($item['amount'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $item['status'] === 'paid' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($item['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="../distributor/view.php?id=<?= $item['user_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end mt-3">
                        <button class="btn btn-outline-primary me-2" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                        <a href="export-payout.php?id=<?= $payoutId ?>" class="btn btn-outline-success">
                            <i class="fas fa-file-excel me-2"></i>Export to Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>