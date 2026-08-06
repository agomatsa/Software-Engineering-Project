<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/cart.php';

require_login();

$pdo = get_pdo();
$user = current_user();
$isAdmin = has_role('admin') && $user['role'] === 'admin';

if ($isAdmin) {
    $carCount = (int) $pdo->query('SELECT COUNT(*) FROM cars')->fetchColumn();
    $subscriberCount = (int) $pdo->query('SELECT COUNT(*) FROM subscribers')->fetchColumn();
    $messageCount = (int) $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();
    $latestCarsStmt = $pdo->query('SELECT id, make, model, year, created_at FROM cars ORDER BY created_at DESC LIMIT 5');
    $latestCars = $latestCarsStmt->fetchAll();
    $pageTitle = 'Dashboard';
} else {
    // Registered user (editor) dashboard data
    $cart_items = get_cart_items();
    $cart_total = get_cart_total();
    
    // Fetch orders placed by this user
    $ordersStmt = $pdo->prepare('SELECT id, total_amount, status, created_at FROM orders WHERE user_id = :user_id ORDER BY created_at DESC');
    $ordersStmt->execute(['user_id' => $user['id']]);
    $userOrders = $ordersStmt->fetchAll();
    
    $pageTitle = 'My Vault';
}

require __DIR__ . '/partials/header.php';
?>

<?php if ($isAdmin): ?>
    <section class="stats-grid" aria-label="Admin analytics">
        <article class="stat-card">
            <h2>Total Cars</h2>
            <p><?= esc((string) $carCount) ?></p>
        </article>
        <article class="stat-card">
            <h2>Subscribers</h2>
            <p><?= esc((string) $subscriberCount) ?></p>
        </article>
        <article class="stat-card">
            <h2>Messages</h2>
            <p><?= esc((string) $messageCount) ?></p>
        </article>
    </section>

    <section class="admin-panel" aria-labelledby="latest-cars-heading">
        <div class="admin-panel__header">
            <h2 id="latest-cars-heading">Latest Car Entries</h2>
            <a class="btn btn-primary" href="/admin/car_form.php">Add Car</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Make</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($latestCars) === 0): ?>
                        <tr><td colspan="5">No cars yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($latestCars as $car): ?>
                            <tr>
                                <td><?= esc($car['make']) ?></td>
                                <td><?= esc($car['model']) ?></td>
                                <td><?= esc((string) $car['year']) ?></td>
                                <td><?= esc((string) $car['created_at']) ?></td>
                                <td><a href="/admin/car_form.php?id=<?= esc((string) $car['id']) ?>">Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php else: ?>
    <!-- Registered User (Editor) Dashboard -->
    <div style="background: linear-gradient(112deg, var(--navy), #173d65); color: var(--cream); padding: 2.5rem; border-radius: var(--radius); margin-bottom: 2rem; border-bottom: 4px solid var(--gold); box-shadow: var(--shadow);">
        <p class="kicker" style="color: var(--gold); margin: 0 0 0.5rem 0;">Customer Dashboard</p>
        <h1 style="color: var(--cream); font-size: clamp(2rem, 4vw, 2.8rem); margin: 0 0 0.5rem 0; font-family: 'Cinzel', serif;">Welcome back, <?= esc($user['username']) ?>!</h1>
        <p style="margin: 0; opacity: 0.9; font-size: 1.1rem;">Manage your selected vintage cars, track your orders, and explore the vault.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr; gap: 2rem; margin-bottom: 2rem;">
        <!-- Current Cart -->
        <section class="admin-panel" aria-labelledby="user-cart-heading">
            <div class="admin-panel__header">
                <h2 id="user-cart-heading" style="font-family: 'Cinzel', serif; color: var(--navy);">My Shopping Cart</h2>
                <a class="btn btn-primary" href="/cart.php">View Full Cart</a>
            </div>
            
            <?php if (empty($cart_items)): ?>
                <div style="text-align: center; padding: 3rem 1rem; background: rgba(16, 35, 63, 0.02); border-radius: 10px; border: 1px dashed var(--border);">
                    <p style="font-size: 1.2rem; color: var(--muted); margin-bottom: 1.5rem;">Your shopping cart is currently empty.</p>
                    <a href="/index.php" class="btn btn-secondary">Explore Vintage Cars</a>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Car</th>
                                <th>Year</th>
                                <th style="text-align: center;">Quantity</th>
                                <th style="text-align: right;">Unit Price</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <?php if ($item['image_url']): ?>
                                                <img src="/<?= esc($item['image_url']) ?>" alt="<?= esc($item['make'] . ' ' . $item['model']) ?>" style="width: 60px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border);">
                                            <?php endif; ?>
                                            <strong><?= esc($item['make'] . ' ' . $item['model']) ?></strong>
                                        </div>
                                    </td>
                                    <td><?= esc((string) $item['year']) ?></td>
                                    <td style="text-align: center;"><?= esc((string) $item['quantity']) ?></td>
                                    <td style="text-align: right;">$<?= number_format($item['price'], 2) ?></td>
                                    <td style="text-align: right; font-weight: bold; color: var(--burgundy);">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--border);">
                    <h3 style="margin: 0; color: var(--navy);">Total Value: <span style="color: #28a745;">$<?= number_format($cart_total, 2) ?></span></h3>
                    <a href="/checkout.php" class="btn btn-primary" style="font-weight: bold;">Proceed to Checkout</a>
                </div>
            <?php endif; ?>
        </section>

        <!-- Order History -->
        <section class="admin-panel" aria-labelledby="user-orders-heading">
            <div class="admin-panel__header">
                <h2 id="user-orders-heading" style="font-family: 'Cinzel', serif; color: var(--navy);">My Order History</h2>
            </div>
            
            <?php if (empty($userOrders)): ?>
                <div style="text-align: center; padding: 3rem 1rem; background: rgba(16, 35, 63, 0.02); border-radius: 10px; border: 1px dashed var(--border);">
                    <p style="font-size: 1.2rem; color: var(--muted); margin-bottom: 1.5rem;">You haven't placed any orders yet.</p>
                    <p style="font-size: 0.95rem; color: var(--muted); margin: 0;">Add your dream vintage cars to your cart and place an order to get started!</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date Placed</th>
                                <th style="text-align: right;">Total Amount</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userOrders as $order): ?>
                                <tr>
                                    <td><strong>#<?= esc((string) $order['id']) ?></strong></td>
                                    <td><?= esc(date('M j, Y g:i A', strtotime($order['created_at']))) ?></td>
                                    <td style="text-align: right; font-weight: bold; color: var(--navy);">$<?= number_format((float) $order['total_amount'], 2) ?></td>
                                    <td style="text-align: center;">
                                        <?php
                                        $badgeClass = 'badge-new';
                                        if (in_array($order['status'], ['shipped', 'delivered'], true)) {
                                            $badgeClass = 'badge-read';
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>" style="padding: 0.35rem 0.75rem; font-weight: bold; font-family: inherit;">
                                            <?= esc(ucfirst($order['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
