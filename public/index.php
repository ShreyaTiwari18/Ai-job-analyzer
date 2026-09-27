<?php
require_once __DIR__ . '/../src/bootstrap.php';

if (Auth::check()) {
    $user = Auth::user();
    header('Location: ' . ($user['role'] === 'admin' ? '../admin/dashboard.php' : 'dashboard.php'));
    exit;
}

header('Location: login.php');
exit;
