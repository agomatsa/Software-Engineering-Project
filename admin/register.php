<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (is_logged_in()) {
    redirect('/admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_fail();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm_password = (string) ($_POST['confirm_password'] ?? '');

    if ($username === '') {
        set_flash('error', 'Username is required.');
        redirect('/admin/register.php');
    }

    if (strlen($username) > 80) {
        set_flash('error', 'Username cannot exceed 80 characters.');
        redirect('/admin/register.php');
    }

    if (strlen($password) < 8) {
        set_flash('error', 'Password must be at least 8 characters long.');
        redirect('/admin/register.php');
    }

    if ($password !== $confirm_password) {
        set_flash('error', 'Passwords do not match.');
        redirect('/admin/register.php');
    }

    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    if ($stmt->fetch()) {
        set_flash('error', 'Username is already taken.');
        redirect('/admin/register.php');
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, "editor")');
    
    try {
        $stmt->execute([
            'username' => $username,
            'password_hash' => $password_hash,
        ]);
        
        if (attempt_login($username, $password)) {
            set_flash('success', 'Account created successfully. Welcome!');
            redirect('/admin/index.php');
        } else {
            set_flash('success', 'Account created successfully. Please sign in.');
            redirect('/admin/login.php');
        }
    } catch (PDOException $e) {
        set_flash('error', 'An error occurred during registration. Please try again.');
        redirect('/admin/register.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | <?= esc(APP_NAME) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-body admin-login-body">
<main class="login-shell" role="main" aria-labelledby="register-heading">
    <section class="login-card">
        <p class="kicker">New account</p>
        <h1 id="register-heading">Join the Vault</h1>
        <?php if ($error = get_flash('error')): ?>
            <div class="alert alert-error" role="alert"><?= esc($error) ?></div>
        <?php endif; ?>
        <?php if ($success = get_flash('success')): ?>
            <div class="alert alert-success" role="alert"><?= esc($success) ?></div>
        <?php endif; ?>
        <form method="post" action="/admin/register.php" class="stack-form" aria-label="Admin registration form">
            <?= csrf_field() ?>
            <label>
                <span>Username</span>
                <input type="text" name="username" required maxlength="80" autocomplete="username">
            </label>
            <label>
                <span>Password (min. 8 characters)</span>
                <input type="password" name="password" required minlength="8" autocomplete="new-password">
            </label>
            <label>
                <span>Confirm Password</span>
                <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
            </label>
            <button type="submit" class="btn btn-secondary">Create Account</button>
        </form>
        <p style="margin-top:1rem;text-align:center;">Already have an account? <a href="/admin/login.php">Sign in</a></p>
    </section>
</main>
</body>
</html>
