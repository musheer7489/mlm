<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$product = getSingleProduct();
$testimonials = getProductTestimonials();
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <!-- Product Details -->
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="product-image-container">
                <img src="assets/images/product/<?= $product['image'] ?>" alt="<?= $product['name'] ?>" class="img-fluid rounded">
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge bg-success stock-badge">In Stock</span>
                <?php else: ?>
                    <span class="badge bg-danger stock-badge">Out of Stock</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <h1 class="product-title"><?= $product['name'] ?></h1>
            
            <div class="d-flex align-items-center mb-3">
                <div class="rating me-3">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star<?= $i <= $product['rating'] ? '' : '-empty' ?> text-warning"></i>
                    <?php endfor; ?>
                </div>
                <span class="text-muted"><?= $product['review_count'] ?> reviews</span>
            </div>
            
            <h3 class="product-price mb-4">₹<?= number_format($product['price'], 2) ?></h3>
            
            <div class="product-description mb-4">
                <h5>Description</h5>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>
            
            <div class="product-benefits mb-4">
                <h5>Key Benefits</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i> 100% Natural Ingredients</li>
                    <li><i class="fas fa-check text-success me-2"></i> Scientifically Proven Formula</li>
                    <li><i class="fas fa-check text-success me-2"></i> No Side Effects</li>
                    <li><i class="fas fa-check text-success me-2"></i> Money Back Guarantee</li>
                </ul>
            </div>
            
            <div class="d-flex align-items-center mb-4">
                <div class="input-group me-3" style="width: 120px;">
                    <button class="btn btn-outline-secondary" type="button" id="decrement">-</button>
                    <input type="text" class="form-control text-center" value="1" id="quantity">
                    <button class="btn btn-outline-secondary" type="button" id="increment">+</button>
                </div>
                
                <?php if (isLoggedIn()): ?>
                    <a href="checkout.php" class="btn btn-primary btn-lg flex-grow-1">
                        <i class="fas fa-shopping-cart me-2"></i>Buy Now
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-lg flex-grow-1">
                        <i class="fas fa-shopping-cart me-2"></i>Buy Now
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="product-share">
                <span class="me-2">Share:</span>
                <a href="#" class="text-decoration-none me-2"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-decoration-none me-2"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-decoration-none me-2"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-decoration-none"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
    </div>
    
    <!-- Product Tabs -->
    <div class="row mb-5">
        <div class="col-12">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">Details</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ingredients-tab" data-bs-toggle="tab" data-bs-target="#ingredients" type="button" role="tab">Ingredients</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="usage-tab" data-bs-toggle="tab" data-bs-target="#usage" type="button" role="tab">Usage</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">Reviews</button>
                </li>
            </ul>
            
            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="productTabsContent">
                <div class="tab-pane fade show active" id="details" role="tabpanel">
                    <h5>Product Details</h5>
                    <p>Our premium health product is formulated with the finest natural ingredients to provide you with optimal health benefits. Each batch is carefully tested to ensure the highest quality standards.</p>
                    <p>The product comes with a 30-day money-back guarantee if you're not completely satisfied with the results. Thousands of customers have already experienced the transformative effects of our formula.</p>
                </div>
                
                <div class="tab-pane fade" id="ingredients" role="tabpanel">
                    <h5>Key Ingredients</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><strong>Turmeric Extract:</strong> Powerful anti-inflammatory properties</li>
                                <li><strong>Ashwagandha:</strong> Reduces stress and anxiety</li>
                                <li><strong>Ginger Root:</strong> Aids digestion and reduces nausea</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><strong>Black Pepper:</strong> Enhances nutrient absorption</li>
                                <li><strong>Holy Basil:</strong> Supports immune function</li>
                                <li><strong>Cinnamon:</strong> Helps regulate blood sugar</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="usage" role="tabpanel">
                    <h5>Recommended Usage</h5>
                    <p>For optimal results, take 2 capsules daily with meals. It's recommended to take one capsule in the morning and one in the evening.</p>
                    <p>For best results, use consistently for at least 60 days. Results may vary based on individual body chemistry and lifestyle factors.</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Consult your healthcare provider before use if you are pregnant, nursing, or taking medications.
                    </div>
                </div>
                
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <h5>Customer Reviews</h5>
                    
                    <?php foreach($testimonials as $testimonial): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <strong><?= $testimonial['name'] ?></strong>
                                    <div class="rating">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?= $i <= $testimonial['rating'] ? '' : '-empty' ?> text-warning"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <small class="text-muted"><?= date('M d, Y', strtotime($testimonial['created_at'])) ?></small>
                            </div>
                            <p class="mb-0"><?= htmlspecialchars($testimonial['comment']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                        <i class="fas fa-pen me-2"></i>Write a Review
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Business Opportunity Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3>Join Our Business Opportunity</h3>
                            <p>Earn commissions by sharing our amazing product with others. Our MLM program offers multiple levels of income potential with no inventory requirements.</p>
                            <a href="register.php" class="btn btn-primary">Become a Distributor</a>
                        </div>
                        <div class="col-md-4 text-center">
                            <img src="assets/images/business-opportunity.png" alt="Business Opportunity" class="img-fluid" width="200">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Write a Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviewForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="rating-input">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="far fa-star" data-rating="<?= $i ?>"></i>
                            <?php endfor; ?>
                            <input type="hidden" name="rating" id="ratingValue" value="5">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reviewName" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="reviewName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reviewComment" class="form-label">Your Review</label>
                        <textarea class="form-control" id="reviewComment" name="comment" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Quantity controls
document.getElementById('decrement').addEventListener('click', function() {
    const quantityInput = document.getElementById('quantity');
    let quantity = parseInt(quantityInput.value);
    if (quantity > 1) {
        quantityInput.value = quantity - 1;
    }
});

document.getElementById('increment').addEventListener('click', function() {
    const quantityInput = document.getElementById('quantity');
    let quantity = parseInt(quantityInput.value);
    quantityInput.value = quantity + 1;
});

// Rating stars in review modal
document.querySelectorAll('.rating-input i').forEach(star => {
    star.addEventListener('click', function() {
        const rating = parseInt(this.getAttribute('data-rating'));
        document.getElementById('ratingValue').value = rating;
        
        document.querySelectorAll('.rating-input i').forEach((s, index) => {
            if (index < rating) {
                s.classList.remove('far');
                s.classList.add('fas');
            } else {
                s.classList.remove('fas');
                s.classList.add('far');
            }
        });
    });
});

// Review form submission
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // In a real implementation, you would submit this via AJAX
    alert('Thank you for your review!');
    $('#reviewModal').modal('hide');
    this.reset();
    
    // Reset stars
    document.querySelectorAll('.rating-input i').forEach((s, index) => {
        if (index < 5) {
            s.classList.remove('far');
            s.classList.add('fas');
        } else {
            s.classList.remove('fas');
            s.classList.add('far');
        }
    });
    document.getElementById('ratingValue').value = 5;
});
</script>

<?php include 'includes/footer.php'; ?>