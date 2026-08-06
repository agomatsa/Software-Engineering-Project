<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? APP_NAME;
require_once __DIR__ . '/cart.php';
$cart_count = get_cart_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?> | <?= esc(APP_NAME) ?></title>
    <meta name="description" content="Discover iconic vintage cars, their stories, and preserved craftsmanship from every era.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;700&family=Crimson+Text:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header" role="banner">
    <div class="container site-header__inner">
        <a class="brand" href="/index.php" aria-label="Agomatsa Motor Vault home">Agomatsa Motor Vault</a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Open navigation">Menu</button>
        <nav id="site-nav" class="site-nav" aria-label="Main navigation">
            <a href="#featured">Collections</a>
            <a href="#gallery">Gallery</a>
            <a href="#timeline">History</a>
            <a href="#contact">Contact</a>
            <a href="/cart.php" style="position: relative; display: inline-block;">
                🛒 Cart <?php if ($cart_count > 0): ?><span style="background: #d32f2f; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; position: absolute; top: -8px; right: -8px;"><?= $cart_count ?></span><?php endif; ?>
            </a>
            <a href="/admin/register.php" class="admin-link">Register</a>
            <a href="/admin/login.php" class="admin-link">Login</a>
        </nav>
    </div>
</header>
<main id="main-content" role="main">
