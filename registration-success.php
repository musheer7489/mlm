<?php
require_once 'includes/config.php';

if (!isset($_SESSION['registration_data'])) {
    header('Location: register.php');
    exit;
}

$userData = $_SESSION['registration_data'];
unset($_SESSION['registration_data']);
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Registration Successful!</h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                        <h3>Welcome to <?= SITE_NAME ?>, <?= htmlspecialchars($userData['full_name']) ?>!</h3>
                        <p class="lead">Your distributor account has been successfully created.</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mx-auto">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Your Account Details</h5>
                                    <ul class="list-unstyled">
                                        <li><strong>Username:</strong> <?= $userData['username'] ?></li>
                                        <li><strong>Distributor ID:</strong> <?= strtoupper(substr(md5($userData['username']), 0, 8)) ?></li>
                                        <li><strong>Sponsor:</strong> <?= getSponsorName($userData['sponsor_id']) ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Next Steps</h5>
                            <div class="row text-start">
                                <div class="col-md-6">
                                    <div class="d-flex mb-3">
                                        <div class="me-3 text-primary">
                                            <i class="fas fa-shopping-cart fa-2x"></i>
                                        </div>
                                        <div>
                                            <h6>Purchase Your Starter Kit</h6>
                                            <p class="mb-0">Get started by purchasing your distributor starter kit.</p>
                                            <a href="checkout.php" class="btn btn-sm btn-outline-primary mt-2">Order Now</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex mb-3">
                                        <div class="me-3 text-primary">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                        <div>
                                            <h6>Build Your Team</h6>
                                            <p class="mb-0">Start sharing your referral link to build your team.</p>
                                            <button class="btn btn-sm btn-outline-primary mt-2" onclick="copyReferralLink()">
                                                <i class="fas fa-copy me-1"></i>Copy Referral Link
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex mb-3">
                                        <div class="me-3 text-primary">
                                            <i class="fas fa-graduation-cap fa-2x"></i>
                                        </div>
                                        <div>
                                            <h6>Complete Training</h6>
                                            <p class="mb-0">Watch our training videos to learn how to succeed.</p>
                                            <a href="training.php" class="btn btn-sm btn-outline-primary mt-2">Start Training</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex mb-3">
                                        <div class="me-3 text-primary">
                                            <i class="fas fa-tachometer-alt fa-2x"></i>
                                        </div>
                                        <div>
                                            <h6>Access Your Dashboard</h6>
                                            <p class="mb-0">Track your sales, team, and commissions.</p>
                                            <a href="distributor/dashboard.php" class="btn btn-sm btn-outline-primary mt-2">Go to Dashboard</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                        <a href="distributor/dashboard.php" class="btn btn-primary btn-lg px-4 gap-3">
                            <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                        </a>
                        <a href="product.php" class="btn btn-outline-secondary btn-lg px-4">
                            <i class="fas fa-box me-2"></i>View Product
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralLink() {
    const link = "<?= getReferralLink($userData['username']) ?>";
    navigator.clipboard.writeText(link);
    alert("Referral link copied to clipboard: " + link);
}
</script>

<?php include 'includes/footer.php'; ?>