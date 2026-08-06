<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin_auth();

$order_id = (int) ($_GET['id'] ?? 0);

if ($order_id <= 0) {
    redirect('/admin/orders.php');
}

$pdo = get_pdo();

$stmt = $pdo->prepare('
    SELECT id, customer_name, customer_email, customer_phone, customer_address, total_amount, status, created_at
    FROM orders
    WHERE id = ?
');
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('/admin/orders.php');
}

$stmt = $pdo->prepare('
    SELECT oi.id, c.id as car_id, c.make, c.model, c.year, c.image_url, oi.price_at_purchase
    FROM order_items oi
    JOIN cars c ON oi.car_id = c.id
    WHERE oi.order_id = ?
    ORDER BY c.make, c.model
');
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

$pageTitle = 'Order #' . $order_id;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?> | Agomatsa Motor Vault Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .admin-content { padding: 2rem; }
        .order-header { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; }
        .order-section { background: #f9f9f9; padding: 1.5rem; border-radius: 4px; border: 1px solid #ddd; margin-bottom: 2rem; }
        .order-section h3 { margin-top: 0; }
        .status-badge { padding: 0.5rem 1rem; border-radius: 4px; font-size: 1rem; font-weight: bold; display: inline-block; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-shipped { background: #d4edda; color: #155724; }
        .status-delivered { background: #cce5ff; color: #004085; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .back-link { display: inline-block; margin-bottom: 1rem; padding: 0.5rem 1rem; background: #666; color: white; text-decoration: none; border-radius: 4px; }
        .back-link:hover { background: #555; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f5f5f5; padding: 1rem; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 1rem; border-bottom: 1px solid #eee; }
    </style>
</head>
<body class="admin-body">
<?php require_once __DIR__ . '/../admin/partials/header.php'; ?>

<div class="admin-content">
    <a href="/admin/orders.php" class="back-link">← Back to Orders</a>

    <div class="order-header">
        <div>
            <h1>Order #<?= $order['id'] ?></h1>
            <p style="color: #666; margin-bottom: 1rem;">
                <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?>
            </p>
            <span class="status-badge status-<?= esc($order['status']) ?>">
                <?= esc(ucfirst($order['status'])) ?>
            </span>
        </div>
        <div style="text-align: right;">
            <h2 style="margin-top: 0; color: #28a745;">$<?= number_format((float) $order['total_amount'], 2) ?></h2>
            <a href="/admin/update_order_status.php?id=<?= $order['id'] ?>" style="display: inline-block; padding: 0.75rem 1.5rem; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">Update Status</a>
        </div>
    </div>

    <div class="order-header">
        <div class="order-section">
            <h3>Customer Information</h3>
            <p>
                <strong>Name:</strong> <?= esc($order['customer_name']) ?><br>
                <strong>Email:</strong> <?= esc($order['customer_email']) ?><br>
                <?php if ($order['customer_phone']): ?>
                    <strong>Phone:</strong> <?= esc($order['customer_phone']) ?><br>
                <?php endif; ?>
            </p>
        </div>

        <div class="order-section">
            <h3>Delivery Address</h3>
            <p style="white-space: pre-wrap; margin: 0;">
                <?= esc($order['customer_address']) ?>
            </p>
        </div>
    </div>

    <div class="order-section">
        <h3>Order Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Car</th>
                    <th>Year</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td>
                            <strong><?= esc($item['make'] . ' ' . $item['model']) ?></strong>
                        </td>
                        <td><?= esc((string) $item['year']) ?></td>
                        <td>$<?= number_format((float) $item['price_at_purchase'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../admin/partials/footer.php'; ?>
</body>
</html>
