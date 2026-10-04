<?php
// Expects $resume, $analysis and the decoded arrays (education, skills, etc.) to already
// be set by whichever page includes this partial.
?>
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
            <h5>Strengths</h5>
            <ul>
                <?php foreach ($strengths as $s): ?><li><?= htmlspecialchars($s) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5>Weaknesses</h5>
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
