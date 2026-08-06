<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php#contact');
}

verify_csrf_or_fail();

$email = trim((string) ($_POST['email'] ?? ''));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Please provide a valid email address.');
    redirect('/index.php#contact');
}

try {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('INSERT INTO subscribers (email, date_subscribed) VALUES (:email, NOW()) ON DUPLICATE KEY UPDATE email = email');
    $stmt->execute(['email' => $email]);

    set_flash('newsletter_success', 'Subscription successful. Welcome to the vault.');
} catch (Throwable $throwable) {
    set_flash('error', 'Subscription failed. Please try again later.');
}

redirect('/index.php#contact');
