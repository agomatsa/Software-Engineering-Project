<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cart.php';

verify_csrf_or_fail();

$cart_items = get_cart_items();
$cart_total = get_cart_total();

if (empty($cart_items)) {
    set_flash('error', 'Your cart is empty.');
    redirect('/cart.php');
}

$customer_name = trim((string) ($_POST['customer_name'] ?? ''));
$customer_email = trim((string) ($_POST['customer_email'] ?? ''));
$customer_phone = trim((string) ($_POST['customer_phone'] ?? ''));
$customer_address = trim((string) ($_POST['customer_address'] ?? ''));

// Validation
if (empty($customer_name) || empty($customer_email) || empty($customer_address)) {
    set_flash('error', 'Please fill in all required fields.');
    redirect('/checkout.php');
}

if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Please enter a valid email address.');
    redirect('/checkout.php');
}

try {
    $pdo = get_pdo();
    $pdo->beginTransaction();

    require_once __DIR__ . '/includes/auth.php';
    $user_id = is_logged_in() ? current_user()['id'] : null;

    // Create order
    $stmt = $pdo->prepare('
        INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, total_amount, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $user_id,
        $customer_name,
        $customer_email,
        $customer_phone ?: null,
        $customer_address,
        $cart_total,
        'pending'
    ]);

    $order_id = (int) $pdo->lastInsertId();

    // Add order items
    $stmt = $pdo->prepare('
        INSERT INTO order_items (order_id, car_id, price_at_purchase)
        VALUES (?, ?, ?)
    ');

    foreach ($cart_items as $item) {
        $stmt->execute([
            $order_id,
            $item['id'],
            $item['price']
        ]);
    }

    $pdo->commit();

    // Clear cart
    clear_cart();

    set_flash('success', 'Order placed successfully! Order ID: ' . $order_id);
    redirect('/order_confirmation.php?order_id=' . $order_id);
} catch (Exception $e) {
    $pdo->rollBack();
    set_flash('error', 'An error occurred while placing your order. Please try again.');
    redirect('/checkout.php');
}
