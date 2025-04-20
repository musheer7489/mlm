<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];
$notifications = getUserNotifications($userId, 1); // Get only the latest notification

echo json_encode([
    'count' => count($notifications),
    'latest' => $notifications[0] ?? null
]);
?>