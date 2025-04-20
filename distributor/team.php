<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isDistributor()) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$teamMembers = getTeamMembers($userId);
$teamTree = getTeamTree($userId);
$user = getUserById($userId);
$badges = getUserBadges($userId);
?>

<?php include '../includes/header.php'; ?>

<div class="dashboard-container">
    <div class="row">
        <!-- Sidebar (same as dashboard) -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">My Team</h4>
                        <div>
                            <span class="badge bg-light text-dark">Total Members: <?= count($teamMembers) ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Team Search and Filters -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search team members..." id="teamSearch">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                                    <button type="button" class="btn btn-outline-primary" data-filter="level1">Level 1</button>
                                    <button type="button" class="btn btn-outline-primary" data-filter="level2">Level 2</button>
                                    <button type="button" class="btn btn-outline-primary" data-filter="level3">Level 3+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Tree Visualization -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Team Structure</h6>
                        </div>
                        <div class="card-body">
                            <div id="teamTree" style="height: 400px; overflow: auto;"></div>
                        </div>
                    </div>
                    
                    <!-- Team Members Table -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Team Members</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Level</th>
                                            <th>Join Date</th>
                                            <th>Sales</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($teamMembers as $member): ?>
                                        <tr data-level="<?= $member['level'] ?>">
                                            <td><?= $member['username'] ?></td>
                                            <td>
                                                <img src="<?=SITE_URL?>/assets/images/profile/<?= $member['image'] ?? 'default.jpg' ?>" width="30" class="rounded-circle me-2">
                                                <?= $member['full_name'] ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= getLevelBadgeColor($member['level']) ?>">
                                                    Level <?= $member['level'] ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y', strtotime($member['created_at'])) ?></td>
                                            <td>₹<?= number_format($member['total_sales'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $member['active'] ? 'success' : 'warning' ?>">
                                                    <?= $member['active'] ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary view-member" data-id="<?= $member['id'] ?>">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Member Details Modal -->
<div class="modal fade" id="memberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Member Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="memberDetails">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize team tree visualization
$(document).ready(function() {
    // Load team tree visualization
    loadTeamTree();
    
    // Filter team members
    $('[data-filter]').click(function() {
        const filter = $(this).data('filter');
        $('[data-filter]').removeClass('active');
        $(this).addClass('active');
        
        if (filter === 'all') {
            $('tbody tr').show();
        } else if (filter === 'level1') {
            $('tbody tr').hide();
            $('tbody tr[data-level="1"]').show();
        } else if (filter === 'level2') {
            $('tbody tr').hide();
            $('tbody tr[data-level="2"]').show();
        } else if (filter === 'level3') {
            $('tbody tr').hide();
            $('tbody tr[data-level="3"]').show();
        }
    });
    
    // Search team members
    $('#teamSearch').keyup(function() {
        const searchText = $(this).val().toLowerCase();
        $('tbody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
    });
    
    // View member details
    $('.view-member').click(function() {
        const memberId = $(this).data('id');
        $.ajax({
            url: '../api/get-member-details.php',
            method: 'POST',
            data: { memberId: memberId },
            success: function(response) {
                $('#memberDetails').html(response);
                $('#memberModal').modal('show');
            }
        });
    });
});

function loadTeamTree() {
    const treeData = <?= json_encode($teamTree) ?>;
    
    // Initialize OrgChart
    const chart = new OrgChart(document.getElementById("teamTree"), {
        template: "ula",
        enableSearch: false,
        scaleInitial: 0.8,
        mouseScrool: OrgChart.action.none,
        
        nodeBinding: {
            field_0: "name",
            field_1: "title",
            img_0: "img"
        },
        
        nodes: treeData
    });
}
</script>

<?php include '../includes/footer.php'; ?>