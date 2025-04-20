<?php
if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token(): string {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
}

if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token(string $token): bool {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && 
               hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
}

// Generate token for forms
$csrf_token = generate_csrf_token();
?>