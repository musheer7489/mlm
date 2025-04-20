<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$topPerformers = getTopPerformers();
$monthlySales = getMonthlySalesReport();
$commissionPayouts = getCommissionPayoutsReport();
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
                    <h4 class="mb-0">Business Reports</h4>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4" id="reportsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="performance-tab" data-bs-toggle="tab" data-bs-target="#performance" type="button">Top Performers</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button">Sales Reports</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="commissions-tab" data-bs-toggle="tab" data-bs-target="#commissions" type="button">Commission Payouts</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="reportsTabContent">
                        <!-- Top Performers Tab -->
                        <div class="tab-pane fade show active" id="performance" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Distributor</th>
                                            <th>Sales Count</th>
                                            <th>Total Commissions</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($topPerformers as $index => $performer): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <img src="../assets/images/profile/<?= $performer['image'] ?? 'default.jpg' ?>" width="30" class="rounded-circle me-2">
                                                <?= $performer['full_name'] ?>
                                                <small class="text-muted d-block">ID: <?= $performer['username'] ?></small>
                                            </td>
                                            <td><?= $performer['sales_count'] ?></td>
                                            <td>₹<?= number_format($performer['total_commissions'], 2) ?></td>
                                            <td>
                                                <a href="view-distributor.php?id=<?= $performer['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4">
                                <button class="btn btn-primary" onclick="exportToExcel('performance')">
                                    <i class="fas fa-file-excel me-2"></i>Export to Excel
                                </button>
                            </div>
                        </div>
                        
                        <!-- Sales Reports Tab -->
                        <div class="tab-pane fade" id="sales" role="tabpanel">
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <canvas id="salesChart" height="300"></canvas>
                                </div>
                                <div class="col-md-4">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Month</th>
                                                    <th>Sales</th>
                                                    <th>Tax</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($monthlySales as $sale): ?>
                                                <tr>
                                                    <td><?= date('M Y', strtotime($sale['month'] . '-01')) ?></td>
                                                    <td>₹<?= number_format($sale['total_sales'], 2) ?></td>
                                                    <td>₹<?= number_format($sale['tax_collected'], 2) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button class="btn btn-primary" onclick="exportToExcel('sales')">
                                    <i class="fas fa-file-excel me-2"></i>Export to Excel
                                </button>
                            </div>
                        </div>
                        
                        <!-- Commission Payouts Tab -->
                        <div class="tab-pane fade" id="commissions" role="tabpanel">
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <canvas id="commissionsChart" height="300"></canvas>
                                </div>
                                <div class="col-md-4">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Month</th>
                                                    <th>Payouts</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($commissionPayouts as $payout): ?>
                                                <tr>
                                                    <td><?= date('M Y', strtotime($payout['month'] . '-01')) ?></td>
                                                    <td><?= $payout['payout_count'] ?></td>
                                                    <td>₹<?= number_format($payout['total_payouts'], 2) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button class="btn btn-primary" onclick="exportToExcel('commissions')">
                                    <i class="fas fa-file-excel me-2"></i>Export to Excel
                                </button>
                            </div>
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
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(function($sale) { 
                return date('M Y', strtotime($sale['month'] . '-01')); 
            }, $monthlySales)) ?>,
            datasets: [
                {
                    label: 'Product Sales',
                    data: <?= json_encode(array_column($monthlySales, 'total_sales')) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Tax Collected',
                    data: <?= json_encode(array_column($monthlySales, 'tax_collected')) ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Monthly Sales Report'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Commissions Chart
    const commissionsCtx = document.getElementById('commissionsChart').getContext('2d');
    const commissionsChart = new Chart(commissionsCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_map(function($payout) { 
                return date('M Y', strtotime($payout['month'] . '-01')); 
            }, $commissionPayouts)) ?>,
            datasets: [
                {
                    label: 'Commission Payouts',
                    data: <?= json_encode(array_column($commissionPayouts, 'total_payouts')) ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Monthly Commission Payouts'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});

function exportToExcel(type) {
    // In a real implementation, this would generate and download an Excel file
    alert(`Exporting ${type} report to Excel...`);
    // You would typically make an AJAX call to a server-side script that generates the Excel file
}
</script>

<?php include '../includes/footer.php'; ?>