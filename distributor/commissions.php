<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isDistributor()) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$commissions = getCommissions($userId);
$levels = getCommissionLevels();
$user = getUserById($userId);
$badges = getUserBadges($userId);
?>

<?php include '../includes/header.php'; ?>

<div class="dashboard-container">
    <div class="row">
        <!-- Sidebar (same as dashboard) -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">My Commissions</h4>
                        <div>
                            <span class="badge bg-light text-dark">Total: ₹<?= number_format(array_sum(array_column($commissions, 'amount')), 2) ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Commission Summary -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Commission Summary</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Level</th>
                                                    <th>Commission %</th>
                                                    <th>Members</th>
                                                    <th>Sales Volume</th>
                                                    <th>Earned</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($levels as $level): 
                                                    $levelCommissions = array_filter($commissions, function($c) use ($level) {
                                                        return $c['level'] == $level['level'];
                                                    });
                                                    $levelAmount = array_sum(array_column($levelCommissions, 'amount'));
                                                    $levelCount = count($levelCommissions);
                                                    $levelSales = array_sum(array_column($levelCommissions, 'order_amount'));
                                                ?>
                                                <tr>
                                                    <td>Level <?= $level['level'] ?></td>
                                                    <td><?= $level['percentage'] ?>%</td>
                                                    <td><?= $levelCount ?></td>
                                                    <td>₹<?= number_format($levelSales, 2) ?></td>
                                                    <td class="text-success">₹<?= number_format($levelAmount, 2) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-active">
                                                    <th colspan="3">Total</th>
                                                    <th>₹<?= number_format(array_sum(array_column($commissions, 'order_amount')), 2) ?></th>
                                                    <th class="text-success">₹<?= number_format(array_sum(array_column($commissions, 'amount')), 2) ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Commission Details -->
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Commission Details</h6>
                                <div>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                                        <button type="button" class="btn btn-outline-primary" data-filter="paid">Paid</button>
                                        <button type="button" class="btn btn-outline-primary" data-filter="pending">Pending</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Level</th>
                                            <th>From Member</th>
                                            <th>Order Amount</th>
                                            <th>Commission %</th>
                                            <th>Commission</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($commissions as $commission): ?>
                                        <tr data-status="<?= strtolower($commission['status']) ?>">
                                            <td><?= date('d M Y', strtotime($commission['created_at'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= getLevelBadgeColor($commission['level']) ?>">
                                                    Level <?= $commission['level'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= $commission['from_member'] ?>
                                                <small class="text-muted d-block">Order #<?= $commission['order_id'] ?></small>
                                            </td>
                                            <td>₹<?= number_format($commission['order_amount'], 2) ?></td>
                                            <td><?= $commission['percentage'] ?>%</td>
                                            <td class="text-success">₹<?= number_format($commission['amount'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $commission['status'] == 'paid' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($commission['status']) ?>
                                                </span>
                                                <?php if($commission['status'] == 'paid'): ?>
                                                    <small class="text-muted d-block"><?= date('d M Y', strtotime($commission['paid_at'])) ?></small>
                                                <?php endif; ?>
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
// Filter commissions by status
$(document).ready(function() {
    $('[data-filter]').click(function() {
        const filter = $(this).data('filter');
        $('[data-filter]').removeClass('active');
        $(this).addClass('active');
        
        if (filter === 'all') {
            $('tbody tr').show();
        } else if (filter === 'paid') {
            $('tbody tr').hide();
            $('tbody tr[data-status="paid"]').show();
        } else if (filter === 'pending') {
            $('tbody tr').hide();
            $('tbody tr[data-status="pending"]').show();
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>