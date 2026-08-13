<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_role('user');
$pageTitle = 'Resume / CV';
require dirname(__DIR__) . '/includes/header.php'; ?>
<div class="d-flex"><?php require dirname(__DIR__) . '/includes/sidebar.php'; ?><main class="main-panel"><div class="content-wrapper dashboard-main">
        <h2>Resume / CV</h2>
        <p class="text-muted">Generate a clean, Harvard-style resume or CV directly from your portfolio data.</p>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card card-soft p-4"><i class="bi bi-file-earmark-text fs-1 text-purple"></i>
                    <h4 class="mt-3">Harvard-style Resume</h4>
                    <p>One to two pages with summary, education, experience, projects, skills and certifications.</p><a class="btn btn-purple" target="_blank" href="<?= asset('resume.php?type=resume') ?>">Generate Resume PDF</a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-soft p-4"><i class="bi bi-file-earmark-pdf fs-1 text-purple"></i>
                    <h4 class="mt-3">CV</h4>
                    <p>Expanded document containing your portfolio credentials.</p><a class="btn btn-purple" target="_blank" href="<?= asset('resume.php?type=cv') ?>">Generate CV PDF</a>
                </div>
            </div>
        </div>
    </div></main>
</div><?php require dirname(__DIR__) . '/includes/footer.php'; ?>