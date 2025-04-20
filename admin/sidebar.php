<?php
$currentPage = $_SERVER['PHP_SELF'];
?>
<div class="col-md-3">
    <div class="card">
        <div class="card-body text-center">
                    <img src="../assets/images/profile/default-admin.jpg" class="rounded-circle mb-3" width="100" height="100">
                    <h5>Admin Panel</h5>
                    
                    <div class="list-group mt-3">
                        <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo $currentPage === '/mlm/admin/dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a href="products.php" class="list-group-item list-group-item-action <?php echo $currentPage === '/mlm/admin/products.php' ? 'active' : ''; ?>">
                            <i class="fas fa-box me-2"></i>Products
                        </a>
                        <a href="users.php" class="list-group-item list-group-item-action <?php echo $currentPage === '/mlm/admin/users.php' ? 'active' : ''; ?>">
                            <i class="fas fa-users me-2"></i>Distributors
                        </a>
                        <a href="commissions.php" class="list-group-item list-group-item-action <?php echo $currentPage === '/mlm/admin/commissions.php' ? 'active' : ''; ?>">
                            <i class="fas fa-money-bill-wave me-2"></i>Commissions
                        </a>
                        <a href="settings.php" class="list-group-item list-group-item-action <?php echo $currentPage === '/mlm/admin/settings.php' ? 'active' : ''; ?>">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                        <a href="../logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>