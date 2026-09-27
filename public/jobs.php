<?php
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireLogin();

$db = Database::connection();
$userId = Auth::user()['id'];

$resumeId = (int) ($_GET['resume_id'] ?? 0);

if (!$resumeId) {
    $stmt = $db->prepare('SELECT id FROM resumes WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    $resumeId = $row ? (int) $row['id'] : 0;
}

if (!$resumeId) {
    $pageTitle = 'Jobs';
    require __DIR__ . '/partials/header.php';
    echo '<div class="card p-4 text-center"><p>Upload a resume first to see job matches.</p>'
        . '<a href="upload.php" class="btn btn-primary">Upload Resume</a></div>';
    require __DIR__ . '/partials/footer.php';
    exit;
}

$stmt = $db->prepare('SELECT r.*, a.skills FROM resumes r JOIN resume_analysis a ON a.resume_id = r.id WHERE r.id = ? AND r.user_id = ?');
$stmt->execute([$resumeId, $userId]);
$resume = $stmt->fetch();

if (!$resume) {
    http_response_code(404);
    exit('Resume not found.');
}

$candidateSkills = json_decode($resume['skills'] ?? '[]', true) ?: [];

$jobs = $db->query('SELECT * FROM jobs ORDER BY created_at DESC')->fetchAll();

// Compute (and cache) a match for every job that doesn't have one yet for this resume.
$insertStmt = $db->prepare(
    'INSERT INTO job_matches (resume_id, job_id, match_percentage, matched_skills, missing_skills, ai_explanation)
     VALUES (?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE match_percentage = VALUES(match_percentage),
        matched_skills = VALUES(matched_skills), missing_skills = VALUES(missing_skills)'
);

foreach ($jobs as &$job) {
    $requiredSkills = json_decode($job['required_skills'], true) ?: [];
    $result = JobMatcher::match($candidateSkills, $requiredSkills);

    $stmt = $db->prepare('SELECT * FROM job_matches WHERE resume_id = ? AND job_id = ?');
    $stmt->execute([$resumeId, $job['id']]);
    $existing = $stmt->fetch();

    if (!$existing) {
        $explanation = '';
        try {
            $ai = new AiService();
            $explanation = $ai->explainJobMatch($candidateSkills, $requiredSkills, $result['matched'], $result['missing'], $result['percentage']);
        } catch (AiServiceException $e) {
            $explanation = 'AI explanation unavailable right now.';
        }
        $insertStmt->execute([
            $resumeId, $job['id'], $result['percentage'],
            json_encode($result['matched']), json_encode($result['missing']), $explanation,
        ]);
        $existing = ['match_percentage' => $result['percentage'], 'matched_skills' => json_encode($result['matched']),
            'missing_skills' => json_encode($result['missing']), 'ai_explanation' => $explanation];
    }

    $job['match'] = $existing;
}
unset($job);

usort($jobs, fn ($a, $b) => $b['match']['match_percentage'] <=> $a['match']['match_percentage']);

$pageTitle = 'Job Matches';
require __DIR__ . '/partials/header.php';
?>
<h3 class="mb-4">Job Matches for <?= htmlspecialchars($resume['original_filename']) ?></h3>

<?php if (!$jobs): ?>
    <div class="card p-4 text-center">No jobs have been posted yet.</div>
<?php endif; ?>

<?php foreach ($jobs as $job): $m = $job['match']; ?>
    <div class="card p-4 mb-3">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-1"><?= htmlspecialchars($job['title']) ?></h5>
                <p class="text-muted mb-2"><?= htmlspecialchars($job['company'] ?? '') ?></p>
            </div>
            <span class="badge bg-<?= $m['match_percentage'] >= 70 ? 'success' : ($m['match_percentage'] >= 40 ? 'warning' : 'danger') ?> fs-6">
                <?= (int) $m['match_percentage'] ?>% match
            </span>
        </div>
        <p><?= htmlspecialchars($job['description']) ?></p>
        <p class="mb-2"><?= htmlspecialchars($m['ai_explanation']) ?></p>
        <div>
            <?php foreach (json_decode($m['matched_skills'], true) ?: [] as $s): ?>
                <span class="tag matched"><?= htmlspecialchars($s) ?></span>
            <?php endforeach; ?>
            <?php foreach (json_decode($m['missing_skills'], true) ?: [] as $s): ?>
                <span class="tag missing"><?= htmlspecialchars($s) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
