<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin_auth();

$pageTitle = 'Orders';
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 20;

$pdo = get_pdo();

// Get total count
$stmt = $pdo->query('SELECT COUNT(*) as count FROM orders');
$result = $stmt->fetch();
$total_orders = (int) $result['count'];
$total_pages = max(1, (int) ceil($total_orders / $per_page));

// Ensure page is within bounds
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$stmt = $pdo->prepare('
    SELECT id, customer_name, customer_email, total_amount, status, created_at
    FROM orders
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
');
$stmt->bindValue(1, $per_page, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

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
        .page-title { margin-bottom: 2rem; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
        th { background: #f5f5f5; padding: 1rem; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 1rem; border-bottom: 1px solid #eee; }
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.9rem; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-shipped { background: #d4edda; color: #155724; }
        .status-delivered { background: #cce5ff; color: #004085; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .pagination { display: flex; gap: 0.5rem; justify-content: center; margin: 2rem 0; }
        .pagination a, .pagination span { padding: 0.5rem 0.75rem; border: 1px solid #ddd; text-decoration: none; border-radius: 4px; }
        .pagination a:hover { background: #f5f5f5; }
        .pagination .active { background: #333; color: white; border-color: #333; }
        .action-links { display: flex; gap: 0.5rem; }
        .action-links a { padding: 0.25rem 0.75rem; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9rem; }
        .action-links a:hover { background: #0056b3; }
    </style>
</head>
<body class="admin-body">
<?php require_once __DIR__ . '/../admin/partials/header.php'; ?>

<div class="admin-content">
    <div class="page-title">
        <h1><?= esc($pageTitle) ?></h1>
        <p>Total Orders: <?= $total_orders ?></p>
    </div>

    <?php if (empty($orders)): ?>
        <p>No orders found.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>#<?= $order['id'] ?></strong></td>
                            <td><?= esc($order['customer_name']) ?></td>
                            <td><?= esc($order['customer_email']) ?></td>
                            <td><strong>$<?= number_format((float) $order['total_amount'], 2) ?></strong></td>
                            <td>
                                <span class="status-badge status-<?= esc($order['status']) ?>">
                                    <?= esc(ucfirst($order['status'])) ?>
                                </span>
                            </td>
                            <td><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></td>
                            <td>
                                <div class="action-links">
                                    <a href="/admin/order_details.php?id=<?= $order['id'] ?>">View</a>
                                    <a href="/admin/update_order_status.php?id=<?= $order['id'] ?>" style="background: #28a745;">Update</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1">« First</a>
                    <a href="?page=<?= $page - 1 ?>">‹ Previous</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>">Next ›</a>
                    <a href="?page=<?= $total_pages ?>">Last »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../admin/partials/footer.php'; ?>
</body>
</html>
