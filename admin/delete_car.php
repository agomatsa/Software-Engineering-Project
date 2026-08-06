<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/cars.php');
}

verify_csrf_or_fail();

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'Invalid car ID.');
    redirect('/admin/cars.php');
}

$pdo = get_pdo();
$stmt = $pdo->prepare('SELECT image_url FROM cars WHERE id = :id');
$stmt->execute(['id' => $id]);
$car = $stmt->fetch();

if (!$car) {
    set_flash('error', 'Car not found.');
    redirect('/admin/cars.php');
}

$deleteStmt = $pdo->prepare('DELETE FROM cars WHERE id = :id');
$deleteStmt->execute(['id' => $id]);

$imageUrl = (string) $car['image_url'];
if (str_starts_with($imageUrl, UPLOAD_URL_PREFIX)) {
    $imagePath = BASE_PATH . '/' . $imageUrl;
    if (is_file($imagePath)) {
        unlink($imagePath);
    }
}

set_flash('success', 'Car entry deleted.');
redirect('/admin/cars.php');
