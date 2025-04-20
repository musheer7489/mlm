<?php
require_once 'includes/config.php';

// Handle referral link (e.g., register.php?sponsor=username)
$referralSponsor = isset($_GET['sponsor']) ? trim($_GET['sponsor']) : '';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'distributor/dashboard.php'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate inputs
        $required = ['full_name', 'username', 'email', 'password', 'confirm_password'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields");
            }
        }

        if ($_POST['password'] !== $_POST['confirm_password']) {
            throw new Exception("Passwords do not match");
        }

        // Check if username exists
        if (usernameExists($_POST['username'])) {
            throw new Exception("Username already exists");
        }

        // Check if email exists
        if (emailExists($_POST['email'])) {
            throw new Exception("Email already registered");
        }

        // Process sponsor (from form or referral link)
        $sponsorId = null;
        $sponsorUsername = !empty($_POST['sponsor_username']) ? $_POST['sponsor_username'] : $referralSponsor;
        
        if (!empty($sponsorUsername)) {
            $sponsor = getUserByUsername($sponsorUsername);
            if (!$sponsor) {
                throw new Exception("Sponsor username not found");
            }
            $sponsorId = $sponsor['id'];
        }

        // Create user
        $userId = createUser([
            'full_name' => $_POST['full_name'],
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'password' => $_POST['password'],
            'sponsor_id' => $sponsorId
        ]);

        // Store registration data in session
        $_SESSION['registration_data'] = [
            'full_name' => $_POST['full_name'],
            'username' => $_POST['username'],
            'sponsor_id' => $sponsorId
        ];

        // Redirect to success page
        header('Location: registration-success.php');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Distributor Registration</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <?php if (!empty($referralSponsor)): ?>
                        <div class="alert alert-info">
                            You're joining under sponsor: <strong><?= htmlspecialchars($referralSponsor) ?></strong>
                        </div>
                    <?php endif; ?>

                    <form id="registrationForm" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                                <div class="form-text">This will be your unique distributor ID</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sponsor_username" class="form-label">Sponsor Username (Optional)</label>
                            <input type="text" class="form-control" id="sponsor_username" name="sponsor_username" 
                                   value="<?= htmlspecialchars($_POST['sponsor_username'] ?? $referralSponsor) ?>">
                            <div class="form-text">Leave blank if you don't have a sponsor</div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="agree_terms" name="agree_terms" required>
                            <label class="form-check-label" for="agree_terms">I agree to the <a href="terms.php" target="_blank">Terms & Conditions</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Register as Distributor</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>