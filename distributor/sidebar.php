<?php
$currentPage = $_SERVER['PHP_SELF'];
?>
<div class="col-md-3">
    <div class="card">
        <div class="card-body text-center">
            <img src="../assets/images/profile/<?= $user['image'] ?? 'default.jpg' ?>" class="rounded-circle mb-3" width="100" height="100">
            <h5><?= htmlspecialchars($user['full_name']) ?></h5>
            <p class="text-muted">Distributor ID: <?= $user['username'] ?></p>

            <div class="d-flex justify-content-center mb-3">
                <?php foreach ($badges as $badge) : ?>
                    <img src="../assets/images/badges/<?= $badge['image'] ?>" title="<?= $badge['name'] ?>" width="40" class="mx-1">
                <?php endforeach; ?>
            </div>

            <div class="list-group">
                <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo $currentPage === '/mlm/distributor/dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <a href="team.php" class="list-group-item list-group-item-action <?php echo $currentPage === '/mlm/distributor/team.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users me-2"></i>My Team
                </a>
                <a href="commissions.php" class="list-group-item list-group-item-action <?php echo $currentPage === '/mlm/distributor/commissions.php' ? 'active' : ''; ?>">
                    <i class="fas fa-money-bill-wave me-2"></i>Commissions
                </a>
                <a href="profile.php" class="list-group-item list-group-item-action <?php echo $currentPage === '/mlm/distributor/profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user me-2"></i>Profile
                </a>
                <a href="orders.php" class="list-group-item list-group-item-action <?php echo $currentPage === '/mlm/distributor/orders.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart me-2"></i> </i>My Orders
                </a>
                <a href="payouts.php" class="list-group-item list-group-item-action <?php echo $currentPage === '/mlm/distributor/payouts.php' ? 'active' : ''; ?>">
                    <i class="fas fa-money-check-dollar me-2"></i> Payments
                </a>
                <a href="../training.php" class="list-group-item list-group-item-action <?php echo $currentPage === '/mlm/training.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-simple"></i> training
                </a>
                <a href="../logout.php" class="list-group-item list-group-item-action text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </div>
</div>