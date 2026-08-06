<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

require_admin_auth();

$pdo = get_pdo();
$currentUser = current_user();

$action = $_GET['action'] ?? '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_fail();
    
    $target_id = (int) ($_POST['id'] ?? 0);
    
    if ($target_id <= 0) {
        set_flash('error', 'Invalid user target.');
        redirect('/admin/users.php');
    }
    
    // Prevent actions on oneself
    if ($target_id === $currentUser['id']) {
        set_flash('error', 'You cannot perform actions on your own active session.');
        redirect('/admin/users.php');
    }
    
    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $target_id]);
        set_flash('success', 'User account successfully deleted.');
        redirect('/admin/users.php');
        
    } elseif ($action === 'change_role') {
        $new_role = $_POST['role'] ?? '';
        if (!in_array($new_role, ['admin', 'editor'], true)) {
            set_flash('error', 'Invalid role selection.');
            redirect('/admin/users.php');
        }
        
        $stmt = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
        $stmt->execute(['role' => $new_role, 'id' => $target_id]);
        set_flash('success', 'User role updated successfully.');
        redirect('/admin/users.php');
    }
}

// Fetch all registered users
$usersStmt = $pdo->query('SELECT id, username, role, created_at FROM users ORDER BY created_at DESC');
$allUsers = $usersStmt->fetchAll();

$pageTitle = 'Manage Accounts';
require __DIR__ . '/partials/header.php';
?>

<section class="admin-panel" aria-labelledby="accounts-heading">
    <div class="admin-panel__header">
        <h1 id="accounts-heading" style="font-family: 'Cinzel', serif; color: var(--navy);">User Accounts Management</h1>
        <p style="color: var(--muted); margin: 0;">Total Registered Accounts: <strong><?= count($allUsers) ?></strong></p>
    </div>

    <div class="table-wrap" style="margin-top: 1.5rem;">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Date Created</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allUsers as $user_row): ?>
                    <tr style="<?= $user_row['id'] === $currentUser['id'] ? 'background: rgba(184, 138, 68, 0.05);' : '' ?>">
                        <td style="vertical-align: middle;">
                            <strong><?= esc($user_row['username']) ?></strong>
                            <?php if ($user_row['id'] === $currentUser['id']): ?>
                                <span class="badge badge-read" style="margin-left: 0.5rem; font-size: 0.7rem; font-weight: bold; background: var(--gold); color: #fff;">You</span>
                            <?php endif; ?>
                        </td>
                        <td style="vertical-align: middle;">
                            <?php
                            $badgeClass = $user_row['role'] === 'admin' ? 'badge-read' : 'badge-new';
                            ?>
                            <span class="badge <?= $badgeClass ?>" style="font-weight: bold;">
                                <?= esc($user_row['role'] === 'editor' ? 'User' : 'Admin') ?>
                            </span>
                        </td>
                        <td style="vertical-align: middle;">
                            <?= esc(date('M j, Y g:i A', strtotime($user_row['created_at']))) ?>
                        </td>
                        <td style="vertical-align: middle; text-align: center;">
                            <?php if ($user_row['id'] !== $currentUser['id']): ?>
                                <div style="display: flex; gap: 0.5rem; justify-content: center; align-items: center; flex-wrap: wrap;">
                                    <!-- Change Role Form -->
                                    <form method="post" action="/admin/users.php?action=change_role" style="display: inline-block; margin: 0;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= esc((string) $user_row['id']) ?>">
                                        <select name="role" onchange="this.form.submit()" style="padding: 0.25rem 0.5rem; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem;">
                                            <option value="editor" <?= $user_row['role'] === 'editor' ? 'selected' : '' ?>>User</option>
                                            <option value="admin" <?= $user_row['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </form>

                                    <!-- Delete Account Form -->
                                    <form method="post" action="/admin/users.php?action=delete" onsubmit="return confirm('Are you sure you want to permanently delete the user account \'<?= esc($user_row['username']) ?>\'? This action cannot be undone.');" style="display: inline-block; margin: 0;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= esc((string) $user_row['id']) ?>">
                                        <button type="submit" class="btn" style="padding: 0.25rem 0.75rem; background: #dc3545; color: white; font-size: 0.8rem; border-radius: 6px; border: none; cursor: pointer; font-family: inherit;">Delete</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span style="color: var(--muted); font-size: 0.9rem; font-style: italic;">No actions available</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
