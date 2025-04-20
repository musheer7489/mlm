<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Validate input
    $required = ['fullName', 'username', 'email', 'phone', 'address', 'password', 'sponsorId'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Please fill in all required fields");
        }
    }
    
    if ($_POST['password'] !== $_POST['confirmPassword']) {
        throw new Exception("Passwords do not match");
    }
    
    // Check if username exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("Username already exists");
    }
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("Email already registered");
    }
    
    // Validate sponsor
    $sponsor = getUserByUsername($_POST['sponsorId']);
    if (!$sponsor) {
        throw new Exception("Invalid sponsor ID");
    }
    
    // Determine placement (binary tree positioning)
    $placement = null;
    $position = null;
    if (!empty($_POST['placementId'])) {
        $placement = getUserByUsername($_POST['placementId']);
        if (!$placement) {
            throw new Exception("Invalid placement ID");
        }
        
        // Check if placement position is available
        $stmt = $pdo->prepare("SELECT position FROM users WHERE parent_id = ?");
        $stmt->execute([$placement['id']]);
        $occupiedPositions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($occupiedPositions) >= 2) {
            throw new Exception("Selected placement already has both positions filled");
        }
        
        $position = (in_array('left', $occupiedPositions)) ? 'right' : 'left';
    }
    
    // Hash password
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Create user
    $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, phone, address, password, sponsor_id, parent_id, position) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['fullName'],
        $_POST['username'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address'],
        $hashedPassword,
        $sponsor['id'],
        $placement ? $placement['id'] : null,
        $position
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // Create welcome badge
    $welcomeBadge = $pdo->query("SELECT id FROM badges WHERE level_required = 0 LIMIT 1")->fetch();
    if ($welcomeBadge) {
        $stmt = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
        $stmt->execute([$userId, $welcomeBadge['id']]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    $response['success'] = true;
    $response['message'] = "Registration successful!";
    $response['userId'] = $userId;
    
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>