<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_role('user');
$userId = current_user_id();
$profile = get_profile($userId);
$pageTitle = 'Dashboard';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="d-flex">
    <?php require dirname(__DIR__) . '/includes/sidebar.php'; ?>
    <main class="main-panel">
        <div class="content-wrapper dashboard-main">
            <h2 class="fw-bold">Welcome, <?= e($profile['full_name'] ?: 'User') ?>!</h2>
            <p class="text-muted">Manage your professional portfolio from one place.</p>
            <?php
            $fields = ['full_name', 'professional_title', 'bio', 'professional_summary', 'profile_image'];
            $complete = 0;
            foreach ($fields as $f) if (!empty($profile[$f])) $complete++;
            $complete += count_table('projects', $userId) > 0 ? 1 : 0;
            $complete += count_table('education', $userId) > 0 ? 1 : 0;
            $complete += count_table('experience', $userId) > 0 ? 1 : 0;
            $complete += count_table('skills', $userId) > 0 ? 1 : 0;
            $complete += count_table('certifications', $userId) > 0 ? 1 : 0;
            $percent = (int)round(($complete / 10) * 100);
            ?>
            <div class="card card-soft p-4 mb-4">
                <div class="d-flex justify-content-between"><strong>Profile Completion</strong><strong><?= $percent ?>%</strong></div>
                <div class="progress mt-2" style="height:9px">
                    <div class="progress-bar" style="width:<?= $percent ?>%;background:#6f42c1"></div>
                </div>
                <p class="small text-muted mt-2 mb-0">It is okay not to fill every field. Add the information you want to showcase.</p>
            </div>
            <div class="row g-3">
                <?php foreach (
                    [
                        ['projects', 'Projects', 'bi-folder'],
                        ['experience', 'Experience', 'bi-briefcase'],
                        ['education', 'Education', 'bi-mortarboard'],
                        ['skills', 'Skills', 'bi-lightning'],
                        ['certifications', 'Certifications', 'bi-award']
                    ] as $s
                ): ?>
                    <div class="col-6 col-lg">
                        <div class="card card-soft p-3 h-100"><i class="bi <?= $s[2] ?> text-purple fs-4"></i>
                            <div class="text-muted mt-2"><?= $s[1] ?></div>
                            <div class="stat-number"><?= count_table($s[0], $userId) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="card card-soft p-4 mt-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Portfolio</h5><span class="badge <?= $profile['portfolio_public'] ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $profile['portfolio_public'] ? 'Public' : 'Private' ?></span>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-secondary" href="<?= asset('user/visibility.php') ?>">Visibility</a>
                        <?php if ($profile['portfolio_public']): ?><a class="btn btn-purple" target="_blank" href="<?= asset('portfolio.php?u=' . $userId) ?>">View Portfolio</a><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>