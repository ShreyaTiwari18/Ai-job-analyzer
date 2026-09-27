<?php
$inAdmin = true;
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireAdmin();

$db = Database::connection();
$jobId = (int) ($_GET['id'] ?? 0);
$job = null;

if ($jobId) {
    $stmt = $db->prepare('SELECT * FROM jobs WHERE id = ?');
    $stmt->execute([$jobId]);
    $job = $stmt->fetch();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $level = $_POST['experience_level'] ?? 'entry';
    $skillsRaw = trim($_POST['required_skills'] ?? '');
    $requiredSkills = array_values(array_filter(array_map('trim', explode(',', $skillsRaw))));

    if ($title === '' || $description === '' || !$requiredSkills) {
        $errors[] = 'Title, description and at least one required skill are needed.';
    }

    if (!$errors) {
        if ($jobId) {
            $stmt = $db->prepare('UPDATE jobs SET title=?, company=?, description=?, required_skills=?, experience_level=? WHERE id=?');
            $stmt->execute([$title, $company, $description, json_encode($requiredSkills), $level, $jobId]);
            // Existing cached matches for this job are now stale; clear them so they recompute.
            $db->prepare('DELETE FROM job_matches WHERE job_id = ?')->execute([$jobId]);
        } else {
            $stmt = $db->prepare('INSERT INTO jobs (title, company, description, required_skills, experience_level, created_by) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $company, $description, json_encode($requiredSkills), $level, Auth::user()['id']]);
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Job saved.'];
        header('Location: jobs.php');
        exit;
    }
}

$pageTitle = $jobId ? 'Edit Job' : 'Add Job';
require __DIR__ . '/../public/partials/header.php';
$existingSkills = $job ? implode(', ', json_decode($job['required_skills'], true) ?: []) : ($_POST['required_skills'] ?? '');
?>
<div class="card p-4">
    <h3 class="mb-3"><?= $jobId ? 'Edit Job' : 'Add Job' ?></h3>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($job['title'] ?? $_POST['title'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Company</label>
            <input type="text" name="company" class="form-control" value="<?= htmlspecialchars($job['company'] ?? $_POST['company'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($job['description'] ?? $_POST['description'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Required skills (comma-separated)</label>
            <input type="text" name="required_skills" class="form-control" value="<?= htmlspecialchars($existingSkills) ?>" placeholder="PHP, MySQL, JavaScript" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Experience level</label>
            <select name="experience_level" class="form-select">
                <?php foreach (['entry', 'mid', 'senior'] as $lvl): ?>
                    <option value="<?= $lvl ?>" <?= ($job['experience_level'] ?? 'entry') === $lvl ? 'selected' : '' ?>><?= ucfirst($lvl) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save Job</button>
        <a href="jobs.php" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
<?php require __DIR__ . '/../public/partials/footer.php'; ?>
