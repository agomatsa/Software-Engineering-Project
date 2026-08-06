<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Admin';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?> | <?= esc(APP_NAME) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-body">
<header class="admin-header" role="banner">
    <div class="admin-header__inner container">
        <a class="brand" href="/admin/index.php" aria-label="Admin dashboard home">
            <?= (has_role('admin') && current_user()['role'] === 'admin') ? 'Agomatsa Motor Vault Admin' : 'Agomatsa Motor Vault' ?>
        </a>
         <nav class="admin-nav" aria-label="Admin navigation">
             <?php if (has_role('admin') && current_user()['role'] === 'admin'): ?>
                 <a href="/admin/index.php">Dashboard</a>
                 <a href="/admin/cars.php">Cars</a>
                 <a href="/admin/orders.php">Orders</a>
                 <a href="/admin/subscribers.php">Subscribers</a>
                 <a href="/admin/messages.php">Messages</a>
                 <a href="/admin/users.php">Accounts</a>
             <?php else: ?>
                 <a href="/admin/index.php">My Account</a>
                 <a href="/cart.php">My Cart</a>
             <?php endif; ?>
         </nav>
        <div class="admin-user">
            <span><?= esc($user['username'] ?? '') ?></span>
            <a href="/admin/logout.php">Logout</a>
        </div>
    </div>
</header>
<main class="container admin-main" role="main">
    <?php if ($success = get_flash('success')): ?>
        <div class="alert alert-success" role="status" aria-live="polite"><?= esc($success) ?></div>
    <?php endif; ?>
    <?php if ($error = get_flash('error')): ?>
        <div class="alert alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>
