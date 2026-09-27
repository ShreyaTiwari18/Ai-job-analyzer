<?php
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireLogin();

$db = Database::connection();
$userId = Auth::user()['id'];

$stmt = $db->prepare(
    'SELECT r.id, r.original_filename, r.uploaded_at, a.resume_score, a.ats_score, a.skills, a.recommended_roles
     FROM resumes r
     LEFT JOIN resume_analysis a ON a.resume_id = r.id
     WHERE r.user_id = ?
     ORDER BY r.uploaded_at DESC'
);
$stmt->execute([$userId]);
$resumes = $stmt->fetchAll();

$latest = $resumes[0] ?? null;
$skillCount = 0;
$recommendedRoles = [];
$matchingJobsCount = 0;

if ($latest) {
    $skills = json_decode($latest['skills'] ?? '[]', true) ?: [];
    $skillCount = count($skills);
    $recommendedRoles = json_decode($latest['recommended_roles'] ?? '[]', true) ?: [];

    $stmt = $db->prepare('SELECT COUNT(*) c FROM job_matches WHERE resume_id = ? AND match_percentage >= 50');
    $stmt->execute([$latest['id']]);
    $matchingJobsCount = (int) $stmt->fetch()['c'];
}

$pageTitle = 'Dashboard';
require __DIR__ . '/partials/header.php';
?>
<h3 class="mb-4">Welcome, <?= htmlspecialchars(Auth::user()['name']) ?></h3>

<?php if (!$latest): ?>
    <div class="card p-4 text-center">
        <p class="mb-3">You haven't uploaded a resume yet.</p>
        <a href="upload.php" class="btn btn-primary">Upload your first resume</a>
    </div>
<?php else: ?>
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="card p-3 text-center">
                <small class="text-muted">Resume Score</small>
                <h2 class="mb-0"><?= (int) ($latest['resume_score'] ?? 0) ?></h2>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card p-3 text-center">
                <small class="text-muted">ATS Score</small>
                <h2 class="mb-0"><?= (int) ($latest['ats_score'] ?? 0) ?></h2>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card p-3 text-center">
                <small class="text-muted">Detected Skills</small>
                <h2 class="mb-0"><?= $skillCount ?></h2>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card p-3 text-center">
                <small class="text-muted">Matching Jobs (&ge;50%)</small>
                <h2 class="mb-0"><?= $matchingJobsCount ?></h2>
            </div>
        </div>
    </div>

    <div class="card p-4 mb-4">
        <h5>Top Recommended Roles</h5>
        <?php foreach ($recommendedRoles as $role): ?>
            <span class="tag"><?= htmlspecialchars($role) ?></span>
        <?php endforeach; ?>
    </div>

    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Your Resumes</h5>
            <a href="upload.php" class="btn btn-sm btn-primary">Upload Another</a>
        </div>
        <table class="table table-hover align-middle">
            <thead><tr><th>File</th><th>Uploaded</th><th>Resume Score</th><th>ATS Score</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($resumes as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['original_filename']) ?></td>
                    <td><?= htmlspecialchars($r['uploaded_at']) ?></td>
                    <td><?= (int) ($r['resume_score'] ?? 0) ?></td>
                    <td><?= (int) ($r['ats_score'] ?? 0) ?></td>
                    <td><a href="resume_result.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
