<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

require_role('user');
verify_csrf();

$userId = current_user_id();

/*
|--------------------------------------------------------------------------
| Delete Experience
|--------------------------------------------------------------------------
*/
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = db()->prepare(
        'DELETE FROM experience WHERE id=? AND user_id=?'
    );

    $stmt->execute([$id, $userId]);

    flash('success', 'Work experience deleted.');
    redirect('user/experience.php');
}

/*
|--------------------------------------------------------------------------
| Add / Update Experience
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = (int) ($_POST['id'] ?? 0);

        $company = trim($_POST['company'] ?? '');
        $job_title = trim($_POST['job_title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $companyUrl = trim($_POST['company_url'] ?? '');
        $startDate = $_POST['start_date'] ?: null;
        $endDate = !empty($_POST['is_current'])
            ? null
            : ($_POST['end_date'] ?: null);
        $isCurrent = isset($_POST['is_current']) ? 1 : 0;

        if ($id) {
            $stmt = db()->prepare('
                UPDATE experience SET
                    company=?,
                    job_title=?,
                    description=?,
                    location=?,
                    company_url=?,
                    start_date=?,
                    end_date=?,
                    is_current=?
                WHERE id=? AND user_id=?
            ');

            $stmt->execute([
                $company,
                $job_title,
                $description,
                $location,
                $companyUrl,
                $startDate,
                $endDate,
                $isCurrent,
                $id,
                $userId
            ]);

            flash('success', 'Work experience updated.');
        } else {
            $stmt = db()->prepare('
                INSERT INTO experience(
                    user_id,
                    company,
                    job_title,
                    description,
                    location,
                    company_url,
                    start_date,
                    end_date,
                    is_current
                )
                VALUES(?,?,?,?,?,?,?,?,?)
            ');

            $stmt->execute([
                $userId,
                $company,
                $job_title,
                $description,
                $location,
                $companyUrl,
                $startDate,
                $endDate,
                $isCurrent
            ]);

            flash('success', 'Work experience added.');
        }
    } catch (Throwable $e) {
        flash('danger', $e->getMessage());
    }

    redirect('user/experience.php');
}

/*
|--------------------------------------------------------------------------
| Edit Experience
|--------------------------------------------------------------------------
*/
$edit = null;

if (isset($_GET['edit'])) {
    $stmt = db()->prepare(
        'SELECT * FROM experience WHERE id=? AND user_id=?'
    );

    $stmt->execute([
        (int) $_GET['edit'],
        $userId
    ]);

    $edit = $stmt->fetch();
}

/*
|--------------------------------------------------------------------------
| Get Experiences
|--------------------------------------------------------------------------
*/
$stmt = db()->prepare('
    SELECT *
    FROM experience
    WHERE user_id=?
    ORDER BY
        is_current DESC,
        start_date DESC,
        created_at DESC
');

$stmt->execute([$userId]);

$experiences = $stmt->fetchAll();

$pageTitle = 'Experience';

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="d-flex">

    <?php require dirname(__DIR__) . '/includes/sidebar.php'; ?>

    <main class="main-panel">

        <div class="content-wrapper dashboard-main">

            <!-- PAGE HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h2 class="fw-bold mb-1">Work Experience</h2>

                    <p class="text-muted mb-0">
                        Add your professional experience and career history.
                    </p>
                </div>

                <!-- <a
                    class="btn btn-outline-secondary"
                    href="<?= asset('user/experience.php') ?>"
                >
                    <i class="bi bi-plus-lg me-1"></i>
                    New Experience
                </a> -->

            </div>


            <!-- TWO COLUMN LAYOUT -->
            <div class="row g-4 align-items-start">

                <!-- =====================================================
                     LEFT: EXPERIENCE FORM
                ====================================================== -->
                <div class="col-lg-7">

                    <div class="card card-soft p-4">

                        <div class="d-flex justify-content-between align-items-center mb-4">

                            <div>

                                <h5 class="mb-1">
                                    <?= $edit ? 'Edit Experience' : 'Add Work Experience' ?>
                                </h5>

                                <p class="text-muted small mb-0">
                                    <?= $edit
                                        ? 'Update your professional experience.'
                                        : 'Add a position, company, and details about your work.' ?>
                                </p>

                            </div>

                            <?php if ($edit): ?>

                                <a
                                    href="<?= asset('user/experience.php') ?>"
                                    class="btn btn-sm btn-outline-secondary"
                                >
                                    <i class="bi bi-x-lg me-1"></i>
                                    Cancel
                                </a>

                            <?php endif; ?>

                        </div>


                        <form method="post">

                            <?= csrf_field() ?>

                            <input
                                type="hidden"
                                name="id"
                                value="<?= e($edit['id'] ?? '') ?>"
                            >


                            <div class="row g-3">

                                <!-- POSITION -->
                                <div class="col-md-7">

                                    <label class="form-label">
                                        Job Title / Position
                                    </label>

                                    <input
                                        required
                                        class="form-control"
                                        name="job_title"
                                        value="<?= e($edit['job_title'] ?? '') ?>"
                                        placeholder="e.g. Web Developer Intern"
                                    >

                                </div>


                                <!-- COMPANY -->
                                <div class="col-md-5">

                                    <label class="form-label">
                                        Company / Organization
                                    </label>

                                    <input
                                        required
                                        class="form-control"
                                        name="company"
                                        value="<?= e($edit['company'] ?? '') ?>"
                                        placeholder="e.g. ABC Company"
                                    >

                                </div>


                                <!-- LOCATION -->
                                <div class="col-md-6">

                                    <label class="form-label">
                                        Location
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            <i class="bi bi-geo-alt"></i>
                                        </span>

                                        <input
                                            class="form-control"
                                            name="location"
                                            value="<?= e($edit['location'] ?? '') ?>"
                                            placeholder="e.g. Naga City, Philippines"
                                        >

                                    </div>

                                </div>


                                <!-- COMPANY URL -->
                                <div class="col-md-6">

                                    <label class="form-label">
                                        Company Website
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            <i class="bi bi-globe2"></i>
                                        </span>

                                        <input
                                            type="url"
                                            class="form-control"
                                            name="company_url"
                                            value="<?= e($edit['company_url'] ?? '') ?>"
                                            placeholder="https://..."
                                        >

                                    </div>

                                </div>


                                <!-- DESCRIPTION -->
                                <div class="col-12">

                                    <label class="form-label">
                                        Description
                                    </label>

                                    <textarea
                                        class="form-control"
                                        name="description"
                                        rows="5"
                                        placeholder="Describe your responsibilities, achievements, and contributions..."
                                    ><?= e($edit['description'] ?? '') ?></textarea>

                                </div>


                                <!-- START DATE -->
                                <div class="col-md-6">

                                    <label class="form-label">
                                        Start Date
                                    </label>

                                    <input
                                        required
                                        type="date"
                                        class="form-control"
                                        name="start_date"
                                        value="<?= e($edit['start_date'] ?? '') ?>"
                                    >

                                </div>


                                <!-- END DATE -->
                                <div class="col-md-6">

                                    <label class="form-label">
                                        End Date
                                    </label>

                                    <input
                                        type="date"
                                        class="form-control"
                                        name="end_date"
                                        value="<?= e($edit['end_date'] ?? '') ?>"
                                        <?= !empty($edit['is_current']) ? 'disabled' : '' ?>
                                        data-end-date
                                    >

                                </div>


                                <!-- CURRENT POSITION -->
                                <div class="col-12">

                                    <label class="experience-current-option">

                                        <input
                                            type="checkbox"
                                            name="is_current"
                                            id="is_current"
                                            <?= !empty($edit['is_current']) ? 'checked' : '' ?>
                                        >

                                        <span class="experience-current-content">

                                            <span class="experience-check-icon">
                                                <i class="bi bi-briefcase-fill"></i>
                                            </span>

                                            <span>
                                                <strong>
                                                    I currently work here
                                                </strong>

                                                <small>
                                                    Leave the end date blank while this position is ongoing.
                                                </small>
                                            </span>

                                        </span>

                                    </label>

                                </div>

                            </div>


                            <!-- SUBMIT -->
                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">

                                <?php if ($edit): ?>

                                    <a
                                        href="<?= asset('user/experience.php') ?>"
                                        class="btn btn-light"
                                    >
                                        Cancel
                                    </a>

                                <?php endif; ?>

                                <button class="btn btn-purple px-4">

                                    <i
                                        class="bi <?= $edit ? 'bi-check-lg' : 'bi-plus-lg' ?> me-1"
                                    ></i>

                                    <?= $edit ? 'Update Experience' : 'Add Experience' ?>

                                </button>

                            </div>

                        </form>

                    </div>

                </div>


                <!-- =====================================================
                     RIGHT: EXPERIENCE LIST
                ====================================================== -->
                <div class="col-lg-5">

                    <div class="experiences-panel">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div>

                                <h5 class="mb-1">
                                    My Experience
                                </h5>

                                <p class="text-muted small mb-0">

                                    <?= count($experiences) ?>

                                    <?= count($experiences) === 1
                                        ? 'job_title'
                                        : 'job_titles' ?>

                                </p>

                            </div>


                            <div class="experience-count">
                                <?= count($experiences) ?>
                            </div>

                        </div>


                        <?php if (!$experiences): ?>

                            <div class="card card-soft empty-experience text-center p-4">

                                <div class="empty-experience-icon">
                                    <i class="bi bi-briefcase"></i>
                                </div>

                                <h6 class="mt-3">
                                    No experience added yet
                                </h6>

                                <p class="text-muted small mb-0">
                                    Add your first work experience using the form.
                                </p>

                            </div>

                        <?php else: ?>

                            <div class="experiences-list">

                                <?php foreach ($experiences as $experience): ?>

                                    <div class="experience-list-card">

                                        <div class="experience-icon">

                                            <i class="bi bi-briefcase-fill"></i>

                                        </div>


                                        <div class="experience-list-content">

                                            <div class="d-flex justify-content-between align-items-start gap-2">

                                                <div class="min-w-0">

                                                    <span class="experience-status-badge">

                                                        <?php if (!empty($experience['is_current'])): ?>

                                                            <i class="bi bi-circle-fill me-1"></i>
                                                            Current

                                                        <?php else: ?>

                                                            <i class="bi bi-check-circle me-1"></i>
                                                            Completed

                                                        <?php endif; ?>

                                                    </span>


                                                    <h6 class="experience-list-title">

                                                        <?= e($experience['job_title']) ?>

                                                    </h6>

                                                    <div class="experience-company">

                                                        <i class="bi bi-building me-1"></i>

                                                        <?= e($experience['company']) ?>

                                                    </div>

                                                </div>

                                            </div>


                                            <div class="experience-list-meta">

                                                <?php if ($experience['start_date']): ?>

                                                    <span>

                                                        <i class="bi bi-calendar3"></i>

                                                        <?= date(
                                                            'M Y',
                                                            strtotime($experience['start_date'])
                                                        ) ?>

                                                        –

                                                        <?php if (!empty($experience['is_current'])): ?>

                                                            Present

                                                        <?php elseif ($experience['end_date']): ?>

                                                            <?= date(
                                                                'M Y',
                                                                strtotime($experience['end_date'])
                                                            ) ?>

                                                        <?php else: ?>

                                                            —

                                                        <?php endif; ?>

                                                    </span>

                                                <?php endif; ?>


                                                <?php if (!empty($experience['location'])): ?>

                                                    <span>

                                                        <i class="bi bi-geo-alt"></i>

                                                        <?= e($experience['location']) ?>

                                                    </span>

                                                <?php endif; ?>

                                            </div>


                                            <?php if (!empty($experience['description'])): ?>

                                                <p class="experience-list-description">

                                                    <?= e($experience['description']) ?>

                                                </p>

                                            <?php endif; ?>


                                            <div class="experience-list-actions">

                                                <a
                                                    class="btn btn-sm btn-outline-secondary"
                                                    href="?edit=<?= $experience['id'] ?>"
                                                >
                                                    <i class="bi bi-pencil me-1"></i>
                                                    Edit
                                                </a>


                                                <?php if (!empty($experience['company_url'])): ?>

                                                    <a
                                                        class="btn btn-sm btn-outline-secondary"
                                                        href="<?= e($experience['company_url']) ?>"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >
                                                        <i class="bi bi-box-arrow-up-right me-1"></i>
                                                        Website
                                                    </a>

                                                <?php endif; ?>


                                                <a
                                                    data-confirm="Delete this work experience?"
                                                    class="btn btn-sm btn-outline-danger"
                                                    href="?delete=<?= $experience['id'] ?>"
                                                >
                                                    <i class="bi bi-trash3 me-1"></i>
                                                    Delete
                                                </a>

                                            </div>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </main>

</div>


<script>
document.addEventListener('DOMContentLoaded', () => {

    const currentCheckbox =
        document.getElementById('is_current');

    const endDate =
        document.querySelector('[data-end-date]');

    const syncCurrentPosition = () => {

        if (!currentCheckbox || !endDate) {
            return;
        }

        endDate.disabled = currentCheckbox.checked;

        if (currentCheckbox.checked) {
            endDate.value = '';
        }

    };

    if (currentCheckbox) {
        currentCheckbox.addEventListener(
            'change',
            syncCurrentPosition
        );
    }

    syncCurrentPosition();

});
</script>