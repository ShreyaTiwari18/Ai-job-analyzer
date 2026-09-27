<?php
$inAdmin = true;
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireAdmin();

$db = Database::connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $stmt = $db->prepare('DELETE FROM jobs WHERE id = ?');
    $stmt->execute([(int) $_POST['id']]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Job deleted.'];
    header('Location: jobs.php');
    exit;
}

$jobs = $db->query('SELECT * FROM jobs ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Manage Jobs';
require __DIR__ . '/../public/partials/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Manage Jobs</h3>
    <a href="job_form.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Job</a>
</div>

<div class="card p-4">
    <table class="table table-hover align-middle">
        <thead><tr><th>Title</th><th>Company</th><th>Required Skills</th><th>Level</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($jobs as $job): ?>
            <tr>
                <td><?= htmlspecialchars($job['title']) ?></td>
                <td><?= htmlspecialchars($job['company'] ?? '') ?></td>
                <td><?php foreach (json_decode($job['required_skills'], true) ?: [] as $s): ?>
                    <span class="tag"><?= htmlspecialchars($s) ?></span>
                <?php endforeach; ?></td>
                <td><?= htmlspecialchars($job['experience_level']) ?></td>
                <td>
                    <a href="job_form.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this job?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $job['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../public/partials/footer.php'; ?>
