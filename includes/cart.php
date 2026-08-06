<?php
declare(strict_types=1);

/**
 * Initialize cart in session if not exists
 */
function init_cart(): void
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Add car to cart
 */
function add_to_cart(int $car_id, int $quantity = 1): void
{
    init_cart();
    
    if (isset($_SESSION['cart'][$car_id])) {
        $_SESSION['cart'][$car_id] += $quantity;
    } else {
        $_SESSION['cart'][$car_id] = $quantity;
    }
}

/**
 * Remove car from cart
 */
function remove_from_cart(int $car_id): void
{
    init_cart();
    unset($_SESSION['cart'][$car_id]);
}

/**
 * Get cart items with details
 */
function get_cart_items(): array
{
    init_cart();
    
    if (empty($_SESSION['cart'])) {
        return [];
    }
    
    $pdo = get_pdo();
    $car_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($car_ids), '?'));
    
    $stmt = $pdo->prepare("
        SELECT id, make, model, year, image_url, price
        FROM cars
        WHERE id IN ($placeholders)
        ORDER BY make, model
    ");
    
    $stmt->execute($car_ids);
    $cars = $stmt->fetchAll();
    
    $cart_items = [];
    foreach ($cars as $car) {
        $cart_items[] = [
            'id' => $car['id'],
            'make' => $car['make'],
            'model' => $car['model'],
            'year' => $car['year'],
            'image_url' => $car['image_url'],
            'price' => (float) $car['price'],
            'quantity' => $_SESSION['cart'][$car['id']],
        ];
    }
    
    return $cart_items;
}

/**
 * Get cart total
 */
function get_cart_total(): float
{
    $items = get_cart_items();
    $total = 0;
    
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    return round($total, 2);
}

/**
 * Get cart count
 */
function get_cart_count(): int
{
    init_cart();
    $count = 0;
    
    foreach ($_SESSION['cart'] as $quantity) {
        $count += $quantity;
    }
    
    return $count;
}

/**
 * Clear cart
 */
function clear_cart(): void
{
    $_SESSION['cart'] = [];
}

/**
 * Update cart item quantity
 */
function update_cart_quantity(int $car_id, int $quantity): void
{
    init_cart();
    
    if ($quantity <= 0) {
        remove_from_cart($car_id);
    } else {
        $_SESSION['cart'][$car_id] = $quantity;
    }
}
