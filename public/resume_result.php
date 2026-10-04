<?php
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireLogin();

$resumeId = (int) ($_GET['id'] ?? 0);
$data = ResumeAnalysisLoader::load($resumeId, Auth::user()['id'], Auth::isAdmin());

if (!$data) {
    http_response_code(404);
    exit('Resume not found or not analyzed yet.');
}

extract($data);

$pageTitle = 'Resume Analysis';
require __DIR__ . '/partials/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Analysis: <?= htmlspecialchars($resume['original_filename']) ?></h3>
    <div>
        <a href="download_report.php?id=<?= $resumeId ?>" class="btn btn-outline-secondary">
            <i class="bi bi-download"></i> Download Report
        </a>
        <a href="jobs.php?resume_id=<?= $resumeId ?>" class="btn btn-outline-primary">
            <i class="bi bi-briefcase"></i> Check Job Matches
        </a>
    </div>
</div>

<?php require __DIR__ . '/partials/analysis_body.php'; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
