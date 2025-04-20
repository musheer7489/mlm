<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= isset($metaDescription) ? $metaDescription : 'Premium health product with MLM business opportunity' ?>">
    <meta name="keywords" content="<?= isset($metaKeywords) ? $metaKeywords : 'health, wellness, mlm, business opportunity' ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="<?=SITE_URL?>/assets/images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
    
    <!-- OrgChart CSS -->
    <link rel="stylesheet" href="https://balkan.app/css/OrgChart.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?=SITE_URL?>/index.php">
                <img src="<?= SITE_URL?>/assets/images/logo.png" alt="Logo" height="40" class="me-2">
                <?= SITE_NAME ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL?>/product.php">Product</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL?>/contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL?>/register.php">Business Opportunity</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?= htmlspecialchars($_SESSION['user_name'] ?? 'My Account') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="distributor/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= isAdmin() ? 'admin/profile.php' : 'distributor/profile.php' ?>"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><i class="fas fa-user-plus me-1"></i>Register</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL?>/distributor/orders.php"><i class="fas fa-shopping-cart me-1"></i> Cart</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main>