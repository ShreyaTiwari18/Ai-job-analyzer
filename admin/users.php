<?php
$inAdmin = true;
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireAdmin();

$db = Database::connection();
$users = $db->query(
    "SELECT u.id, u.name, u.email, u.created_at, COUNT(r.id) resume_count
     FROM users u LEFT JOIN resumes r ON r.user_id = u.id
     WHERE u.role = 'user'
     GROUP BY u.id ORDER BY u.created_at DESC"
)->fetchAll();

$pageTitle = 'Users';
require __DIR__ . '/../public/partials/header.php';
?>
<h3 class="mb-4">Users</h3>
<div class="card p-4">
    <table class="table table-hover align-middle">
        <thead><tr><th>Name</th><th>Email</th><th>Joined</th><th>Resumes</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['created_at']) ?></td>
                <td><?= (int) $u['resume_count'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../public/partials/footer.php'; ?>
