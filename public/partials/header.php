<?php
$user = Auth::user();
// $inAdmin must be set to true by any script under admin/ before including this partial.
$inAdmin = $inAdmin ?? false;
$assetPrefix = $inAdmin ? '../public/' : '';
$publicPrefix = $inAdmin ? '../public/' : '';
$adminPrefix = $inAdmin ? '' : '../admin/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Resume Analyzer AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= $assetPrefix ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= $user ? ($user['role'] === 'admin' ? $adminPrefix . 'dashboard.php' : $publicPrefix . 'dashboard.php') : $publicPrefix . 'index.php' ?>">
            <i class="bi bi-file-earmark-text"></i> Resume Analyzer AI
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto">
                <?php if ($user && $user['role'] === 'user'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $publicPrefix ?>dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $publicPrefix ?>upload.php">Upload Resume</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $publicPrefix ?>jobs.php">Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $publicPrefix ?>logout.php">Logout (<?= htmlspecialchars($user['name']) ?>)</a></li>
                <?php elseif ($user && $user['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $adminPrefix ?>dashboard.php">Admin Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $adminPrefix ?>jobs.php">Manage Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $adminPrefix ?>users.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $adminPrefix ?>resumes.php">Resumes</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $publicPrefix ?>logout.php">Logout (<?= htmlspecialchars($user['name']) ?>)</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $publicPrefix ?>login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $publicPrefix ?>register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
    <?php if ($flash = $_SESSION['flash'] ?? null): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
