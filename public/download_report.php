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

$filename = 'resume-report-' . preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($resume['original_filename'], PATHINFO_FILENAME)) . '.html';

header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resume Report - <?= htmlspecialchars($resume['original_filename']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; padding: 2rem; }
        .card { border: none; border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 1.5rem; padding: 1.5rem; }
        .score-ring { display: inline-flex; align-items: center; justify-content: center; width: 110px; height: 110px; border-radius: 50%; font-size: 1.75rem; font-weight: 700; color: #fff; }
        .score-ring.good { background: #198754; }
        .score-ring.ok { background: #fd7e14; }
        .score-ring.poor { background: #dc3545; }
        .tag { display: inline-block; padding: 0.25rem 0.65rem; margin: 0.15rem; border-radius: 999px; background: #e9ecef; font-size: 0.85rem; }
        .tag.matched { background: #d1e7dd; color: #0f5132; }
        .tag.missing { background: #f8d7da; color: #842029; }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4">Resume Report: <?= htmlspecialchars($resume['original_filename']) ?></h2>
    <?php require __DIR__ . '/partials/analysis_body.php'; ?>
    <p class="text-muted text-center mt-4"><small>Generated on <?= date('Y-m-d') ?></small></p>
</div>
</body>
</html>
