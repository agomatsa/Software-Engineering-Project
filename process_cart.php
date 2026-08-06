<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cart.php';

$action = $_GET['action'] ?? '';
$car_id = (int) ($_GET['car_id'] ?? 0);

if ($action === 'add' && $car_id > 0) {
    add_to_cart($car_id);
    set_flash('success', 'Cart added, thank you!');
    $referrer = $_GET['referrer'] ?? '/index.php';
    redirect($referrer);
} elseif ($action === 'remove' && $car_id > 0) {
    remove_from_cart($car_id);
    set_flash('success', 'Car removed from cart!');
    redirect('/cart.php');
} elseif ($action === 'update' && $car_id > 0) {
    $quantity = max(0, (int) ($_POST['quantity'] ?? 0));
    update_cart_quantity($car_id, $quantity);
    redirect('/cart.php');
} elseif ($action === 'clear') {
    clear_cart();
    set_flash('success', 'Cart cleared!');
    redirect('/cart.php');
}

redirect('/cart.php');
