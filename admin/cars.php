<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

require_admin_auth();

$pdo = get_pdo();
$search = trim((string) ($_GET['search'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;

$where = '';
$params = [];

if ($search !== '') {
    $where = 'WHERE make LIKE :search OR model LIKE :search OR CAST(year AS CHAR) LIKE :search';
    $params['search'] = '%' . $search . '%';
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM cars {$where}");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$pagination = paginate($total, $page, $perPage);

$listStmt = $pdo->prepare("SELECT id, make, model, year, image_url, created_at FROM cars {$where} ORDER BY year DESC, make ASC LIMIT :limit OFFSET :offset");
foreach ($params as $key => $value) {
    $listStmt->bindValue(':' . $key, $value);
}
$listStmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$listStmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$listStmt->execute();
$cars = $listStmt->fetchAll();

$pageTitle = 'Manage Cars';
require __DIR__ . '/partials/header.php';
?>
<section class="admin-panel" aria-labelledby="cars-heading">
    <div class="admin-panel__header">
        <h1 id="cars-heading">Cars</h1>
        <a class="btn btn-primary" href="/admin/car_form.php">Add Car</a>
    </div>

    <form class="filter-form filter-form--admin" method="get" action="/admin/cars.php" aria-label="Search cars">
        <label>
            <span>Search by make, model, year</span>
            <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Example: Jaguar 1986">
        </label>
        <button type="submit" class="btn btn-secondary">Search</button>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Make</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($cars) === 0): ?>
                    <tr><td colspan="6">No cars found.</td></tr>
                <?php else: ?>
                    <?php foreach ($cars as $car): ?>
                        <tr>
                            <td><img class="table-thumb" src="/<?= esc($car['image_url']) ?>" alt="<?= esc($car['make'] . ' ' . $car['model']) ?>"></td>
                            <td><?= esc($car['make']) ?></td>
                            <td><?= esc($car['model']) ?></td>
                            <td><?= esc((string) $car['year']) ?></td>
                            <td><?= esc((string) $car['created_at']) ?></td>
                            <td class="table-actions">
                                <a href="/admin/car_form.php?id=<?= esc((string) $car['id']) ?>">Edit</a>
                                <?php if (has_role('admin')): ?>
                                    <form method="post" action="/admin/delete_car.php" onsubmit="return confirm('Delete this car entry?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= esc((string) $car['id']) ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination['total_pages'] > 1): ?>
        <nav class="pagination" aria-label="Cars pagination">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="?<?= esc(http_build_query(['search' => $search, 'page' => $i])) ?>" class="<?= $i === $pagination['page'] ? 'active' : '' ?>" aria-current="<?= $i === $pagination['page'] ? 'page' : 'false' ?>"><?= esc((string) $i) ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
