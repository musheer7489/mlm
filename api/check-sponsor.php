<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$sponsor = $_POST['sponsor'] ?? '';
$response = ['valid' => false];

if (!empty($sponsor)) {
    $user = getUserByUsername($sponsor);
    $response['valid'] = ($user && !$user['is_admin']);
}

echo json_encode($response);
?>