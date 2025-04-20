<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get product details
$product = getSingleProduct();
$testimonials = getFeaturedTestimonials(3);
$distributorBenefits = getDistributorBenefits();

// Check if user is logged in
$isLoggedIn = isLoggedIn();
$user = $isLoggedIn ? getUserById($_SESSION['user_id']) : null;
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Transform Your Health & Wealth</h1>
                <p class="lead mb-4">Discover our revolutionary health product and start your journey to better wellness and financial freedom.</p>
                <div class="d-flex gap-3">
                    <a href="#product" class="btn btn-light btn-lg px-4">Learn More</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="<?= isAdmin() ? 'admin/dashboard.php' : 'distributor/dashboard.php' ?>" class="btn btn-outline-light btn-lg px-4">
                            My Dashboard
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-outline-light btn-lg px-4">
                            Join Our Team
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/product-hero.png" alt="HealthPlus Product" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Product Highlights -->
<section id="product" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Flagship Product</h2>
            <p class="lead text-muted">The science-backed solution for optimal health</p>
        </div>
        
        <div class="row align-items-center">
            <div class="col-lg-5">
                <img src="assets/images/product/<?= $product['image'] ?>" alt="<?= $product['name'] ?>" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-7">
                <h3 class="fw-bold"><?= $product['name'] ?></h3>
                <div class="d-flex align-items-center mb-3">
                    <div class="rating me-3">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?= $i <= $product['rating'] ? '' : '-empty' ?> text-warning"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="text-muted"><?= $product['review_count'] ?> reviews</span>
                </div>
                
                <h4 class="text-primary mb-4">₹<?= number_format($product['price'], 2) ?></h4>
                
                <p class="mb-4"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle p-2 me-3">
                                <i class="fas fa-check fa-lg"></i>
                            </div>
                            <span>100% Natural Ingredients</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle p-2 me-3">
                                <i class="fas fa-check fa-lg"></i>
                            </div>
                            <span>Scientifically Proven</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle p-2 me-3">
                                <i class="fas fa-check fa-lg"></i>
                            </div>
                            <span>Money Back Guarantee</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle p-2 me-3">
                                <i class="fas fa-check fa-lg"></i>
                            </div>
                            <span>Fast Shipping</span>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-3">
                    <a href="product.php" class="btn btn-outline-primary btn-lg px-4">View Details</a>
                    <a href="checkout.php" class="btn btn-primary btn-lg px-4">Buy Now</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How It Works</h2>
            <p class="lead text-muted">Simple steps to better health and income</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary text-white rounded-circle p-3 mb-3 mx-auto" style="width: 80px; height: 80px;">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                        <h4 class="fw-bold mb-3">1. Purchase Product</h4>
                        <p>Start by purchasing our health product to experience its benefits firsthand.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary text-white rounded-circle p-3 mb-3 mx-auto" style="width: 80px; height: 80px;">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <h4 class="fw-bold mb-3">2. Share With Others</h4>
                        <p>Recommend the product to friends and family using your unique referral link.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary text-white rounded-circle p-3 mb-3 mx-auto" style="width: 80px; height: 80px;">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                        <h4 class="fw-bold mb-3">3. Earn Commissions</h4>
                        <p>Earn money from your sales and build a team for residual income.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Success Stories</h2>
            <p class="lead text-muted">What our customers and distributors say</p>
        </div>
        
        <div class="row g-4">
            <?php foreach($testimonials as $testimonial): ?>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rating me-3">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?= $i <= $testimonial['rating'] ? '' : '-empty' ?> text-warning"></i>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted"><?= date('M Y', strtotime($testimonial['created_at'])) ?></small>
                        </div>
                        <p class="mb-4">"<?= htmlspecialchars($testimonial['comment']) ?>"</p>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle p-2 me-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= $testimonial['name'] ?></h6>
                                <small class="text-muted">
                                    <?= $testimonial['is_distributor'] ? 'Distributor' : 'Customer' ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="product.php#reviews" class="btn btn-outline-primary px-4">View All Reviews</a>
        </div>
    </div>
</section>

<!-- Distributor Benefits -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Become a Distributor?</h2>
            <p class="lead text-muted">Unlock your earning potential with our MLM program</p>
        </div>
        
        <div class="row g-4">
            <?php foreach($distributorBenefits as $benefit): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary text-white rounded-circle p-3 mb-3 mx-auto" style="width: 70px; height: 70px;">
                            <i class="fas <?= $benefit['icon'] ?> fa-lg"></i>
                        </div>
                        <h5 class="fw-bold mb-3"><?= $benefit['title'] ?></h5>
                        <p class="mb-0"><?= $benefit['description'] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <?php if ($isLoggedIn && !isAdmin()): ?>
                <a href="distributor/dashboard.php" class="btn btn-primary px-4">Go to Dashboard</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary px-4">Join Our Team</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">Ready to Transform Your Life?</h2>
        <p class="lead mb-5">Start your journey to better health and financial freedom today.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="product.php" class="btn btn-light px-4">Learn About Our Product</a>
            <a href="register.php" class="btn btn-outline-light px-4">Become a Distributor</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>