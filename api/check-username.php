<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$username = $_POST['username'] ?? '';
$response = ['available' => false];

if (!empty($username)) {
    $response['available'] = !usernameExists($username);
}

echo json_encode($response);
?>