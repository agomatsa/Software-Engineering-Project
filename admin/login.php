<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    redirect('/admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_fail();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username !== '' && $password !== '' && attempt_login($username, $password)) {
        set_flash('success', 'Welcome back.');
        redirect('/admin/index.php');
    }

    set_flash('error', 'Invalid credentials.');
    redirect('/admin/login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | <?= esc(APP_NAME) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-body admin-login-body">
<main class="login-shell" role="main" aria-labelledby="login-heading">
    <section class="login-card">
        <p class="kicker">Log in</p>
        <h1 id="login-heading">Sign in to the Vault</h1>
        <?php if ($error = get_flash('error')): ?>
            <div class="alert alert-error" role="alert"><?= esc($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/admin/login.php" class="stack-form" aria-label="Admin login form">
            <?= csrf_field() ?>
            <label>
                <span>Username</span>
                <input type="text" name="username" required maxlength="80" autocomplete="username">
            </label>
            <label>
                <span>Password</span>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>
        <p style="margin-top:1rem;text-align:center;">Don't have an account? <a href="/admin/register.php">Create one</a></p>
    </section>
</main>
</body>
</html>
