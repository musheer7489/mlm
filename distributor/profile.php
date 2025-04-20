<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isDistributor()) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);
$badges = getUserBadges($userId);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Handle password change
    $password = null;
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $_SESSION['error'] = 'Passwords do not match';
        } else {
            $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }
    }

    // Handle image upload
    $image = $user['image'];
    if (!empty($_FILES['profile_image']['name'])) {
        $uploadDir = '../assets/images/profile/';
        $uploadFile = $uploadDir . basename($_FILES['profile_image']['name']);

        // Check if file is an image
        $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
                $image = basename($_FILES['profile_image']['name']);
            }
        }
    }

    updateProfile($userId, $fullName, $email, $phone, $address, $password, $image);
    $_SESSION['success'] = 'Profile updated successfully';
    header('Location: profile.php');
    exit;
}
?>

<?php include '../includes/header.php'; ?>

<div class="dashboard-container">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">My Profile</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])) : ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])) : ?>
                        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="mb-3">
                                    <img src="../assets/images/profile/<?= $user['image'] ?? 'default.jpg' ?>" class="rounded-circle" width="150" height="150" id="profileImage">
                                    <div class="mt-2">
                                        <input type="file" class="form-control d-none" id="profileImageInput" name="profile_image" accept="image/*">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('profileImageInput').click()">
                                            <i class="fas fa-camera me-1"></i> Change Photo
                                        </button>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">Account Information</h6>
                                        <p class="mb-1"><strong>Username:</strong> <?= $user['username'] ?></p>
                                        <p class="mb-1"><strong>Join Date:</strong> <?= date('d M Y', strtotime($user['created_at'])) ?></p>
                                        <p class="mb-0"><strong>Sponsor:</strong> <?= getSponsorName($user['sponsor_id']) ?></p>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">Referral Link</h6>
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" id="referralLink" value="<?= getReferralLink($user['username']) ?>" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyReferralLink()">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">Share this link to refer new distributors</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
                                </div>

                                <!-- Add this to the form in distributor/profile.php -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Bank Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="bank_name" class="form-label">Bank Name</label>
                                                <input type="text" class="form-control" id="bank_name" name="bank_name" value="<?= htmlspecialchars($user['bank_name'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="account_number" class="form-label">Account Number</label>
                                                <input type="text" class="form-control" id="account_number" name="account_number" value="<?= htmlspecialchars($user['account_number'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="ifsc_code" class="form-label">IFSC Code</label>
                                                <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" value="<?= htmlspecialchars($user['ifsc_code'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="pan_number" class="form-label">PAN Number</label>
                                                <input type="text" class="form-control" id="pan_number" name="pan_number" value="<?= htmlspecialchars($user['pan_number'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Change Password</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password">
                                        </div>

                                        <div class="mb-0">
                                            <label for="confirm_password" class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Preview profile image before upload
    document.getElementById('profileImageInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('profileImage').src = event.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    // Copy referral link to clipboard
    function copyReferralLink() {
        const copyText = document.getElementById("referralLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        alert("Referral link copied: " + copyText.value);
    }
</script>

<?php include '../includes/footer.php'; ?>