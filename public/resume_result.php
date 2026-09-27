<?php
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireLogin();

$resumeId = (int) ($_GET['id'] ?? 0);
$db = Database::connection();

$stmt = $db->prepare('SELECT * FROM resumes WHERE id = ?');
$stmt->execute([$resumeId]);
$resume = $stmt->fetch();

if (!$resume || ($resume['user_id'] != Auth::user()['id'] && !Auth::isAdmin())) {
    http_response_code(404);
    exit('Resume not found.');
}

$stmt = $db->prepare('SELECT * FROM resume_analysis WHERE resume_id = ? ORDER BY id DESC LIMIT 1');
$stmt->execute([$resumeId]);
$analysis = $stmt->fetch();

if (!$analysis) {
    exit('This resume has not been analyzed yet.');
}

$decode = fn ($json) => json_decode($json ?? '[]', true) ?: [];

$personalInfo = $decode($analysis['personal_info']);
$education = $decode($analysis['education']);
$skills = $decode($analysis['skills']);
$technicalSkills = $decode($analysis['technical_skills']);
$softSkills = $decode($analysis['soft_skills']);
$experience = $decode($analysis['experience']);
$projects = $decode($analysis['projects']);
$certifications = $decode($analysis['certifications']);
$achievements = $decode($analysis['achievements']);
$strengths = $decode($analysis['strengths']);
$weaknesses = $decode($analysis['weaknesses']);
$missingSkills = $decode($analysis['missing_skills']);
$suggestions = $decode($analysis['suggestions']);
$recommendedRoles = $decode($analysis['recommended_roles']);

function scoreClass(int $score): string
{
    if ($score >= 75) return 'good';
    if ($score >= 50) return 'ok';
    return 'poor';
}

$pageTitle = 'Resume Analysis';
require __DIR__ . '/partials/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Analysis: <?= htmlspecialchars($resume['original_filename']) ?></h3>
    <a href="jobs.php?resume_id=<?= $resumeId ?>" class="btn btn-outline-primary">
        <i class="bi bi-briefcase"></i> Check Job Matches
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card p-4 text-center">
            <h6 class="text-muted">Resume Score</h6>
            <div class="score-ring <?= scoreClass($analysis['resume_score']) ?> mx-auto">
                <?= (int) $analysis['resume_score'] ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4 text-center">
            <h6 class="text-muted">ATS Score</h6>
            <div class="score-ring <?= scoreClass($analysis['ats_score']) ?> mx-auto">
                <?= (int) $analysis['ats_score'] ?>
            </div>
        </div>
    </div>
</div>

<div class="card p-4 mb-4">
    <h5>Summary</h5>
    <p class="mb-0"><?= nl2br(htmlspecialchars($analysis['resume_summary'])) ?></p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5><i class="bi bi-hand-thumbs-up text-success"></i> Strengths</h5>
            <ul>
                <?php foreach ($strengths as $s): ?><li><?= htmlspecialchars($s) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5><i class="bi bi-hand-thumbs-down text-danger"></i> Weaknesses</h5>
            <ul>
                <?php foreach ($weaknesses as $w): ?><li><?= htmlspecialchars($w) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<div class="card p-4 mb-4">
    <h5>Skills</h5>
    <?php foreach ($skills as $skill): ?>
        <span class="tag"><?= htmlspecialchars($skill) ?></span>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5>Education</h5>
            <?php foreach ($education as $e): ?>
                <p class="mb-1"><strong><?= htmlspecialchars($e['degree'] ?? '') ?></strong><br>
                <?= htmlspecialchars($e['institution'] ?? '') ?> <?= isset($e['year']) ? '(' . htmlspecialchars($e['year']) . ')' : '' ?></p>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5>Experience</h5>
            <?php foreach ($experience as $exp): ?>
                <p class="mb-1"><strong><?= htmlspecialchars($exp['role'] ?? '') ?></strong> at
                <?= htmlspecialchars($exp['company'] ?? '') ?> <?= isset($exp['duration']) ? '(' . htmlspecialchars($exp['duration']) . ')' : '' ?></p>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card p-4 mb-4">
    <h5>Projects</h5>
    <?php foreach ($projects as $p): ?>
        <div class="mb-2">
            <strong><?= htmlspecialchars($p['name'] ?? '') ?></strong>
            <p class="mb-1"><?= htmlspecialchars($p['description'] ?? '') ?></p>
        </div>
    <?php endforeach; ?>
</div>

<div class="card p-4 mb-4">
    <h5>Missing Skills to Improve Your Profile</h5>
    <?php foreach ($missingSkills as $m): ?>
        <span class="tag missing"><?= htmlspecialchars($m) ?></span>
    <?php endforeach; ?>
</div>

<div class="card p-4 mb-4">
    <h5>Suggestions</h5>
    <ul>
        <?php foreach ($suggestions as $s): ?><li><?= htmlspecialchars($s) ?></li><?php endforeach; ?>
    </ul>
</div>

<div class="card p-4 mb-4">
    <h5>Recommended Roles</h5>
    <?php foreach ($recommendedRoles as $r): ?>
        <span class="tag"><?= htmlspecialchars($r) ?></span>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
