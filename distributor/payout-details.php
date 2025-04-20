<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isDistributor()) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: payouts.php');
    exit;
}

$userId = $_SESSION['user_id'];
$payoutItemId = $_GET['id'];
$payoutDetails = getDistributorPayoutDetails($userId, $payoutItemId);

if (!$payoutDetails) {
    header('Location: payouts.php');
    exit;
}

$commissions = getCommissionsForPayoutItem($payoutItemId);
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
                    <h4 class="mb-0">Payout Details #<?= str_pad($payoutDetails['payout_id'], 5, '0', STR_PAD_LEFT) ?></h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Payout Summary</h5>
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <th>Payout ID</th>
                                                <td>#<?= str_pad($payoutDetails['payout_id'], 5, '0', STR_PAD_LEFT) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td>
                                                    <span class="badge bg-<?= $payoutDetails['status'] === 'paid' ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($payoutDetails['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Period</th>
                                                <td><?= ucfirst($payoutDetails['period']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Payment Date</th>
                                                <td><?= date('M j, Y', strtotime($payoutDetails['paid_at'])) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Payment Method</th>
                                                <td>Bank Transfer</td>
                                            </tr>
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
                                                <th>Total Amount</th>
                                                <td>₹<?= number_format($payoutDetails['amount'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Commissions</th>
                                                <td><?= $payoutDetails['commission_count'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Bank Name</th>
                                                <td><?= $payoutDetails['bank_name'] ?? 'Not specified' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Account Number</th>
                                                <td><?= $payoutDetails['account_number'] ? '••••'.substr($payoutDetails['account_number'], -4) : 'Not specified' ?></td>
                                            </tr>
                                            <tr>
                                                <th>IFSC Code</th>
                                                <td><?= $payoutDetails['ifsc_code'] ?? 'Not specified' ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Commission Details</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>From</th>
                                            <th>Level</th>
                                            <th>Order Amount</th>
                                            <th>Commission %</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($commissions as $commission): ?>
                                        <tr>
                                            <td><?= date('M j, Y', strtotime($commission['created_at'])) ?></td>
                                            <td><?= $commission['from_member'] ?></td>
                                            <td>
                                                <span class="badge bg-<?= getLevelBadgeColor($commission['level']) ?>">
                                                    Level <?= $commission['level'] ?>
                                                </span>
                                            </td>
                                            <td>₹<?= number_format($commission['order_amount'], 2) ?></td>
                                            <td><?= $commission['percentage'] ?>%</td>
                                            <td>₹<?= number_format($commission['amount'], 2) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <th colspan="5">Total</th>
                                            <th>₹<?= number_format($payoutDetails['amount'], 2) ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end mt-3">
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>