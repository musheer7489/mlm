<?php
// Enable strict mode
declare(strict_types=1);

// Secure session configuration
session_start([
    'cookie_secure' => true,    // Only send cookies over HTTPS
    'cookie_httponly' => true,  // Prevent JavaScript access to cookies
    'cookie_samesite' => 'Lax', // CSRF protection
    'use_strict_mode' => true   // Prevent session fixation
]);

// Disable error display in production
/*
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php-errors.log'); */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Never use root in production
define('DB_PASS', ''); // Use strong password
define('DB_NAME', 'mlm');

// Site configuration
define('SITE_NAME', 'HealthPlus MLM');
define('SITE_URL', 'http://localhost/mlm'); // Always HTTPS
define('SITE_EMAIL', 'support@yourdomain.com');
define('CSRF_TOKEN_NAME', 'csrf_token');

// Razorpay configuration
define('RAZORPAY_KEY_ID', 'live_key_here'); // Use live keys in production
define('RAZORPAY_KEY_SECRET', 'live_secret_here');

// Create database connection with error handling
try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    header('HTTP/1.1 503 Service Unavailable');
    die("We're experiencing technical difficulties. Please try again later.");
}

// Include other required files
require_once 'functions.php';
require_once 'csrf.php';
?>