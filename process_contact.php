<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php#contact');
}

verify_csrf_or_fail();

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Please submit a valid name, email, and message.');
    redirect('/index.php#contact');
}

try {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('INSERT INTO messages (name, email, message, date) VALUES (:name, :email, :message, NOW())');
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'message' => $message,
    ]);

    set_flash('contact_success', 'Your message has been received. Thank you.');
} catch (Throwable $throwable) {
    set_flash('error', 'Message could not be stored. Please try again later.');
}

redirect('/index.php#contact');
