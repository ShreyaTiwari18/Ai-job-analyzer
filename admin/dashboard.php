<?php
$inAdmin = true;
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireAdmin();

$db = Database::connection();

$totalUsers = (int) $db->query("SELECT COUNT(*) c FROM users WHERE role = 'user'")->fetch()['c'];
$totalResumes = (int) $db->query('SELECT COUNT(*) c FROM resumes')->fetch()['c'];
$totalJobs = (int) $db->query('SELECT COUNT(*) c FROM jobs')->fetch()['c'];
$avgAts = round((float) ($db->query('SELECT AVG(ats_score) a FROM resume_analysis')->fetch()['a'] ?? 0), 1);

$jobStats = $db->query(
    "SELECT j.title, COUNT(m.id) matches, ROUND(AVG(m.match_percentage), 1) avg_match
     FROM jobs j LEFT JOIN job_matches m ON m.job_id = j.id
     GROUP BY j.id ORDER BY matches DESC LIMIT 10"
)->fetchAll();

$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../public/partials/header.php';
?>
<h3 class="mb-4">Admin Dashboard</h3>

<div class="row g-4 mb-4">
    <div class="col-md-3 col-6">
        <div class="card p-3 text-center"><small class="text-muted">Users</small><h2><?= $totalUsers ?></h2></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card p-3 text-center"><small class="text-muted">Resumes Uploaded</small><h2><?= $totalResumes ?></h2></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card p-3 text-center"><small class="text-muted">Jobs Posted</small><h2><?= $totalJobs ?></h2></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card p-3 text-center"><small class="text-muted">Avg ATS Score</small><h2><?= $avgAts ?></h2></div>
    </div>
</div>

<div class="card p-4">
    <h5>Job Match Statistics</h5>
    <canvas id="jobChart" height="100"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('jobChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($jobStats, 'title')) ?>,
        datasets: [{
            label: 'Average Match %',
            data: <?= json_encode(array_column($jobStats, 'avg_match')) ?>,
            backgroundColor: '#0d6efd'
        }]
    },
    options: { scales: { y: { beginAtZero: true, max: 100 } } }
});
</script>

<?php require __DIR__ . '/../public/partials/footer.php'; ?>
