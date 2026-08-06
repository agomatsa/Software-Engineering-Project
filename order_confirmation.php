<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/site_header.php';

$pageTitle = 'Order Confirmation';

$order_id = (int) ($_GET['order_id'] ?? 0);
$flash_success = get_flash('success');

if ($order_id <= 0) {
    redirect('/index.php');
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
    redirect('/index.php');
}

$stmt = $pdo->prepare('
    SELECT oi.id, c.id as car_id, c.make, c.model, c.year, oi.price_at_purchase
    FROM order_items oi
    JOIN cars c ON oi.car_id = c.id
    WHERE oi.order_id = ?
    ORDER BY c.make, c.model
');
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<section class="container" style="padding: 2rem 0;">
    <div style="max-width: 700px; margin: 0 auto;">
        <h1>Order Confirmation</h1>

        <?php if ($flash_success): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                <?= esc($flash_success) ?>
            </div>
        <?php endif; ?>

        <div style="background: #f9f9f9; padding: 2rem; border-radius: 4px; border: 1px solid #ddd; margin-bottom: 2rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                <div>
                    <p style="margin: 0; color: #666; font-size: 0.9rem;">ORDER ID</p>
                    <p style="margin: 0; font-size: 1.5rem; font-weight: bold;">#<?= $order['id'] ?></p>
                </div>
                <div>
                    <p style="margin: 0; color: #666; font-size: 0.9rem;">STATUS</p>
                    <p style="margin: 0; font-size: 1.5rem; font-weight: bold; color: #ffc107;"><?= esc(ucfirst($order['status'])) ?></p>
                </div>
            </div>

            <h3>Customer Information</h3>
            <p>
                <strong><?= esc($order['customer_name']) ?></strong><br>
                <?= esc($order['customer_email']) ?><br>
                <?php if ($order['customer_phone']): ?>
                    <?= esc($order['customer_phone']) ?><br>
                <?php endif; ?>
            </p>

            <h3>Delivery Address</h3>
            <p style="white-space: pre-wrap;"><?= esc($order['customer_address']) ?></p>

            <h3>Order Items</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 1rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #ddd;">
                        <th style="text-align: left; padding: 0.75rem;">Car</th>
                        <th style="text-align: right; padding: 0.75rem;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 0.75rem;">
                                <strong><?= esc($item['make'] . ' ' . $item['model']) ?></strong> (<?= esc((string) $item['year']) ?>)
                            </td>
                            <td style="text-align: right; padding: 0.75rem;">$<?= number_format((float) $item['price_at_purchase'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="padding: 1rem; background: white; border-top: 2px solid #ddd; display: flex; justify-content: space-between; font-size: 1.2rem;">
                <strong>Total Amount:</strong>
                <strong style="color: #28a745;">$<?= number_format((float) $order['total_amount'], 2) ?></strong>
            </div>

            <p style="color: #666; font-size: 0.9rem; margin-top: 1rem;">
                Order Date: <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?>
            </p>
        </div>

        <div style="text-align: center;">
            <a href="/index.php" style="display: inline-block; background: #333; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px;">Back to Home</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>
