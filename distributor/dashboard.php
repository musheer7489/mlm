<?php
require_once '../includes/config.php';

// Check if user is logged in and is a distributor
if (!isLoggedIn() || !isDistributor()) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);
$product = getSingleProduct();
$sales = getRecentSales($userId);
$teamStats = getTeamStats($userId);
$commissionStats = getCommissionStats($userId);
$badges = getUserBadges($userId);
$trainingProgress = getTrainingProgress($userId);
$teamActivity = getTeamActivity($userId);
?>

<?php include '../includes/header.php'; ?>

<div class="dashboard-container">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'sidebar.php' ?>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Distributor Dashboard</h4>
                </div>
                <div class="card-body">
                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-success">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">Personal Sales</h6>
                                            <h3 class="mb-0"><?= $sales['personal']['count'] ?></h3>
                                            <small class="text-success">₹<?= number_format($sales['personal']['amount'], 2) ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-info">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">Team Sales</h6>
                                            <h3 class="mb-0"><?= $teamStats['total_members'] ?></h3>
                                            <small class="text-info">₹<?= number_format($teamStats['team_sales'], 2) ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-warning">
                                            <i class="fas fa-rupee-sign"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">Total Commissions</h6>
                                            <h3 class="mb-0">₹<?= number_format($commissionStats['total'], 2) ?></h3>
                                            <small><?= $commissionStats['paid'] ?> Paid / <?= $commissionStats['pending'] ?> Pending</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Highlight -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center">
                                    <img src="../assets/images/product/<?= $product['image'] ?>" class="img-fluid" alt="<?= $product['name'] ?>">
                                </div>
                                <div class="col-md-6">
                                    <h4><?= $product['name'] ?></h4>
                                    <p><?= $product['description'] ?></p>
                                    <h5 class="text-primary">₹<?= number_format($product['price'], 2) ?></h5>
                                </div>
                                <div class="col-md-3 text-center">
                                    <a href="../checkout.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-cart-plus me-2"></i>Buy Now
                                    </a>
                                    <div class="mt-2">
                                        <small>Share your referral link:</small>
                                        <div class="input-group mt-1">
                                            <input type="text" class="form-control" id="referralLink" value="<?= getReferralLink($user['username']) ?>" readonly>
                                            <button class="btn btn-outline-secondary" onclick="copyReferralLink()">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Recent Sales</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Customer</th>
                                                    <th>Amount</th>
                                                    <th>Commission</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($sales['recent'] as $sale) : ?>
                                                    <tr>
                                                        <td><?= date('d M Y', strtotime($sale['created_at'])) ?></td>
                                                        <td><?= $sale['customer_name'] ?></td>
                                                        <td>₹<?= number_format($sale['amount'], 2) ?></td>
                                                        <td class="text-success">+₹<?= number_format($sale['commission'], 2) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Team Growth</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="teamChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add this after the existing Team Growth chart -->
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Training Progress</h6>
                            </div>
                            <div class="card-body">
                                <div class="progress mb-3" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $trainingProgress['percentage'] ?>%;" aria-valuenow="<?= $trainingProgress['percentage'] ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?= $trainingProgress['percentage'] ?>%
                                    </div>
                                </div>
                                <p class="mb-1"><strong><?= $trainingProgress['completed'] ?></strong> of <strong><?= $trainingProgress['total'] ?></strong> modules completed</p>
                                <a href="../training.php" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-graduation-cap me-1"></i>Continue Training
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Add this as a new row after the existing rows -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Recent Team Activity</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Member</th>
                                                    <th>Activity</th>
                                                    <th>Date</th>
                                                    <th>Level</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($teamActivity as $activity) : ?>
                                                    <tr>
                                                        <td>
                                                            <img src="../assets/images/profile/<?= $activity['image'] ?? 'default.jpg' ?>" width="30" class="rounded-circle me-2">
                                                            <?= $activity['full_name'] ?>
                                                        </td>
                                                        <td><?= $activity['activity'] ?></td>
                                                        <td><?= date('M j, Y', strtotime($activity['created_at'])) ?></td>
                                                        <td>
                                                            <span class="badge bg-<?= getLevelBadgeColor($activity['level']) ?>">
                                                                Level <?= $activity['level'] ?>
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
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyReferralLink() {
        const copyText = document.getElementById("referralLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        alert("Referral link copied: " + copyText.value);
    }

    // Team Growth Chart
    $(document).ready(function() {
        const ctx = document.getElementById('teamChart').getContext('2d');
        const teamChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Team Members',
                    data: [12, 19, 15, 25, 30, 42],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>