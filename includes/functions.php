<?php
// ... other functions ...

/**
 * Calculate commissions for a purchase
 */
function calculateCommissions($orderId, $userId, $amount)
{
    global $pdo;

    // Get commission levels
    $levels = $pdo->query("SELECT * FROM commission_levels ORDER BY level")->fetchAll();

    // Get upline users
    $upline = getUplineUsers($userId, count($levels));

    // Calculate and record commissions
    foreach ($upline as $level => $user) {
        if ($level >= count($levels)) break;

        $commissionLevel = $levels[$level];
        $commissionAmount = $amount * ($commissionLevel['percentage'] / 100);

        $stmt = $pdo->prepare("INSERT INTO commissions 
                              (user_id, order_id, level, amount, percentage, from_member, order_amount) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user['id'],
            $orderId,
            $commissionLevel['level'],
            $commissionAmount,
            $commissionLevel['percentage'],
            $userId,
            $amount
        ]);

        // Check if user reached a new level and award badge
        checkLevelAchievement($user['id'], $commissionLevel['level']);
    }
}

/**
 * Get upline users for commission calculation
 */
function getUplineUsers($userId, $levelsToFetch)
{
    global $pdo;
    $upline = [];
    $currentId = $userId;

    for ($i = 0; $i < $levelsToFetch; $i++) {
        $stmt = $pdo->prepare("SELECT u.id, u.username, u.full_name, u.sponsor_id 
                              FROM users u 
                              WHERE u.id = (
                                  SELECT sponsor_id FROM users WHERE id = ?
                              )");
        $stmt->execute([$currentId]);
        $user = $stmt->fetch();

        if (!$user) break;

        $upline[$i] = $user;
        $currentId = $user['id'];
    }

    return $upline;
}

/**
 * Check if user reached a new level and award badge
 */
function checkLevelAchievement($userId, $level)
{
    global $pdo;

    // Check if user already has this level badge
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_badges ub
                          JOIN badges b ON ub.badge_id = b.id
                          WHERE ub.user_id = ? AND b.level_required = ?");
    $stmt->execute([$userId, $level]);
    $hasBadge = $stmt->fetchColumn() > 0;

    if (!$hasBadge) {
        // Get badge for this level
        $stmt = $pdo->prepare("SELECT id FROM badges WHERE level_required = ? LIMIT 1");
        $stmt->execute([$level]);
        $badge = $stmt->fetch();

        if ($badge) {
            // Award badge
            $stmt = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
            $stmt->execute([$userId, $badge['id']]);

            // Notify user
            $message = "Congratulations! You've earned a new badge for reaching Level $level";
            $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")
                ->execute([$userId, $message]);
        }
    }
}

// ... other functions ...

/**
 * Verify Razorpay payment and update order status
 */
function verifyPayment($paymentId, $orderId)
{
    global $pdo;

    try {
        // Initialize Razorpay client
        $api = new Razorpay\Api\Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

        // Fetch payment details
        $payment = $api->payment->fetch($paymentId);

        // Verify payment is attached to our order
        if ($payment->order_id !== $orderId) {
            throw new Exception("Payment not attached to this order");
        }

        // Verify payment is captured
        if ($payment->status !== 'captured') {
            throw new Exception("Payment not captured");
        }

        // Start transaction
        $pdo->beginTransaction();

        // Update order status
        $stmt = $pdo->prepare("UPDATE orders SET 
                              payment_status = 'completed', 
                              payment_id = ?,
                              updated_at = NOW()
                              WHERE payment_id = ?");
        $stmt->execute([$paymentId, $orderId]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Order not found");
        }

        // Get order details
        $order = $pdo->query("SELECT * FROM orders WHERE payment_id = '$paymentId'")->fetch();

        // Calculate commissions
        calculateCommissions($order['id'], $order['user_id'], $order['total_amount']);

        // Commit transaction
        $pdo->commit();

        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Payment verification failed: " . $e->getMessage());
        return false;
    }
}
/**
 * Authentication functions
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isDistributor()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'distributor';
}

function authenticateUser($username, $password)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }

    return false;
}

/**
 * User functions
 */
function getUserById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getUserByUsername($username)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function getSponsorName($sponsorId)
{
    if (!$sponsorId) return 'N/A';

    $sponsor = getUserById($sponsorId);
    return $sponsor ? $sponsor['full_name'] : 'N/A';
}

function updateProfile($userId, $fullName, $email, $phone, $address, $password = null, $image = null, $bankName = null, $accountNumber = null, $ifscCode = null, $panNumber = null)
{
    global $pdo;

    if ($password) {
        $stmt = $pdo->prepare("
            UPDATE users SET 
            full_name = ?, email = ?, phone = ?, address = ?, password = ?, image = ?,
            bank_name = ?, account_number = ?, ifsc_code = ?, pan_number = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $fullName, $email, $phone, $address, $password, $image,
            $bankName, $accountNumber, $ifscCode, $panNumber, $userId
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE users SET 
            full_name = ?, email = ?, phone = ?, address = ?, image = ?,
            bank_name = ?, account_number = ?, ifsc_code = ?, pan_number = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $fullName, $email, $phone, $address, $image,
            $bankName, $accountNumber, $ifscCode, $panNumber, $userId
        ]);
    }
}

/**
 * Product functions
 */
function getSingleProduct()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM products LIMIT 1");
    return $stmt->fetch();
}

function updateProduct($id, $name, $description, $price, $stock, $image)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?");
    $stmt->execute([$name, $description, $price, $stock, $image, $id]);
}

function getProductTestimonials()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM testimonials WHERE approved = 1 ORDER BY created_at DESC LIMIT 5");
    return $stmt->fetchAll();
}

/**
 * Commission functions
 */
function getCommissionLevels()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM commission_levels ORDER BY level");
    return $stmt->fetchAll();
}

function updateCommissionLevel($id, $percentage, $description)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE commission_levels SET percentage = ?, description = ? WHERE id = ?");
    $stmt->execute([$percentage, $description, $id]);
}

function getTotalPendingCommissions()
{
    global $pdo;
    $stmt = $pdo->query("SELECT SUM(amount) FROM commissions WHERE status = 'pending'");
    return $stmt->fetchColumn() ?? 0;
}

function getEligibleDistributorsCount()
{
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM commissions WHERE status = 'pending'");
    return $stmt->fetchColumn() ?? 0;
}

/**
 * Admin functions
 */
function getTotalDistributors()
{
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0");
    return $stmt->fetchColumn();
}
function getAllDistributors()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM users WHERE is_admin = 0"); // Select usernames
    return $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all rows as an associative array
}

// Delete User

function deleteUser($user_id)
{
    global $pdo; // Use the global PDO connection

    // Sanitize the input to prevent SQL injection.
    $user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);

    // Check if the user ID is valid.
    if (!$user_id || $user_id <= 0) {
        error_log("Invalid user ID provided to deleteUser: " . print_r($user_id, true));
        return false; // Return false for invalid input
    }

    try {
        // Prepare the SQL statement.
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        // Bind the parameter.
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

        // Execute the statement.
        $stmt->execute();

        // Check the number of affected rows.
        $rowsAffected = $stmt->rowCount();

        if ($rowsAffected > 0) {
            return true; // User deleted successfully.
        } else {
            return false; // User not found or no rows affected.
        }
    } catch (PDOException $e) {
        // Handle any exceptions (errors).  Log the error message.
        error_log("PDO Exception: " . $e->getMessage());
        return false; // Indicate failure.
    }
}
function getTotalSales()
{
    global $pdo;
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'completed'");
    return $stmt->fetchColumn() ?? 0;
}

function getRecentSignups($limit = 5)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE is_admin = 0 ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getRecentOrders($limit = 5)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Distributor functions
 */
function getTeamMembers($userId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        WITH RECURSIVE team_tree AS (
            SELECT id, username, full_name, sponsor_id, parent_id, created_at, image, active, 1 as level 
            FROM users WHERE sponsor_id = ?
            UNION ALL
            SELECT u.id, u.username, u.full_name, u.sponsor_id, u.parent_id, u.created_at, u.image, u.active, tt.level + 1
            FROM users u
            JOIN team_tree tt ON u.sponsor_id = tt.id
        )
        SELECT * FROM team_tree ORDER BY level, created_at
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getTeamTree($userId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        WITH RECURSIVE team_tree AS (
            SELECT id, username, full_name, sponsor_id, parent_id, position, 1 as level 
            FROM users WHERE id = ?
            UNION ALL
            SELECT u.id, u.username, u.full_name, u.sponsor_id, u.parent_id, u.position, tt.level + 1
            FROM users u
            JOIN team_tree tt ON u.parent_id = tt.id
            WHERE u.parent_id IS NOT NULL
        )
        SELECT * FROM team_tree
        LIMIT 100
    ");
    $stmt->execute([$userId]);
    $members = $stmt->fetchAll();

    // Format for org chart
    $tree = [];
    foreach ($members as $member) {
        $node = [
            'id' => $member['id'],
            'pid' => $member['parent_id'] ?: null,
            'name' => $member['full_name'],
            'title' => 'Level ' . $member['level'],
            'img' => 'assets/images/profile/' . ($member['image'] ?? 'default.jpg')
        ];
        $tree[] = $node;
    }

    return $tree;
}

function getCommissions($userId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*, u.full_name as from_member, o.total_amount as order_amount, cl.percentage
        FROM commissions c
        JOIN users u ON c.from_member = u.id
        JOIN orders o ON c.order_id = o.id
        JOIN commission_levels cl ON c.level = cl.level
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getCommissionStats($userId)
{
    global $pdo;

    $stats = [
        'total' => 0,
        'paid' => 0,
        'pending' => 0
    ];

    $stmt = $pdo->prepare("SELECT SUM(amount) FROM commissions WHERE user_id = ?");
    $stmt->execute([$userId]);
    $stats['total'] = $stmt->fetchColumn() ?? 0;

    $stmt = $pdo->prepare("SELECT SUM(amount) FROM commissions WHERE user_id = ? AND status = 'paid'");
    $stmt->execute([$userId]);
    $stats['paid'] = $stmt->fetchColumn() ?? 0;

    $stmt = $pdo->prepare("SELECT SUM(amount) FROM commissions WHERE user_id = ? AND status = 'pending'");
    $stmt->execute([$userId]);
    $stats['pending'] = $stmt->fetchColumn() ?? 0;

    return $stats;
}

function getRecentSales($userId)
{
    global $pdo;

    $sales = [
        'personal' => [
            'count' => 0,
            'amount' => 0
        ],
        'recent' => []
    ];

    // Get personal sales
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count, SUM(total_amount) as amount 
        FROM orders 
        WHERE user_id = ? AND payment_status = 'completed'
    ");
    $stmt->execute([$userId]);
    $personalSales = $stmt->fetch();
    $sales['personal'] = $personalSales ?: $sales['personal'];

    // Get recent sales (including team sales that generate commissions)
    $stmt = $pdo->prepare("
        SELECT o.id, o.payment_id, o.total_amount as amount, o.created_at, 
               c.amount as commission, u.full_name as customer_name
        FROM orders o
        LEFT JOIN commissions c ON o.id = c.order_id AND c.user_id = ?
        LEFT JOIN users u ON o.user_id = u.id
        WHERE (o.user_id = ? OR c.id IS NOT NULL) AND o.payment_status = 'completed'
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$userId, $userId]);
    $sales['recent'] = $stmt->fetchAll();

    return $sales;
}

function getTeamStats($userId)
{
    global $pdo;

    $stats = [
        'total_members' => 0,
        'team_sales' => 0,
        'levels' => []
    ];

    // Get total team members
    $stmt = $pdo->prepare("
        WITH RECURSIVE team_tree AS (
            SELECT id FROM users WHERE sponsor_id = ?
            UNION ALL
            SELECT u.id FROM users u
            JOIN team_tree tt ON u.sponsor_id = tt.id
        )
        SELECT COUNT(*) FROM team_tree
    ");
    $stmt->execute([$userId]);
    $stats['total_members'] = $stmt->fetchColumn() ?? 0;

    // Get team sales
    $stmt = $pdo->prepare("
        WITH RECURSIVE team_tree AS (
            SELECT id FROM users WHERE sponsor_id = ?
            UNION ALL
            SELECT u.id FROM users u
            JOIN team_tree tt ON u.sponsor_id = tt.id
        )
        SELECT SUM(o.total_amount) 
        FROM orders o
        JOIN team_tree tt ON o.user_id = tt.id
        WHERE o.payment_status = 'completed'
    ");
    $stmt->execute([$userId]);
    $stats['team_sales'] = $stmt->fetchColumn() ?? 0;

    return $stats;
}

function getUserBadges($userId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT b.* FROM badges b
        JOIN user_badges ub ON b.id = ub.badge_id
        WHERE ub.user_id = ?
        ORDER BY b.level_required
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getReferralLink($username)
{
    return SITE_URL . '/register.php?sponsor=' . urlencode($username);
}

function getLevelBadgeColor($level)
{
    $colors = [
        1 => 'primary',
        2 => 'info',
        3 => 'success',
        4 => 'warning',
        5 => 'danger'
    ];

    return $colors[$level] ?? 'secondary';
}

/**
 * Settings functions
 */
function getSettings()
{
    global $pdo;
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $settingsByKey = [];
    foreach ($results as $row) {
        $settingsByKey[$row['setting_key']] = $row['setting_value'];
    }
    return $settingsByKey;
}

function updateSettings($data)
{
    global $pdo;

    // In a real implementation, you would update each setting individually
    // or have a more sophisticated settings system
    foreach ($data as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                              ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
}

function getMySQLVersion()
{
    global $pdo;
    return $pdo->query("SELECT VERSION()")->fetchColumn();
}

function getPendingPayoutsCount()
{
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM payouts WHERE status = 'pending'");
    return $stmt->fetchColumn() ?? 0;
}

function getRecentPayouts()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM payouts ORDER BY created_at DESC LIMIT 5");
    return $stmt->fetchAll();
}
// Add these functions to your existing functions.php file

/**
 * Password reset functions
 */
function getUserByEmail($email)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function storePasswordResetToken($userId, $token, $expires)
{
    global $pdo;

    // Delete any existing tokens for this user
    $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$userId]);

    // Insert new token
    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $token, $expires]);
}

function validatePasswordResetToken($token)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    return $stmt->fetch();
}

function deletePasswordResetToken($token)
{
    global $pdo;
    $pdo->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);
}

function updateUserPassword($userId, $password)
{
    global $pdo;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $userId]);
}

/**
 * New MLM reporting functions
 */
function getTopPerformers($limit = 5)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, u.username, u.image, 
               SUM(c.amount) as total_commissions,
               COUNT(DISTINCT c.order_id) as sales_count
        FROM users u
        JOIN commissions c ON u.id = c.user_id
        WHERE c.status = 'paid'
        GROUP BY u.id
        ORDER BY total_commissions DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getMonthlySalesReport()
{
    global $pdo;
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as order_count,
            SUM(total_amount) as total_sales,
            SUM(total_amount * 0.05) as tax_collected
        FROM orders
        WHERE payment_status = 'completed'
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 12
    ");
    return $stmt->fetchAll();
}

function getCommissionPayoutsReport()
{
    global $pdo;
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(paid_at, '%Y-%m') as month,
            COUNT(*) as payout_count,
            SUM(amount) as total_payouts
        FROM commissions
        WHERE status = 'paid'
        GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 12
    ");
    return $stmt->fetchAll();
}

/**
 * Team communication functions
 */
function sendTeamNotification($senderId, $teamLevel, $message)
{
    global $pdo;

    // Get team members at specified level
    $stmt = $pdo->prepare("
        WITH RECURSIVE team_tree AS (
            SELECT id, 1 as level FROM users WHERE sponsor_id = ?
            UNION ALL
            SELECT u.id, tt.level + 1
            FROM users u
            JOIN team_tree tt ON u.sponsor_id = tt.id
            WHERE tt.level < ?
        )
        SELECT id FROM team_tree WHERE level <= ?
    ");
    $stmt->execute([$senderId, $teamLevel, $teamLevel]);
    $teamMembers = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Insert notifications
    $insertStmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    foreach ($teamMembers as $memberId) {
        $insertStmt->execute([$memberId, $message]);
    }

    return count($teamMembers);
}

function getUserNotifications($userId, $limit = 10)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

function markNotificationsAsRead($userId)
{
    global $pdo;
    $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE")
        ->execute([$userId]);
}

/**
 * Helper function to display time elapsed
 */
function time_elapsed_string($datetime, $full = false)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );

    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
/**
 * Training functions
 */
function getUserTrainingProgress($userId)
{
    global $pdo;

    // In a real implementation, you would track which videos users have completed
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as completed_modules 
        FROM user_training 
        WHERE user_id = ? AND completed = 1
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();

    $totalModules = 4; // This would come from database in real implementation

    return [
        'completed' => $result['completed_modules'] ?? 0,
        'total' => $totalModules,
        'percentage' => $totalModules > 0 ? round(($result['completed_modules'] / $totalModules) * 100) : 0
    ];
}
/**
 * Get team activity for dashboard
 */
function getTeamActivity($userId, $limit = 5)
{
    global $pdo;

    $stmt = $pdo->prepare("
        WITH RECURSIVE team_tree AS (
            SELECT id, 1 as level FROM users WHERE sponsor_id = ?
            UNION ALL
            SELECT u.id, tt.level + 1
            FROM users u
            JOIN team_tree tt ON u.sponsor_id = tt.id
            WHERE tt.level < 5
        )
        SELECT 
            u.id, u.full_name, u.image, tt.level,
            CASE 
                WHEN o.id IS NOT NULL THEN CONCAT('Made a sale (₹', o.total_amount, ')')
                WHEN ub.id IS NOT NULL THEN CONCAT('Earned badge: ', b.name)
                ELSE 'Joined the team'
            END as activity,
            COALESCE(o.created_at, ub.earned_at, u.created_at) as created_at
        FROM team_tree tt
        JOIN users u ON tt.id = u.id
        LEFT JOIN orders o ON o.user_id = u.id AND o.payment_status = 'completed'
        LEFT JOIN user_badges ub ON ub.user_id = u.id
        LEFT JOIN badges b ON ub.badge_id = b.id
        WHERE u.id != ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$userId, $userId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get training progress for dashboard
 */
function getTrainingProgress($userId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT ut.video_id) as completed,
            (SELECT COUNT(*) FROM training_videos) as total,
            ROUND(COUNT(DISTINCT ut.video_id) / (SELECT COUNT(*) FROM training_videos) * 100) as percentage
        FROM user_training ut
        WHERE ut.user_id = ? AND ut.completed = 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: ['completed' => 0, 'total' => 0, 'percentage' => 0];
}
/**
 * Get featured testimonials
 */
function getFeaturedTestimonials($limit = 3)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT t.*, 
               (SELECT COUNT(*) FROM user_badges WHERE user_id = t.user_id) > 0 as is_distributor
        FROM testimonials t
        WHERE t.approved = 1
        ORDER BY t.rating DESC, t.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get distributor benefits for homepage
 */
function getDistributorBenefits()
{
    return [
        [
            'icon' => 'fa-money-bill-wave',
            'title' => 'Earn Commissions',
            'description' => 'Get paid for every sale you make through multiple levels'
        ],
        [
            'icon' => 'fa-users',
            'title' => 'Build a Team',
            'description' => 'Grow your business by building your own sales team'
        ],
        [
            'icon' => 'fa-gift',
            'title' => 'Rewards & Bonuses',
            'description' => 'Earn special bonuses and rewards for top performance'
        ],
        [
            'icon' => 'fa-chart-line',
            'title' => 'Residual Income',
            'description' => 'Create ongoing income from your team\'s sales'
        ]
    ];
}
/**
 * Check if username exists
 */
function usernameExists($username)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch() !== false;
}

/**
 * Check if email exists
 */
function emailExists($email)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() !== false;
}

/**
 * Create a new user
 */
function createUser($data)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert user
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, username, email, phone, address, password, sponsor_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['full_name'],
            $data['username'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $hashedPassword,
            $data['sponsor_id']
        ]);

        $userId = $pdo->lastInsertId();

        // Assign welcome badge
        $welcomeBadge = $pdo->query("SELECT id FROM badges WHERE level_required = 0 LIMIT 1")->fetch();
        if ($welcomeBadge) {
            $stmt = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
            $stmt->execute([$userId, $welcomeBadge['id']]);
        }

        $pdo->commit();
        return $userId;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
/**
 * Get sponsor name with fallback
 */
/**
 * Order processing functions
 */
function processOrderPayment($orderId, $paymentId, $amount)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Update order status
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET payment_status = 'completed', 
                payment_id = ?,
                total_amount = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$paymentId, $amount, $orderId]);

        // Get order details
        $stmt = $pdo->prepare("
            SELECT user_id, product_id, total_amount 
            FROM orders 
            WHERE id = ?
        ");
        $executionResult = $stmt->execute([$orderId]);
        if (!$executionResult) {
            print_r($stmt->errorInfo()); // This will give you specific error information from the database
            die('Error executing the query.');
        }
        $order = $stmt->fetchAll();

        if (!$order) {
            throw new Exception("Order not found");
        }

        // Calculate and distribute commissions
        calculateCommissions($orderId, $order['user_id'], $order['total_amount']);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Order processing failed: " . $e->getMessage());
        return false;
    }
}
/**
 * Payout functions
 */
function processPayouts($period, $fromDate = null, $toDate = null, $minAmount = 500)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Create payout record
        $stmt = $pdo->prepare("
            INSERT INTO payouts (period, from_date, to_date, min_amount, distributor_count, total_amount)
            VALUES (?, ?, ?, ?, 0, 0)
        ");
        $stmt->execute([$period, $fromDate, $toDate, $minAmount]);
        $payoutId = $pdo->lastInsertId();

        // Get eligible distributors and their pending commissions
        $query = "
            SELECT 
                u.id as user_id,
                u.username,
                u.full_name,
                u.bank_name,
                u.account_number,
                u.ifsc_code,
                COUNT(c.id) as commission_count,
                SUM(c.amount) as total_amount
            FROM users u
            JOIN commissions c ON u.id = c.user_id
            WHERE c.status = 'pending'
        ";

        // Add date range if custom period
        if ($period === 'custom' && $fromDate && $toDate) {
            $query .= " AND c.created_at BETWEEN ? AND ?";
            $params = [$fromDate, $toDate];
        } elseif ($period === 'weekly') {
            $query .= " AND c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $params = [];
        } else { // monthly
            $query .= " AND c.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            $params = [];
        }

        $query .= "
            GROUP BY u.id
            HAVING total_amount >= ?
            ORDER BY total_amount DESC
        ";
        $params[] = $minAmount;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $eligibleDistributors = $stmt->fetchAll();

        // Process each eligible distributor
        $distributorCount = 0;
        $totalAmount = 0;

        foreach ($eligibleDistributors as $distributor) {
            // Create payout item
            $stmt = $pdo->prepare("
                INSERT INTO payout_items 
                (payout_id, user_id, amount, commission_count, bank_name, account_number, ifsc_code)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $payoutId,
                $distributor['user_id'],
                $distributor['total_amount'],
                $distributor['commission_count'],
                $distributor['bank_name'],
                $distributor['account_number'],
                $distributor['ifsc_code']
            ]);
            $payoutItemId = $pdo->lastInsertId();

            // Mark commissions as paid
            $updateQuery = "
                UPDATE commissions 
                SET status = 'paid', paid_at = NOW(), payout_item_id = ?
                WHERE user_id = ? AND status = 'pending'
            ";

            if ($period === 'custom' && $fromDate && $toDate) {
                $updateQuery .= " AND created_at BETWEEN ? AND ?";
                $updateParams = [$payoutItemId, $distributor['user_id'], $fromDate, $toDate];
            } elseif ($period === 'weekly') {
                $updateQuery .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                $updateParams = [$payoutItemId, $distributor['user_id']];
            } else { // monthly
                $updateQuery .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                $updateParams = [$payoutItemId, $distributor['user_id']];
            }

            $stmt = $pdo->prepare($updateQuery);
            $stmt->execute($updateParams);

            $distributorCount++;
            $totalAmount += $distributor['total_amount'];
        }

        // Update payout totals
        $stmt = $pdo->prepare("
            UPDATE payouts 
            SET distributor_count = ?, total_amount = ?
            WHERE id = ?
        ");
        $stmt->execute([$distributorCount, $totalAmount, $payoutId]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Payout processing failed: " . $e->getMessage());
        return false;
    }
}

function markPayoutAsPaid($payoutId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        UPDATE payouts 
        SET status = 'completed', completed_at = NOW() 
        WHERE id = ?
    ");
    return $stmt->execute([$payoutId]);
}

function getPendingPayouts()
{
    global $pdo;
    $stmt = $pdo->query("
        SELECT * FROM payouts 
        WHERE status = 'pending'
        ORDER BY created_at DESC
    ");
    return $stmt->fetchAll();
}

function getCompletedPayouts()
{
    global $pdo;
    $stmt = $pdo->query("
        SELECT * FROM payouts 
        WHERE status = 'completed'
        ORDER BY completed_at DESC
        LIMIT 10
    ");
    return $stmt->fetchAll();
}

function getPayoutById($payoutId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM payouts 
        WHERE id = ?
    ");
    $stmt->execute([$payoutId]);
    return $stmt->fetch();
}

function getPayoutItems($payoutId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            pi.*,
            u.username,
            u.full_name,
            u.image
        FROM payout_items pi
        JOIN users u ON pi.user_id = u.id
        WHERE pi.payout_id = ?
        ORDER BY pi.amount DESC
    ");
    $stmt->execute([$payoutId]);
    return $stmt->fetchAll();
}

function getEligibleDistributors()
{
    global $pdo;
    $stmt = $pdo->query("
        SELECT 
            u.id,
            u.username,
            u.full_name,
            COUNT(c.id) as pending_commissions,
            SUM(c.amount) as pending_amount
        FROM users u
        JOIN commissions c ON u.id = c.user_id
        WHERE c.status = 'pending'
        GROUP BY u.id
        HAVING pending_amount >= 500
        ORDER BY pending_amount DESC
    ");
    return $stmt->fetchAll();
}

function getDistributorPayouts($userId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            pi.*,
            p.period,
            p.from_date,
            p.to_date,
            p.completed_at as paid_at
        FROM payout_items pi
        JOIN payouts p ON pi.payout_id = p.id
        WHERE pi.user_id = ?
        ORDER BY p.completed_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getDistributorPayoutDetails($userId, $payoutItemId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            pi.*,
            p.id as payout_id,
            p.period,
            p.from_date,
            p.to_date,
            p.completed_at as paid_at,
            p.status
        FROM payout_items pi
        JOIN payouts p ON pi.payout_id = p.id
        WHERE pi.id = ? AND pi.user_id = ?
    ");
    $stmt->execute([$payoutItemId, $userId]);
    return $stmt->fetch();
}

function getCommissionsForPayoutItem($payoutItemId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            c.*,
            cl.percentage,
            u.full_name as from_member
        FROM commissions c
        JOIN commission_levels cl ON c.level = cl.level
        JOIN users u ON c.from_member = u.id
        WHERE c.payout_item_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$payoutItemId]);
    return $stmt->fetchAll();
}

function getPendingCommissionsTotal($userId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT SUM(amount) 
        FROM commissions 
        WHERE user_id = ? AND status = 'pending'
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn() ?? 0;
}

function getTotalCommissionsEarned($userId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT SUM(amount) 
        FROM commissions 
        WHERE user_id = ? AND status = 'paid'
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn() ?? 0;
}
/**
 * Order Management Functions
 */
function getOrdersWithFilters($statusFilter = 'all', $dateFrom = '', $dateTo = '', $searchQuery = '')
{
    global $pdo;

    $sql = "
        SELECT o.*, u.username, u.full_name, u.image, 
               COUNT(oi.id) as item_count,
               s.full_name as sponsor_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN users s ON u.sponsor_id = s.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
    ";

    $where = [];
    $params = [];

    if ($statusFilter !== 'all') {
        if (in_array($statusFilter, ['pending', 'completed', 'failed', 'refunded'])) {
            $where[] = "o.payment_status = ?";
            $params[] = $statusFilter;
        } elseif (in_array($statusFilter, ['processing', 'shipped', 'delivered', 'returned'])) {
            $where[] = "o.shipping_status = ?";
            $params[] = $statusFilter;
        }
    }

    if ($dateFrom && $dateTo) {
        $where[] = "DATE(o.created_at) BETWEEN ? AND ?";
        $params[] = $dateFrom;
        $params[] = $dateTo;
    } elseif ($dateFrom) {
        $where[] = "DATE(o.created_at) >= ?";
        $params[] = $dateFrom;
    } elseif ($dateTo) {
        $where[] = "DATE(o.created_at) <= ?";
        $params[] = $dateTo;
    }

    if ($searchQuery) {
        $where[] = "(o.id LIKE ? OR u.full_name LIKE ? OR u.username LIKE ? OR o.payment_id LIKE ?)";
        $searchParam = "%$searchQuery%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getOrderDetails($orderId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT o.*, u.username, u.full_name, u.email, u.phone, u.image,
               s.full_name as sponsor_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN users s ON u.sponsor_id = s.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    return $stmt->fetch();
}

function getOrderItems($orderId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT oi.*, p.name, p.image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}
function getOrderItemsById($orderId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT o.*, p.name, p.image, p.price
        FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE o.id = ?;
    ");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function getOrderStatusHistory($orderId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT h.*, u.full_name as changed_by_name
        FROM order_status_history h
        LEFT JOIN users u ON h.changed_by = u.id
        WHERE h.order_id = ?
        ORDER BY h.created_at DESC
    ");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function updateOrderStatus($orderId, $statusType, $newStatus, $notes, $changedBy)
{
    global $pdo;

    // Get current status
    $order = getOrderDetails($orderId);
    $currentStatus = $statusType === 'payment' ? $order['payment_status'] : $order['shipping_status'];

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Update order status
        if ($statusType === 'payment') {
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE orders SET shipping_status = ? WHERE id = ?");
        }
        $stmt->execute([$newStatus, $orderId]);

        // Record status change
        $stmt = $pdo->prepare("
            INSERT INTO order_status_history 
            (order_id, status_type, old_status, new_status, notes, changed_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $statusType, $currentStatus, $newStatus, $notes, $changedBy]);

        // Commit transaction
        $pdo->commit();

        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating order status: " . $e->getMessage());
        return false;
    }
}

function updateOrderTracking($orderId, $trackingNumber, $changedBy)
{
    global $pdo;

    // Get current tracking number
    $order = getOrderDetails($orderId);
    $currentTracking = $order['tracking_number'] ?? '';

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Update tracking number
        $stmt = $pdo->prepare("UPDATE orders SET tracking_number = ?, shipping_status = 'shipped' WHERE id = ?");
        $stmt->execute([$trackingNumber, $orderId]);

        // Record status change if shipping status was updated
        if ($order['shipping_status'] !== 'shipped') {
            $stmt = $pdo->prepare("
                INSERT INTO order_status_history 
                (order_id, status_type, old_status, new_status, notes, changed_by)
                VALUES (?, 'shipping', ?, ?, ?, ?)
            ");
            $notes = "Tracking number: $trackingNumber";
            $stmt->execute([$orderId, $order['shipping_status'], 'shipped', $notes, $changedBy]);
        }

        // Commit transaction
        $pdo->commit();

        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating tracking number: " . $e->getMessage());
        return false;
    }
}

function getUserOrders($userId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getPaymentStatusColor($status)
{
    switch ($status) {
        case 'completed':
            return 'success';
        case 'pending':
            return 'warning';
        case 'failed':
            return 'danger';
        case 'refunded':
            return 'info';
        default:
            return 'secondary';
    }
}

function getShippingStatusColor($status)
{
    switch ($status) {
        case 'delivered':
            return 'success';
        case 'shipped':
            return 'primary';
        case 'processing':
            return 'info';
        case 'pending':
            return 'warning';
        case 'returned':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getUserName($userId)
{
    if (!$userId) return 'System';

    global $pdo;
    $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    return $user ? $user['full_name'] : 'Unknown User';
}

function getShippingMethods()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM shipping_methods WHERE is_active = TRUE ORDER BY cost");
    return $stmt->fetchAll();
}
/**
 * Order-related functions
 */
function createOrder($data)
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, product_id, quantity, total_amount, 
            shipping_address, billing_address, payment_method,
            notes, shipping_method_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['user_id'],
        $data['product_id'],
        $data['quantity'] ?? 1,
        $data['total_amount'],
        $data['shipping_address'],
        $data['billing_address'] ?? $data['shipping_address'],
        $data['payment_method'],
        $data['notes'] ?? null,
        $data['shipping_method_id']
    ]);

    return $pdo->lastInsertId();
}

function getOrderById($orderId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT o.*, sm.name as shipping_method_name, sm.cost as shipping_cost,
               sm.estimated_days as shipping_estimate
        FROM orders o
        LEFT JOIN shipping_methods sm ON o.shipping_method_id = sm.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    return $stmt->fetch();
}

function updateOrderPayment($orderId, $paymentId, $status)
{
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET payment_id = ?, payment_status = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$paymentId, $status, $orderId]);
}

function updateOrderPaymentId($orderId, $paymentId)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE orders SET payment_id = ? WHERE id = ?");
    $stmt->execute([$paymentId, $orderId]);
}



function addOrderStatusHistory($orderId, $type, $oldStatus, $newStatus, $notes = null)
{
    global $pdo;
    $changedBy = $_SESSION['user_id'] ?? null;

    $stmt = $pdo->prepare("
        INSERT INTO order_status_history (
            order_id, status_type, old_status, new_status, notes, changed_by
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$orderId, $type, $oldStatus, $newStatus, $notes, $changedBy]);
}


function getShippingMethodById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM shipping_methods WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function verifyRazorpayPayment($paymentId, $amount)
{
    try {
        // Initialize Razorpay client
        $api = new Razorpay\Api\Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

        // Fetch payment details
        $payment = $api->payment->fetch($paymentId);

        // Verify amount
        if ($payment->amount != $amount * 100) { // Razorpay uses paise
            return false;
        }

        // Verify payment is captured
        if ($payment->status !== 'captured') {
            return false;
        }

        return true;
    } catch (Exception $e) {
        error_log("Razorpay verification error: " . $e->getMessage());
        return false;
    }
}

// ... other functions ...
