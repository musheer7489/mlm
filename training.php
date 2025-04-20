<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$trainingModules = [
    [
        'title' => 'Getting Started',
        'description' => 'Learn the basics of our MLM system and how to get started',
        'videos' => [
            ['title' => 'Welcome to Our Team', 'duration' => '5:22', 'completed' => true],
            ['title' => 'Understanding the Compensation Plan', 'duration' => '12:45', 'completed' => true],
            ['title' => 'Your First 30 Days', 'duration' => '8:36', 'completed' => false]
        ]
    ],
    [
        'title' => 'Product Knowledge',
        'description' => 'Become an expert on our health product',
        'videos' => [
            ['title' => 'Product Ingredients and Benefits', 'duration' => '15:10', 'completed' => false],
            ['title' => 'How to Demonstrate the Product', 'duration' => '10:25', 'completed' => false],
            ['title' => 'Answering Common Questions', 'duration' => '7:45', 'completed' => false]
        ]
    ],
    [
        'title' => 'Sales Techniques',
        'description' => 'Learn proven methods to sell our product',
        'videos' => [
            ['title' => 'The Power of Storytelling', 'duration' => '9:15', 'completed' => false],
            ['title' => 'Overcoming Objections', 'duration' => '11:30', 'completed' => false],
            ['title' => 'Closing the Sale', 'duration' => '6:50', 'completed' => false]
        ]
    ],
    [
        'title' => 'Team Building',
        'description' => 'How to recruit and lead your team',
        'videos' => [
            ['title' => 'Finding Potential Team Members', 'duration' => '14:20', 'completed' => false],
            ['title' => 'Effective Team Communication', 'duration' => '8:45', 'completed' => false],
            ['title' => 'Motivating Your Team', 'duration' => '7:15', 'completed' => false]
        ]
    ]
];
function calculateTrainingProgress(array $trainingModules): array
{
    $progress = [];

    foreach ($trainingModules as $module) {
        $totalVideos = count($module['videos']);
        $completedVideos = 0;

        foreach ($module['videos'] as $video) {
            if ($video['completed']) {
                $completedVideos++;
            }
        }

        $completionPercentage = ($totalVideos > 0) ? round(($completedVideos / $totalVideos) * 100) : 0;

        $progress[] = [
            'module_title' => $module['title'],
            'total_videos' => $totalVideos,
            'completed_videos' => $completedVideos,
            'completion_percentage' => $completionPercentage,
        ];
    }

    return $progress;
}
$progress = calculateTrainingProgress($trainingModules);
$totalCompletion = 0;
foreach ($progress as $moduleProgress) {
    $totalCompletion += $moduleProgress['completion_percentage'];
}
$overallProgress = (count($progress) > 0) ? round($totalCompletion / count($progress)) : 0;
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="progress position-relative" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $overallProgress ?>%;" 
                             aria-valuenow="<?= $overallProgress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="my-3">
                        <h2 class="mb-0"><?= $overallProgress ?>%</h2>
                        <small class="text-muted">Training Completed</small>
                    </div>
                    
                    <div class="list-group">
                        <?php foreach($trainingModules as $index => $module): ?>
                        <a href="#module-<?= $index ?>" class="list-group-item list-group-item-action">
                            <?= $module['title'] ?>
                            <span class="float-end">
                                <?= count(array_filter($module['videos'], function($v) { return $v['completed']; })) ?> /
                                <?= count($module['videos']) ?>
                            </span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Training Resources</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#"><i class="fas fa-file-pdf me-2"></i>Product Brochure</a></li>
                        <li class="mb-2"><a href="#"><i class="fas fa-file-pdf me-2"></i>Compensation Plan</a></li>
                        <li class="mb-2"><a href="#"><i class="fas fa-file-pdf me-2"></i>Sales Scripts</a></li>
                        <li><a href="#"><i class="fas fa-file-pdf me-2"></i>FAQs</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Distributor Training Portal</h4>
                </div>
                <div class="card-body">
                    <?php foreach($trainingModules as $index => $module): ?>
                    <div class="mb-5" id="module-<?= $index ?>">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3><?= $module['title'] ?></h3>
                            <span class="badge bg-secondary">
                                <?= count(array_filter($module['videos'], function($v) { return $v['completed']; })) ?> /
                                <?= count($module['videos']) ?> completed
                            </span>
                        </div>
                        <p class="text-muted mb-4"><?= $module['description'] ?></p>
                        
                        <div class="list-group">
                            <?php foreach($module['videos'] as $video): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= $video['title'] ?></h6>
                                        <small class="text-muted"><?= $video['duration'] ?></small>
                                    </div>
                                    <div>
                                        <?php if ($video['completed']): ?>
                                            <span class="badge bg-success me-2"><i class="fas fa-check"></i> Completed</span>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#videoModal" 
                                                data-video="<?= urlencode($video['title']) ?>">
                                            <i class="fas fa-play me-1"></i> Play
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoModalTitle">Training Video</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="ratio ratio-16x9">
                    <iframe id="trainingVideo" src="" allowfullscreen></iframe>
                </div>
            </div>
            <div class="modal-footer">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="markCompleted">
                    <label class="form-check-label" for="markCompleted">
                        Mark as completed
                    </label>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle video modal
    $('#videoModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const videoTitle = decodeURIComponent(button.data('video'));
        const modal = $(this);
        
        modal.find('.modal-title').text(videoTitle);
        
        // In a real implementation, you would load the actual video URL
        modal.find('iframe').attr('src', 'https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1');
    });
    
    // Handle modal close and mark as completed
    $('#videoModal').on('hidden.bs.modal', function() {
        if ($('#markCompleted').is(':checked')) {
            // In a real implementation, you would save this to the database
            alert('Video marked as completed!');
            $('#markCompleted').prop('checked', false);
        }
        
        // Stop video playback
        $(this).find('iframe').attr('src', '');
    });
});

function calculateTrainingProgress(modules) {
    let totalVideos = 0;
    let completedVideos = 0;
    
    modules.forEach(module => {
        totalVideos += module.videos.length;
        completedVideos += module.videos.filter(v => v.completed).length;
    });
    
    return totalVideos > 0 ? Math.round((completedVideos / totalVideos) * 100) : 0;
}
</script>

<?php include 'includes/footer.php'; ?>