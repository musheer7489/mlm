<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        // Send email (in a real implementation)
        // mail(SITE_EMAIL, "Contact Form: $subject", "From: $name <$email>\n\n$message");
        
        $_SESSION['success'] = "Thank you for your message! We'll get back to you soon.";
        header('Location: contact.php');
        exit;
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Contact Us</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                    <h5>Our Location</h5>
                    <p class="mb-0">123 Business Street<br>City, State 12345<br>Country</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-phone fa-3x text-primary mb-3"></i>
                    <h5>Phone Support</h5>
                    <p class="mb-0">+1 (123) 456-7890<br>Mon-Fri: 9am-5pm<br>Sat: 10am-2pm</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                    <h5>Email Support</h5>
                    <p class="mb-0">support@example.com<br>info@example.com<br>sales@example.com</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>