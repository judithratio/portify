<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_role('admin');
$total = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$activeQuery = (int)db()->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND account_status = 'active'")->fetchColumn();
$public = (int)db()->query("SELECT COUNT(*) FROM profiles p JOIN users u ON u.id=p.user_id WHERE u.role='user' AND p.portfolio_public=1")->fetchColumn();
$pageTitle = 'Admin Dashboard';

$users = db()->query("SELECT u.*,p.full_name,p.portfolio_public FROM users u LEFT JOIN profiles p ON p.user_id=u.id ORDER BY u.created_at DESC")->fetchAll();

require dirname(__DIR__) . '/includes/header.php';
?>
<?php require dirname(__DIR__) . '/includes/sidebar.php'; ?>
<div class="main-panel">
    <div class="content-wrapper dashboard-main">
        <h2 class="fw-bold">Admin Dashboard</h2>
        <p class="text-muted">Manage Portify user accounts.</p>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card card-soft p-4">
                    <i class="bi bi-people-fill text-purple fs-4"></i>
                    <div class="text-muted mt-2">Total Users</div>
                    <div class="stat-number"><?= $total ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft p-4">
                    <i class="bi bi-person-check-fill text-purple fs-4"></i>
                    <div class="text-muted mt-2">Active Users</div>
                    <div class="stat-number"><?= $activeQuery ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft p-4">
                    <i class="bi bi-globe2 text-purple fs-4"></i>
                    <div class="text-muted mt-2">Public Portfolios</div>
                    <div class="stat-number"><?= $public ?></div>
                </div>
            </div>
        </div>
        <div class="card card-soft p-4 mt-4">
            <div class="d-flex justify-content-between">
                <h5>Users</h5><a class="btn btn-purple" href="<?= asset('admin/users.php?add=1') ?>">Add User</a>
            </div>
            <p class="text-muted">Use the Users page to add, edit, activate or delete accounts.</p>

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
                                <th>Date Registered</th>
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
                                    <td><?= $u['created_at'] ? date('M d, Y', strtotime($u['created_at'])) : '—' ?></td>
                                </tr><?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>