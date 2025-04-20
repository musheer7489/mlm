<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'distributor/dashboard.php'));
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $user = authenticateUser($username, $password);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['is_admin'] ? 'admin' : 'distributor';
        
        header('Location: ' . ($user['is_admin'] ? 'admin/dashboard.php' : 'distributor/dashboard.php'));
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="assets/images/logo.png" alt="Logo" width="100" class="mb-3">
                        <h3>Welcome Back</h3>
                        <p class="text-muted">Please login to your account</p>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="forgot-password.php" class="text-decoration-none">Forgot password?</a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0">Don't have an account? <a href="register.php" class="text-decoration-none">Register</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>