<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName = $_POST['site_name'];
    $siteEmail = $_POST['site_email'];
    $razorpayKeyId = $_POST['razorpay_key_id'];
    $razorpayKeySecret = $_POST['razorpay_key_secret'];
    $minPayoutAmount = $_POST['min_payout_amount'];
    
    updateSettings([
        'site_name' => $siteName,
        'site_email' => $siteEmail,
        'razorpay_key_id' => $razorpayKeyId,
        'razorpay_key_secret' => $razorpayKeySecret,
        'min_payout_amount' => $minPayoutAmount
    ]);
    
    $_SESSION['success'] = 'Settings updated successfully';
    header('Location: settings.php');
    exit;
}

// Get current settings
$settings = getSettings();
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
                    <h4 class="mb-0">System Settings</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Site Name</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['site_name']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_email" class="form-label">Site Email</label>
                                    <input type="email" class="form-control" id="site_email" name="site_email" value="<?= htmlspecialchars($settings['site_email']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="min_payout_amount" class="form-label">Minimum Payout Amount (₹)</label>
                                    <input type="number" class="form-control" id="min_payout_amount" name="min_payout_amount" step="0.01" min="0" value="<?= $settings['min_payout_amount'] ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Payment Gateway Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="razorpay_key_id" class="form-label">Razorpay Key ID</label>
                                    <input type="text" class="form-control" id="razorpay_key_id" name="razorpay_key_id" value="<?= htmlspecialchars($settings['razorpay_key_id']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="razorpay_key_secret" class="form-label">Razorpay Key Secret</label>
                                    <input type="password" class="form-control" id="razorpay_key_secret" name="razorpay_key_secret" value="<?= htmlspecialchars($settings['razorpay_key_secret']) ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                    
                    <!-- System Information -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">System Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="30%">PHP Version</th>
                                            <td><?= phpversion() ?></td>
                                        </tr>
                                        <tr>
                                            <th>MySQL Version</th>
                                            <td><?= getMySQLVersion() ?></td>
                                        </tr>
                                        <tr>
                                            <th>Server Software</th>
                                            <td><?= $_SERVER['SERVER_SOFTWARE'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Last Cron Run</th>
                                            <td><?= $settings['last_cron_run'] ? date('d M Y H:i:s', strtotime($settings['last_cron_run'])) : 'Never' ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="text-end mt-3">
                                <button class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-sync-alt me-2"></i>Check for Updates
                                </button>
                                <button class="btn btn-outline-danger">
                                    <i class="fas fa-database me-2"></i>Backup Database
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>