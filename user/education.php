<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

require_role('user');
verify_csrf();

$uid = current_user_id();

/*
|--------------------------------------------------------------------------
| Delete Education
|--------------------------------------------------------------------------
*/
if (isset($_GET['delete'])) {
    db()->prepare(
        'DELETE FROM education WHERE id=? AND user_id=?'
    )->execute([
        (int)$_GET['delete'],
        $uid
    ]);

    flash('success', 'Education deleted.');
    redirect('user/education.php');
}

/*
|--------------------------------------------------------------------------
| Add / Update Education
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = (int)($_POST['id'] ?? 0);

        $institution = trim($_POST['institution'] ?? '');
        $degree = trim($_POST['degree'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $startDate = $_POST['start_date'] ?: null;
        $isCurrent = isset($_POST['is_current']) ? 1 : 0;
        $endDate = $isCurrent
            ? null
            : ($_POST['end_date'] ?: null);
        $institutionUrl = trim($_POST['institution_url'] ?? '');
        $isPublic = isset($_POST['is_public']) ? 1 : 0;

        if ($id) {
            $stmt = db()->prepare('
                UPDATE education SET
                    institution=?,
                    degree=?,
                    description=?,
                    start_date=?,
                    end_date=?,
                    is_current=?,
                    institution_url=?,
                    is_public=?
                WHERE id=? AND user_id=?
            ');

            $stmt->execute([
                $institution,
                $degree,
                $description,
                $startDate,
                $endDate,
                $isCurrent,
                $institutionUrl,
                $isPublic,
                $id,
                $uid
            ]);

            flash('success', 'Education updated.');
        } else {
            $stmt = db()->prepare('
                INSERT INTO education(
                    user_id,
                    institution,
                    degree,
                    description,
                    start_date,
                    end_date,
                    is_current,
                    institution_url,
                    is_public
                )
                VALUES(?,?,?,?,?,?,?,?,?)
            ');

            $stmt->execute([
                $uid,
                $institution,
                $degree,
                $description,
                $startDate,
                $endDate,
                $isCurrent,
                $institutionUrl,
                $isPublic
            ]);

            flash('success', 'Education added.');
        }
    } catch (Throwable $e) {
        flash('danger', $e->getMessage());
    }

    redirect('user/education.php');
}

/*
|--------------------------------------------------------------------------
| Edit Education
|--------------------------------------------------------------------------
*/
$edit = null;

if (isset($_GET['edit'])) {
    $stmt = db()->prepare(
        'SELECT * FROM education WHERE id=? AND user_id=?'
    );

    $stmt->execute([
        (int)$_GET['edit'],
        $uid
    ]);

    $edit = $stmt->fetch();
}

/*
|--------------------------------------------------------------------------
| Get Education
|--------------------------------------------------------------------------
*/
$rows = db()->prepare('
    SELECT *
    FROM education
    WHERE user_id=?
    ORDER BY
        is_current DESC,
        start_date DESC
');

$rows->execute([$uid]);
$rows = $rows->fetchAll();

$pageTitle = 'Education';

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="d-flex">

    <?php require dirname(__DIR__) . '/includes/sidebar.php'; ?>

    <main class="main-panel">

        <div class="content-wrapper dashboard-main">

            <!-- PAGE HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h2 class="fw-bold mb-1">
                        Educational Background
                    </h2>

                    <p class="text-muted mb-0">
                        Add your schools, degrees, and educational achievements.
                    </p>
                </div>

                <!-- <a
                    class="btn btn-outline-secondary"
                    href="<?= asset('user/education.php') ?>">
                    <i class="bi bi-plus-lg me-1"></i>
                    New Education
                </a> -->

            </div>


            <!-- TWO COLUMN LAYOUT -->
            <div class="row g-4 align-items-start">

                <!-- =====================================================
                     LEFT: EDUCATION FORM
                ====================================================== -->
                <div class="col-lg-7">

                    <div class="card card-soft p-4">

                        <div class="d-flex justify-content-between align-items-center mb-4">

                            <div>

                                <h5 class="mb-1">
                                    <?= $edit
                                        ? 'Edit Education'
                                        : 'Add Educational Background' ?>
                                </h5>

                                <p class="text-muted small mb-0">
                                    <?= $edit
                                        ? 'Update your educational information.'
                                        : 'It is okay to add more than one school or degree.' ?>
                                </p>

                            </div>

                            <?php if ($edit): ?>

                                <a
                                    href="<?= asset('user/education.php') ?>"
                                    class="btn btn-sm btn-outline-secondary">
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
                                ?>


                            <div class="row g-3">

                                <!-- INSTITUTION -->
                                <div class="col-md-7">

                                    <label class="form-label">
                                        Institution
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            <i class="bi bi-building"></i>
                                        </span>

                                        <input
                                            required
                                            class="form-control"
                                            name="institution"
                                            value="<?= e($edit['institution'] ?? '') ?>"
                                            placeholder="e.g. Ateneo de Naga University">

                                    </div>

                                </div>


                                <!-- DEGREE -->
                                <div class="col-md-5">

                                    <label class="form-label">
                                        Degree
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            <i class="bi bi-mortarboard"></i>
                                        </span>

                                        <input
                                            class="form-control"
                                            name="degree"
                                            value="<?= e($edit['degree'] ?? '') ?>"
                                            placeholder="e.g. BS Information Technology">

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
                                        placeholder="Add relevant details, achievements, activities, honors, or specialization..."><?= e($edit['description'] ?? '') ?></textarea>

                                </div>


                                <!-- START DATE -->
                                <div class="col-md-4">

                                    <label class="form-label">
                                        Start Date
                                    </label>

                                    <input
                                        type="date"
                                        class="form-control"
                                        name="start_date"
                                        value="<?= e($edit['start_date'] ?? '') ?>">

                                </div>


                                <!-- END DATE -->
                                <div class="col-md-4">

                                    <label class="form-label">
                                        End Date
                                    </label>

                                    <input
                                        id="endDate"
                                        type="date"
                                        class="form-control"
                                        name="end_date"
                                        value="<?= e($edit['end_date'] ?? '') ?>"
                                        <?= !empty($edit['is_current']) ? 'disabled' : '' ?>>

                                </div>


                                <!-- INSTITUTION URL -->
                                <div class="col-md-4">

                                    <label class="form-label">
                                        Institution URL
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            <i class="bi bi-globe2"></i>
                                        </span>

                                        <input
                                            type="url"
                                            class="form-control"
                                            name="institution_url"
                                            value="<?= e($edit['institution_url'] ?? '') ?>"
                                            placeholder="https://...">

                                    </div>

                                </div>


                                <!-- CURRENT + PUBLIC -->
                                <div class="col-12">

                                    <div class="education-options">

                                        <label class="education-option">

                                            <input
                                                type="checkbox"
                                                name="is_current"
                                                id="isCurrent"
                                                <?= !empty($edit['is_current']) ? 'checked' : '' ?>>

                                            <span class="education-option-content">

                                                <span class="education-option-icon">
                                                    <i class="bi bi-book"></i>
                                                </span>

                                                <span>

                                                
                                                    <strong>
                                                        Currently Studying
                                                    </strong>

                                                    <small>
                                                        Select this if you are still enrolled.
                                                    </small>

                                                </span>

                                            </span>

                                        </label>


                                        <label class="education-public-option">

                                            <input
                                                type="checkbox"
                                                name="is_public"
                                                <?= !isset($edit['is_public']) || $edit['is_public'] ? 'checked' : '' ?>>

                                            <span>
                                                <i class="bi bi-eye me-1"></i>
                                                Public
                                            </span>

                                        </label>

                                    </div>

                                </div>

                            </div>


                            <!-- SUBMIT -->
                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">

                                <?php if ($edit): ?>

                                    <a
                                        href="<?= asset('user/education.php') ?>"
                                        class="btn btn-light">
                                        Cancel
                                    </a>

                                <?php endif; ?>

                                <button class="btn btn-purple px-4">

                                    <i
                                        class="bi <?= $edit ? 'bi-check-lg' : 'bi-plus-lg' ?> me-1"></i>

                                    <?= $edit
                                        ? 'Update Education'
                                        : 'Add Education' ?>

                                </button>

                            </div>

                        </form>

                    </div>

                </div>


                <!-- =====================================================
                     RIGHT: EDUCATION LIST
                ====================================================== -->
                <div class="col-lg-5">

                    <div class="education-panel">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div>

                                <h5 class="mb-1">
                                    My Education
                                </h5>

                                <p class="text-muted small mb-0">

                                    <?= count($rows) ?>

                                    <?= count($rows) === 1
                                        ? 'education record'
                                        : 'education records' ?>

                                </p>

                            </div>


                            <div class="education-count">
                                <?= count($rows) ?>
                            </div>

                        </div>


                        <?php if (!$rows): ?>

                            <div class="card card-soft empty-education text-center p-4">

                                <div class="empty-education-icon">
                                    <i class="bi bi-mortarboard"></i>
                                </div>

                                <h6 class="mt-3">
                                    No education added yet
                                </h6>

                                <p class="text-muted small mb-0">
                                    Add your first school or degree using the form.
                                </p>

                            </div>

                        <?php else: ?>

                            <div class="education-list">

                                <?php foreach ($rows as $r): ?>

                                    <div class="education-list-card">

                                        <div class="education-icon">

                                            <i class="bi bi-mortarboard-fill"></i>

                                        </div>


                                        <div class="education-list-content">

                                            <div class="d-flex justify-content-between align-items-start gap-2">

                                                <div class="min-w-0">

                                                    <span class="education-status-badge">

                                                        <?php if (!empty($r['is_current'])): ?>

                                                            <i class="bi bi-circle-fill me-1"></i>
                                                            Currently Studying

                                                        <?php else: ?>

                                                            <i class="bi bi-check-circle me-1"></i>
                                                            Completed

                                                        <?php endif; ?>

                                                    </span>


                                                    <h6 class="education-list-title">

                                                        <?= e($r['degree'] ?: 'Education') ?>

                                                    </h6>


                                                    <div class="education-institution">

                                                        <i class="bi bi-building me-1"></i>

                                                        <?= e($r['institution']) ?>

                                                    </div>

                                                </div>

                                            </div>


                                            <div class="education-list-meta">

                                                <?php if ($r['start_date']): ?>

                                                    <span>

                                                        <i class="bi bi-calendar3"></i>

                                                        <?= date(
                                                            'M Y',
                                                            strtotime($r['start_date'])
                                                        ) ?>

                                                        –

                                                        <?php if (!empty($r['is_current'])): ?>

                                                            Present

                                                        <?php elseif ($r['end_date']): ?>

                                                            <?= date(
                                                                'M Y',
                                                                strtotime($r['end_date'])
                                                            ) ?>

                                                        <?php else: ?>

                                                            —

                                                        <?php endif; ?>

                                                    </span>

                                                <?php endif; ?>

                                            </div>


                                            <?php if (!empty($r['description'])): ?>

                                                <p class="education-list-description">

                                                    <?= e($r['description']) ?>

                                                </p>

                                            <?php endif; ?>


                                            <div class="education-list-actions">

                                                <a
                                                    class="btn btn-sm btn-outline-secondary"
                                                    href="?edit=<?= $r['id'] ?>">
                                                    <i class="bi bi-pencil me-1"></i>
                                                    Edit
                                                </a>


                                                <?php if (!empty($r['institution_url'])): ?>

                                                    <a
                                                        class="btn btn-sm btn-outline-secondary"
                                                        href="<?= e($r['institution_url']) ?>"
                                                        target="_blank"
                                                        rel="noopener noreferrer">
                                                        <i class="bi bi-box-arrow-up-right me-1"></i>
                                                        Website
                                                    </a>

                                                <?php endif; ?>


                                                <a
                                                    data-confirm="Delete this education?"
                                                    class="btn btn-sm btn-outline-danger"
                                                    href="?delete=<?= $r['id'] ?>">
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
            document.getElementById('isCurrent');

        const endDate =
            document.getElementById('endDate');

        const syncCurrentEducation = () => {

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
                syncCurrentEducation
            );
        }

        syncCurrentEducation();

    });
</script>


<?php require dirname(__DIR__) . '/includes/footer.php'; ?>