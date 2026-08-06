<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

require_role('admin');

$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_fail();

    $markReadId = (int) ($_POST['mark_read_id'] ?? 0);
    $deleteId = (int) ($_POST['delete_id'] ?? 0);

    if ($markReadId > 0) {
        $readStmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE id = :id");
        $readStmt->execute(['id' => $markReadId]);
        set_flash('success', 'Message marked as read.');
    }

    if ($deleteId > 0) {
        $deleteStmt = $pdo->prepare('DELETE FROM messages WHERE id = :id');
        $deleteStmt->execute(['id' => $deleteId]);
        set_flash('success', 'Message deleted.');
    }

    redirect('/admin/messages.php');
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;
$total = (int) $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();
$pagination = paginate($total, $page, $perPage);

$listStmt = $pdo->prepare('SELECT id, name, email, message, status, date FROM messages ORDER BY date DESC LIMIT :limit OFFSET :offset');
$listStmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$listStmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$listStmt->execute();
$messages = $listStmt->fetchAll();

$pageTitle = 'Messages';
require __DIR__ . '/partials/header.php';
?>
<section class="admin-panel" aria-labelledby="messages-heading">
    <div class="admin-panel__header">
        <h1 id="messages-heading">Contact Messages</h1>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>From</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($messages) === 0): ?>
                    <tr><td colspan="5">No messages yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <tr>
                            <td>
                                <strong><?= esc($message['name']) ?></strong><br>
                                <a href="mailto:<?= esc($message['email']) ?>"><?= esc($message['email']) ?></a>
                            </td>
                            <td><?= nl2br(esc($message['message'])) ?></td>
                            <td>
                                <span class="badge <?= $message['status'] === 'new' ? 'badge-new' : 'badge-read' ?>">
                                    <?= esc($message['status']) ?>
                                </span>
                            </td>
                            <td><?= esc((string) $message['date']) ?></td>
                            <td class="table-actions">
                                <?php if ($message['status'] === 'new'): ?>
                                    <form method="post" action="/admin/messages.php">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="mark_read_id" value="<?= esc((string) $message['id']) ?>">
                                        <button type="submit">Mark Read</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="/admin/messages.php" onsubmit="return confirm('Delete this message?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="delete_id" value="<?= esc((string) $message['id']) ?>">
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
        <nav class="pagination" aria-label="Messages pagination">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="?page=<?= esc((string) $i) ?>" class="<?= $i === $pagination['page'] ? 'active' : '' ?>" aria-current="<?= $i === $pagination['page'] ? 'page' : 'false' ?>"><?= esc((string) $i) ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
