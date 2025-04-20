<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$validToken = false;

// Validate token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $resetData = validatePasswordResetToken($token);
    
    if ($resetData) {
        $validToken = true;
        $userId = $resetData['user_id'];
        
        // Handle password reset
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if ($password !== $confirmPassword) {
                $error = "Passwords do not match";
            } else {
                // Update password
                updateUserPassword($userId, $password);
                
                // Delete token
                deletePasswordResetToken($token);
                
                $success = "Your password has been reset successfully. <a href='login.php'>Login now</a>.";
                $validToken = false;
            }
        }
    }
}

if (!$validToken && !$success) {
    $error = "Invalid or expired password reset link.";
}
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">Reset Password</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php elseif ($validToken): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Update Password
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>