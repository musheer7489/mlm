<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isDistributor()) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$notifications = getUserNotifications($userId);

// Mark notifications as read when page loads
markNotificationsAsRead($userId);

// Handle team message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $teamLevel = $_POST['team_level'];
    $message = $_POST['message'];
    
    $sentCount = sendTeamNotification($userId, $teamLevel, $message);
    $_SESSION['success'] = "Message sent to $sentCount team members";
    header('Location: team-communication.php');
    exit;
}
?>

<?php include '../includes/header.php'; ?>

<div class="dashboard-container">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Team Communication</h4>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-bell me-1"></i> <?= count($notifications) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Send Team Message</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label for="team_level" class="form-label">Team Level</label>
                                            <select class="form-select" id="team_level" name="team_level" required>
                                                <option value="1">Level 1 (Direct Team)</option>
                                                <option value="2">Level 1-2</option>
                                                <option value="3">Level 1-3</option>
                                                <option value="4">Level 1-4</option>
                                                <option value="5">Level 1-5 (Entire Team)</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="message" class="form-label">Message</label>
                                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                        </div>
                                        
                                        <div class="text-end">
                                            <button type="submit" name="send_message" class="btn btn-primary">
                                                <i class="fas fa-paper-plane me-2"></i>Send Message
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Recent Notifications</h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (empty($notifications)): ?>
                                        <div class="p-3 text-center text-muted">
                                            No notifications to display
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach($notifications as $notification): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between">
                                                    <div><?= htmlspecialchars($notification['message']) ?></div>
                                                    <small class="text-muted">
                                                        <?= time_elapsed_string($notification['created_at']) ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// In a real implementation, you might add real-time notification updates
$(document).ready(function() {
    // Example: Check for new notifications every 30 seconds
    setInterval(function() {
        $.get('../api/check-notifications.php', function(response) {
            if (response.count > 0) {
                // Update notification badge
                $('.badge .fa-bell').addClass('text-danger');
                $('.badge').text(response.count);
                
                // Play notification sound
                const audio = new Audio('../assets/sounds/notification.mp3');
                audio.play();
                
                // Show toast notification
                if (response.latest) {
                    showToast('New Notification', response.latest.message);
                }
            }
        });
    }, 30000);
});

function showToast(title, message) {
    // Implement a toast notification
    const toast = `
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">${title}</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        </div>
    `;
    $('body').append(toast);
    
    // Remove toast after 5 seconds
    setTimeout(function() {
        $('.toast').remove();
    }, 5000);
}
</script>

<?php include '../includes/footer.php'; ?>