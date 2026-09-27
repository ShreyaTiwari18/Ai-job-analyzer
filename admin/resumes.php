<?php
$inAdmin = true;
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireAdmin();

$db = Database::connection();
$resumes = $db->query(
    "SELECT r.id, r.original_filename, r.uploaded_at, u.name AS user_name, u.email,
            a.resume_score, a.ats_score
     FROM resumes r
     JOIN users u ON u.id = r.user_id
     LEFT JOIN resume_analysis a ON a.resume_id = r.id
     ORDER BY r.uploaded_at DESC"
)->fetchAll();

$pageTitle = 'Uploaded Resumes';
require __DIR__ . '/../public/partials/header.php';
?>
<h3 class="mb-4">Uploaded Resumes</h3>
<div class="card p-4">
    <table class="table table-hover align-middle">
        <thead><tr><th>User</th><th>File</th><th>Uploaded</th><th>Resume Score</th><th>ATS Score</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($resumes as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['user_name']) ?><br><small class="text-muted"><?= htmlspecialchars($r['email']) ?></small></td>
                <td><?= htmlspecialchars($r['original_filename']) ?></td>
                <td><?= htmlspecialchars($r['uploaded_at']) ?></td>
                <td><?= (int) ($r['resume_score'] ?? 0) ?></td>
                <td><?= (int) ($r['ats_score'] ?? 0) ?></td>
                <td><a href="../public/resume_result.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">View Analysis</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../public/partials/footer.php'; ?>
