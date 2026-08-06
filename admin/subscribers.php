<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

require_role('admin');

$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_fail();

    $deleteId = (int) ($_POST['delete_id'] ?? 0);
    if ($deleteId > 0) {
        $deleteStmt = $pdo->prepare('DELETE FROM subscribers WHERE id = :id');
        $deleteStmt->execute(['id' => $deleteId]);
        set_flash('success', 'Subscriber removed.');
    }

    redirect('/admin/subscribers.php');
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$total = (int) $pdo->query('SELECT COUNT(*) FROM subscribers')->fetchColumn();
$pagination = paginate($total, $page, $perPage);

$listStmt = $pdo->prepare('SELECT id, email, date_subscribed FROM subscribers ORDER BY date_subscribed DESC LIMIT :limit OFFSET :offset');
$listStmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$listStmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$listStmt->execute();
$subscribers = $listStmt->fetchAll();

$pageTitle = 'Subscribers';
require __DIR__ . '/partials/header.php';
?>
<section class="admin-panel" aria-labelledby="subscribers-heading">
    <div class="admin-panel__header">
        <h1 id="subscribers-heading">Newsletter Subscribers</h1>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Date Subscribed</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($subscribers) === 0): ?>
                    <tr><td colspan="3">No subscribers yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($subscribers as $subscriber): ?>
                        <tr>
                            <td><?= esc($subscriber['email']) ?></td>
                            <td><?= esc((string) $subscriber['date_subscribed']) ?></td>
                            <td>
                                <form method="post" action="/admin/subscribers.php" onsubmit="return confirm('Delete subscriber?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="delete_id" value="<?= esc((string) $subscriber['id']) ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination['total_pages'] > 1): ?>
        <nav class="pagination" aria-label="Subscribers pagination">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="?page=<?= esc((string) $i) ?>" class="<?= $i === $pagination['page'] ? 'active' : '' ?>" aria-current="<?= $i === $pagination['page'] ? 'page' : 'false' ?>"><?= esc((string) $i) ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
