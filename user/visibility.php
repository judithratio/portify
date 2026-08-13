<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

require_role('user');
verify_csrf();

$uid = current_user_id();
$profile = get_profile($uid);
$user = get_user($uid);

$ownerEmail = $user['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db()->prepare('
        UPDATE profiles SET
            portfolio_public=?,
            show_about=?,
            show_projects=?,
            show_experience=?,
            show_education=?,
            show_skills=?,
            show_certifications=?,
            show_socials=?
        WHERE user_id=?
    ');

    $stmt->execute([
        (int)isset($_POST['portfolio_public']),
        (int)isset($_POST['show_about']),
        (int)isset($_POST['show_projects']),
        (int)isset($_POST['show_experience']),
        (int)isset($_POST['show_education']),
        (int)isset($_POST['show_skills']),
        (int)isset($_POST['show_certifications']),
        (int)isset($_POST['show_socials']),
        $uid
    ]);

    flash('success', 'Visibility settings saved.');
    redirect('user/visibility.php');
}

$sections = [
    [
        'key' => 'show_about',
        'label' => 'About Me',
        'description' => 'Your personal introduction and profile summary.',
        'icon' => 'bi-person'
    ],
    [
        'key' => 'show_projects',
        'label' => 'Projects',
        'description' => 'Showcase your projects and creative work.',
        'icon' => 'bi-folder2-open'
    ],
    [
        'key' => 'show_experience',
        'label' => 'Work Experience',
        'description' => 'Display your employment and professional experience.',
        'icon' => 'bi-briefcase'
    ],
    [
        'key' => 'show_education',
        'label' => 'Education',
        'description' => 'Display your schools, degrees, and academic background.',
        'icon' => 'bi-mortarboard'
    ],
    [
        'key' => 'show_skills',
        'label' => 'Skills',
        'description' => 'Display your technical, professional, and creative skills.',
        'icon' => 'bi-lightning'
    ],
    [
        'key' => 'show_certifications',
        'label' => 'Certifications',
        'description' => 'Show your certifications, seminars, and credentials.',
        'icon' => 'bi-award'
    ],
    [
        'key' => 'show_socials',
        'label' => 'Social Links',
        'description' => 'Display your professional and social media links.',
        'icon' => 'bi-share'
    ]
];

$visibleCount = 0;
foreach ($sections as $section) {
    if (!empty($profile[$section['key']])) {
        $visibleCount++;
    }
}

$pageTitle = 'Visibility';

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="d-flex">

    <?php require dirname(__DIR__) . '/includes/sidebar.php'; ?>

    <main class="main-panel">

        <div class="content-wrapper dashboard-main visibility-page">

            <!-- PAGE HEADER -->
            <div class="visibility-header mb-4">

                <div>
                    <h2 class="fw-bold mb-1">Portfolio Visibility</h2>

                    <p class="text-muted mb-0">
                        Control what visitors can see on your public Portify portfolio.
                    </p>
                </div>

                <a
                    href="<?= asset('portfolio.php?u=' . $uid) ?>"
                    target="_blank"
                    class="btn btn-purple">
                    <i class="bi bi-box-arrow-up-right me-1"></i>
                    View Website
                </a>

            </div>


            <form method="post">

                <?= csrf_field() ?>

                <div class="row g-4 align-items-start">

                    <!-- =====================================================
                         LEFT: VISIBILITY SETTINGS
                    ====================================================== -->
                    <div class="col-lg-7">

                        <!-- PUBLIC PORTFOLIO -->
                        <div class="visibility-public-card mb-4">

                            <div class="visibility-public-icon">
                                <i class="bi bi-globe2"></i>
                            </div>

                            <div class="visibility-public-content">

                                <div class="d-flex justify-content-between align-items-start gap-3">

                                    <div>

                                        <span class="visibility-eyebrow">
                                            PORTFOLIO STATUS
                                        </span>

                                        <h5 class="mb-1 mt-1">
                                            Make my portfolio public
                                        </h5>

                                        <p class="text-muted small mb-0">
                                            Anyone with your portfolio link can view your public profile.
                                        </p>

                                    </div>

                                    <div class="form-check form-switch visibility-switch">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            role="switch"
                                            name="portfolio_public"
                                            id="portfolio_public"
                                            <?= !empty($profile['portfolio_public']) ? 'checked' : '' ?>>
                                    </div>

                                </div>

                                <div class="visibility-status-row mt-3">

                                    <span
                                        id="portfolioStatusBadge"
                                        class="visibility-status-badge <?= !empty($profile['portfolio_public']) ? 'is-public' : 'is-private' ?>">
                                        <i class="bi <?= !empty($profile['portfolio_public']) ? 'bi-eye-fill' : 'bi-eye-slash-fill' ?>"></i>

                                        <span id="portfolioStatusText">
                                            <?= !empty($profile['portfolio_public']) ? 'Public portfolio' : 'Private portfolio' ?>
                                        </span>
                                    </span>

                                    <span class="text-muted small">
                                        You can change this anytime.
                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- SECTIONS -->
                        <div class="visibility-section-card">

                            <div class="visibility-section-header">

                                <div>

                                    <h5 class="mb-1">
                                        Public Sections
                                    </h5>

                                    <p class="text-muted small mb-0">
                                        Choose which parts of your portfolio are visible.
                                    </p>

                                </div>

                                <span class="visibility-count">
                                    <span id="visibleCount"><?= $visibleCount ?></span>/<?= count($sections) ?>
                                </span>

                            </div>


                            <div class="visibility-list">

                                <?php foreach ($sections as $section): ?>

                                    <div class="visibility-item">

                                        <div class="visibility-item-icon">
                                            <i class="bi <?= e($section['icon']) ?>"></i>
                                        </div>

                                        <div class="visibility-item-content">

                                            <label
                                                class="visibility-item-title"
                                                for="<?= e($section['key']) ?>">
                                                <?= e($section['label']) ?>
                                            </label>

                                            <div class="visibility-item-description">
                                                <?= e($section['description']) ?>
                                            </div>

                                        </div>

                                        <div class="form-check form-switch visibility-item-switch">

                                            <input
                                                class="form-check-input section-toggle"
                                                type="checkbox"
                                                role="switch"
                                                name="<?= e($section['key']) ?>"
                                                id="<?= e($section['key']) ?>"
                                                <?= !empty($profile[$section['key']]) ? 'checked' : '' ?>>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            </div>


                            <div class="visibility-note">

                                <div class="visibility-note-icon">
                                    <i class="bi bi-info-circle"></i>
                                </div>

                                <div>
                                    <strong>About individual items</strong>

                                    <p class="mb-0">
                                        Projects, skills, experience, education, and certifications
                                        also have their own Public/Private setting. An item must be
                                        public at both levels to appear on your portfolio.
                                    </p>
                                </div>

                            </div>


                            <div class="visibility-form-footer">

                                <button class="btn btn-purple px-4">

                                    <i class="bi bi-check2 me-1"></i>
                                    Save Visibility

                                </button>

                            </div>

                        </div>

                    </div>


                    <!-- =====================================================
                         RIGHT: PREVIEW
                    ====================================================== -->
                    <div class="col-lg-5">

                        <div class="visibility-preview-card">

                            <div class="visibility-preview-header">

                                <div>

                                    <span class="visibility-eyebrow">
                                        LIVE PREVIEW
                                    </span>

                                    <h5 class="mb-1 mt-1">
                                        Portfolio Visibility
                                    </h5>

                                    <p class="text-muted small mb-0">
                                        A quick overview of what visitors can see.
                                    </p>

                                </div>

                                <div class="visibility-preview-icon">
                                    <i class="bi bi-eye"></i>
                                </div>

                            </div>


                            <div class="preview-portfolio-status">

                                <div class="preview-status-icon">
                                    <i
                                        id="previewGlobeIcon"
                                        class="bi <?= !empty($profile['portfolio_public']) ? 'bi-globe2' : 'bi-lock' ?>"></i>
                                </div>

                                <div>

                                    <strong id="previewPortfolioText">
                                        <?= !empty($profile['portfolio_public']) ? 'Portfolio is public' : 'Portfolio is private' ?>
                                    </strong>

                                    <span id="previewPortfolioDescription">
                                        <?= !empty($profile['portfolio_public'])
                                            ? 'Visitors can access your public portfolio.'
                                            : 'Visitors cannot access your public portfolio.' ?>
                                    </span>

                                </div>

                            </div>


                            <div class="preview-divider"></div>


                            <div class="preview-section-title">
                                Visible sections
                            </div>


                            <div class="preview-section-list">

                                <?php foreach ($sections as $section): ?>

                                    <div
                                        class="preview-section-row <?= !empty($profile[$section['key']]) ? 'visible' : 'hidden' ?>"
                                        data-preview="<?= e($section['key']) ?>">

                                        <div class="preview-section-left">

                                            <span class="preview-section-icon">
                                                <i class="bi <?= e($section['icon']) ?>"></i>
                                            </span>

                                            <span>
                                                <?= e($section['label']) ?>
                                            </span>

                                        </div>

                                        <span class="preview-section-state">

                                            <i class="bi <?= !empty($profile[$section['key']]) ? 'bi-eye-fill' : 'bi-eye-slash' ?>"></i>

                                            <span>
                                                <?= !empty($profile[$section['key']]) ? 'Visible' : 'Hidden' ?>
                                            </span>

                                        </span>

                                    </div>

                                <?php endforeach; ?>

                            </div>


                            <div class="preview-tip">

                                <i class="bi bi-lightbulb"></i>

                                <span>
                                    Keep your portfolio public and highlight your strongest
                                    sections when you're ready to share your work.
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </main>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const portfolioToggle = document.getElementById('portfolio_public');
        const statusBadge = document.getElementById('portfolioStatusBadge');
        const statusText = document.getElementById('portfolioStatusText');

        const previewGlobeIcon = document.getElementById('previewGlobeIcon');
        const previewPortfolioText = document.getElementById('previewPortfolioText');
        const previewPortfolioDescription = document.getElementById('previewPortfolioDescription');

        const sectionToggles = document.querySelectorAll('.section-toggle');
        const visibleCount = document.getElementById('visibleCount');


        function updatePortfolioStatus() {

            if (!portfolioToggle) return;

            const isPublic = portfolioToggle.checked;

            if (isPublic) {

                statusBadge.classList.remove('is-private');
                statusBadge.classList.add('is-public');

                statusBadge.innerHTML =
                    '<i class="bi bi-eye-fill"></i>' +
                    '<span id="portfolioStatusText">Public portfolio</span>';

                previewGlobeIcon.className = 'bi bi-globe2';

                previewPortfolioText.textContent =
                    'Portfolio is public';

                previewPortfolioDescription.textContent =
                    'Visitors can access your public portfolio.';

            } else {

                statusBadge.classList.remove('is-public');
                statusBadge.classList.add('is-private');

                statusBadge.innerHTML =
                    '<i class="bi bi-eye-slash-fill"></i>' +
                    '<span id="portfolioStatusText">Private portfolio</span>';

                previewGlobeIcon.className = 'bi bi-lock';

                previewPortfolioText.textContent =
                    'Portfolio is private';

                previewPortfolioDescription.textContent =
                    'Visitors cannot access your public portfolio.';

            }

        }


        function updateSectionPreview() {

            let count = 0;

            sectionToggles.forEach(function(toggle) {

                if (toggle.checked) {
                    count++;
                }

                const row = document.querySelector(
                    '[data-preview="' + toggle.name + '"]'
                );

                if (!row) return;

                const state = row.querySelector('.preview-section-state');

                if (toggle.checked) {

                    row.classList.remove('hidden');
                    row.classList.add('visible');

                    if (state) {
                        state.innerHTML =
                            '<i class="bi bi-eye-fill"></i>' +
                            '<span>Visible</span>';
                    }

                } else {

                    row.classList.remove('visible');
                    row.classList.add('hidden');

                    if (state) {
                        state.innerHTML =
                            '<i class="bi bi-eye-slash"></i>' +
                            '<span>Hidden</span>';
                    }

                }

            });

            visibleCount.textContent = count;

        }


        if (portfolioToggle) {
            portfolioToggle.addEventListener(
                'change',
                updatePortfolioStatus
            );
        }

        sectionToggles.forEach(function(toggle) {
            toggle.addEventListener(
                'change',
                updateSectionPreview
            );
        });


        updatePortfolioStatus();
        updateSectionPreview();

    });
</script>


<?php require dirname(__DIR__) . '/includes/footer.php'; ?>