<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle actions
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        deleteUser($_GET['id']);
        $_SESSION['success'] = 'Distributor deleted successfully';
        header('Location: users.php');
        exit;
    }
}

// Get all distributors
$distributors = getAllDistributors();
?>

<?php include '../includes/header.php'; ?>

<div class="admin-container">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Distributors Management</h4>
                        <a href="add-distributor.php" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i> Add New
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <!-- Distributors Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Join Date</th>
                                    <th>Sponsor</th>
                                    <th>Level</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($distributors as $distributor): ?>
                                <tr>
                                    <td><?= $distributor['username'] ?></td>
                                    <td>
                                        <img src="../assets/images/profile/<?= $distributor['image'] ?? 'default.jpg' ?>" width="30" class="rounded-circle me-2">
                                        <?= $distributor['full_name'] ?>
                                    </td>
                                    <td><?= date('d M Y', strtotime($distributor['created_at'])) ?></td>
                                    <td><?= getSponsorName($distributor['sponsor_id']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= getLevelBadgeColor($distributor['level']) ?>">
                                            Level <?= $distributor['level'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $distributor['active'] ? 'success' : 'danger' ?>">
                                            <?= $distributor['active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit-distributor.php?id=<?= $distributor['id'] ?>" class="btn btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=delete&id=<?= $distributor['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="view-distributor.php?id=<?= $distributor['id'] ?>" class="btn btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-3">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>