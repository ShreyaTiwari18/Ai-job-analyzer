<?php
require_once __DIR__ . '/../src/bootstrap.php';

if (Auth::check()) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = Auth::attempt($email, $password);

    if ($user) {
        Auth::login($user);
        header('Location: ' . ($user['role'] === 'admin' ? '../admin/dashboard.php' : 'dashboard.php'));
        exit;
    }

    $error = 'Invalid email or password.';
}

$pageTitle = 'Login';
require __DIR__ . '/partials/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card p-4 mt-4">
            <h3 class="mb-3">Log in</h3>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" novalidate>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Log in</button>
            </form>
            <p class="text-center mt-3 mb-0">No account yet? <a href="register.php">Register</a></p>
        </div>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
