</main>
        
        <!-- Footer -->
        <footer class="bg-dark text-white pt-5 pb-4">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <h5>About <?= SITE_NAME ?></h5>
                        <p>We provide premium health products with a business opportunity that allows you to earn while improving your health.</p>
                        <div class="social-icons">
                            <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                    <div class="col-md-2 mb-4">
                        <h5>Quick Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="index.php" class="text-white">Home</a></li>
                            <li><a href="product.php" class="text-white">Product</a></li>
                            <li><a href="register.php" class="text-white">Business Opportunity</a></li>
                            <li><a href="contact.php" class="text-white">Contact</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3 mb-4">
                        <h5>Support</h5>
                        <ul class="list-unstyled">
                            <li><a href="faq.php" class="text-white">FAQ</a></li>
                            <li><a href="terms.php" class="text-white">Terms & Conditions</a></li>
                            <li><a href="privacy.php" class="text-white">Privacy Policy</a></li>
                            <li><a href="shipping.php" class="text-white">Shipping & Returns</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3 mb-4">
                        <h5>Contact Us</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-map-marker-alt me-2"></i> 123 Business St, City</li>
                            <li><i class="fas fa-phone me-2"></i> +1 (123) 456-7890</li>
                            <li><i class="fas fa-envelope me-2"></i> info@example.com</li>
                        </ul>
                    </div>
                </div>
                <hr class="my-4">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <p class="mb-0">Designed with <i class="fas fa-heart text-danger"></i> by <?= SITE_NAME ?> Team</p>
                    </div>
                </div>
            </div>
        </footer>
        
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <!-- OrgChart JS -->
        <script src="https://balkan.app/js/OrgChart.js"></script>
        
        <!-- Custom JS -->
        <script src="<?=SITE_URL?>/assets/js/main.js"></script>
    </body>
</html>