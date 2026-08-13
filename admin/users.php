<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_role('admin');
verify_csrf();
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== current_user_id()) {
        db()->prepare("DELETE FROM users WHERE id=? AND role='user'")->execute([$id]);
        flash('success', 'User deleted.');
    }
    redirect('admin/users.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $email = strtolower(trim($_POST['email']));
    $username = generate_unique_username($_POST['full_name']);
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
    $status = $_POST['account_status'] === 'inactive' ? 'inactive' : 'active';
    try {
        if ($id) {
            db()->prepare('UPDATE users SET email=?, username=?, role=?,account_status=? WHERE id=?')->execute([$email, $username, $role, $status, $id]);
        } else {
            db()->prepare('INSERT INTO users(email,username,role,account_status) VALUES(?,?,?,?)')->execute([$email, $username, $role, $status]);
            $new = (int)db()->lastInsertId();
            ensure_profile($new);
        }
        flash('success', 'User saved.');
    } catch (Throwable $e) {
        flash('danger', 'Could not save user: ' . $e->getMessage());
    }
    redirect('admin/users.php');
}
$edit = null;
if (isset($_GET['edit'])) {
    $s = db()->prepare('SELECT * FROM users WHERE id=?');
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch();
}
$users = db()->query("SELECT u.*,p.full_name,p.portfolio_public FROM users u LEFT JOIN profiles p ON p.user_id=u.id ORDER BY u.created_at DESC")->fetchAll();
$pageTitle = 'Manage Users';
require dirname(__DIR__) . '/includes/header.php'; ?>
<?php require dirname(__DIR__) . '/includes/sidebar.php'; ?>
<div class="main-panel">
    <div class="content-wrapper dashboard-main">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2>Users</h2>
                <p class="text-muted">Add, edit or delete user accounts.</p>
            </div><a class="btn btn-purple" href="?add=1">Add User</a>
        </div>
        <?php if (isset($_GET['add']) || $edit): ?><form method="post" class="card card-soft p-4 mb-4"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Google Email</label><input required type="email" class="form-control" name="email" value="<?= e($edit['email'] ?? '') ?>"></div>
                    <div class="col-md-3"><label class="form-label">Role</label><select class="form-select" name="role">
                            <option value="user" <?= ($edit['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= ($edit['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="account_status">
                            <option value="active" <?= ($edit['account_status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($edit['account_status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select></div>
                </div><button class="btn btn-purple mt-4">Save Account</button>
            </form><?php endif; ?>
        <div class="card card-soft p-3">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Portfolio</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= e($u['full_name'] ?: '—') ?></td>
                                <td><?= e($u['email']) ?></td>
                                <td><?= e(ucfirst($u['role'])) ?></td>
                                <td><span class="badge <?= $u['account_status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e($u['account_status']) ?></span></td>
                                <td><?= $u['portfolio_public'] ? 'Public' : 'Private' ?></td>
                                <?php
                                if ($u['role'] === 'admin') {
                                ?>
                                    <td></td>
                                <?php
                                } else {
                                ?>
                                    <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="?edit=<?= $u['id'] ?>">Edit</a><?php if ($u['role'] === 'user'): ?> <a class="btn btn-sm btn-outline-danger" data-confirm="Delete this user and all portfolio data?" href="?delete=<?= $u['id'] ?>">Delete</a><?php endif; ?></td>
                                <?php
                                }
                                ?>
                            </tr><?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>