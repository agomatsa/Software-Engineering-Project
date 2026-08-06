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

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_fail();

    $status = trim((string) ($_POST['status'] ?? ''));
    $valid_statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

    if (!in_array($status, $valid_statuses, true)) {
        set_flash('error', 'Invalid status.');
        redirect('/admin/update_order_status.php?id=' . $order_id);
    }

    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $order_id]);

    set_flash('success', 'Order status updated successfully.');
    redirect('/admin/order_details.php?id=' . $order_id);
}

// Get order
$stmt = $pdo->prepare('SELECT id, customer_name, status FROM orders WHERE id = ?');
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('/admin/orders.php');
}

$pageTitle = 'Update Order Status';
$valid_statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
$flash_error = get_flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?> | Agomatsa Motor Vault Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .admin-content { padding: 2rem; max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        select { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
        .form-actions { display: flex; gap: 1rem; margin-top: 2rem; }
        button, .cancel-link { padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; text-decoration: none; display: inline-block; }
        button { background: #007bff; color: white; }
        button:hover { background: #0056b3; }
        .cancel-link { background: #666; color: white; }
        .cancel-link:hover { background: #555; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .back-link { display: inline-block; margin-bottom: 1rem; padding: 0.5rem 1rem; background: #666; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body class="admin-body">
<?php require_once __DIR__ . '/../admin/partials/header.php'; ?>

<div class="admin-content">
    <a href="/admin/order_details.php?id=<?= $order_id ?>" class="back-link">← Back to Order</a>

    <h1><?= esc($pageTitle) ?></h1>
    <p>Order #<?= $order_id ?> - <?= esc($order['customer_name']) ?></p>

    <?php if ($flash_error): ?>
        <div class="alert alert-error"><?= esc($flash_error) ?></div>
    <?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="status">Order Status *</label>
            <select id="status" name="status" required>
                <option value="">-- Select Status --</option>
                <?php foreach ($valid_statuses as $status_option): ?>
                    <option value="<?= $status_option ?>" <?= $order['status'] === $status_option ? 'selected' : '' ?>>
                        <?= ucfirst($status_option) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit">Update Status</button>
            <a href="/admin/order_details.php?id=<?= $order_id ?>" class="cancel-link">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../admin/partials/footer.php'; ?>
</body>
</html>
