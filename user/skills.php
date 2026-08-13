<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

require_role('user');
verify_csrf();

$uid = current_user_id();

/*
|--------------------------------------------------------------------------
| Delete Skill
|--------------------------------------------------------------------------
*/
if (isset($_GET['delete'])) {
    db()->prepare(
        'DELETE FROM skills WHERE id=? AND user_id=?'
    )->execute([
        (int)$_GET['delete'],
        $uid
    ]);

    flash('success', 'Skill deleted.');
    redirect('user/skills.php');
}

/*
|--------------------------------------------------------------------------
| Add / Update Skill
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = (int)($_POST['id'] ?? 0);

        $skillName = trim($_POST['skill_name'] ?? '');
        $category = trim($_POST['category'] ?? '');

        $proficiency = max(
            0,
            min(100, (int)($_POST['proficiency'] ?? 0))
        );

        $isPublic = isset($_POST['is_public']) ? 1 : 0;

        if ($id) {
            $stmt = db()->prepare('
                UPDATE skills SET
                    skill_name=?,
                    category=?,
                    proficiency=?,
                    is_public=?
                WHERE id=? AND user_id=?
            ');

            $stmt->execute([
                $skillName,
                $category,
                $proficiency,
                $isPublic,
                $id,
                $uid
            ]);

            flash('success', 'Skill updated.');
        } else {
            $stmt = db()->prepare('
                INSERT INTO skills(
                    user_id,
                    skill_name,
                    category,
                    proficiency,
                    is_public
                )
                VALUES(?,?,?,?,?)
            ');

            $stmt->execute([
                $uid,
                $skillName,
                $category,
                $proficiency,
                $isPublic
            ]);

            flash('success', 'Skill added.');
        }
    } catch (Throwable $e) {
        flash('danger', $e->getMessage());
    }

    redirect('user/skills.php');
}

/*
|--------------------------------------------------------------------------
| Edit Skill
|--------------------------------------------------------------------------
*/
$edit = null;

if (isset($_GET['edit'])) {
    $stmt = db()->prepare(
        'SELECT * FROM skills WHERE id=? AND user_id=?'
    );

    $stmt->execute([
        (int)$_GET['edit'],
        $uid
    ]);

    $edit = $stmt->fetch();
}

/*
|--------------------------------------------------------------------------
| Get Skills
|--------------------------------------------------------------------------
*/
$rows = db()->prepare('
    SELECT *
    FROM skills
    WHERE user_id=?
    ORDER BY category, skill_name
');

$rows->execute([$uid]);
$rows = $rows->fetchAll();

$pageTitle = 'Skills';

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
                        Skills
                    </h2>

                    <p class="text-muted mb-0">
                        Showcase your technical, professional, and creative abilities.
                    </p>
                </div>

                <!-- <a
                    class="btn btn-outline-secondary"
                    href="<?= asset('user/skills.php') ?>">
                    <i class="bi bi-plus-lg me-1"></i>
                    New Skill
                </a> -->

            </div>


            <!-- TWO COLUMN LAYOUT -->
            <div class="row g-4 align-items-start">

                <!-- =====================================================
                     LEFT: SKILL FORM
                ====================================================== -->
                <div class="col-lg-7">

                    <div class="card card-soft p-4">

                        <div class="d-flex justify-content-between align-items-center mb-4">

                            <div>

                                <h5 class="mb-1">
                                    <?= $edit ? 'Edit Skill' : 'Add Skill' ?>
                                </h5>

                                <p class="text-muted small mb-0">
                                    <?= $edit
                                        ? 'Update your skill information.'
                                        : 'Add a skill and indicate your proficiency level.' ?>
                                </p>

                            </div>

                            <?php if ($edit): ?>

                                <a
                                    href="<?= asset('user/skills.php') ?>"
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


                            <!-- SKILL -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Skill
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        <i class="bi bi-lightning-charge"></i>
                                    </span>

                                    <input
                                        required
                                        class="form-control"
                                        name="skill_name"
                                        value="<?= e($edit['skill_name'] ?? '') ?>"
                                        placeholder="e.g. PHP, UI/UX Design, MySQL">

                                </div>

                            </div>


                            <!-- CATEGORY -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Category
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        <i class="bi bi-grid"></i>
                                    </span>

                                    <input
                                        class="form-control"
                                        name="category"
                                        value="<?= e($edit['category'] ?? '') ?>"
                                        placeholder="Programming, Web, Tools...">

                                </div>

                                <div class="form-text">
                                    Group similar skills together.
                                </div>

                            </div>


                            <!-- PROFICIENCY -->
                            <div class="mb-3">

                                <div class="d-flex justify-content-between align-items-center">

                                    <label class="form-label mb-0">
                                        Proficiency
                                    </label>

                                    <span
                                        class="skill-proficiency-value"
                                        id="proficiencyValue">
                                        <?= e($edit['proficiency'] ?? 80) ?>%
                                    </span>

                                </div>


                                <input
                                    class="form-range mt-3"
                                    type="range"
                                    min="0"
                                    max="100"
                                    name="proficiency"
                                    id="proficiency"
                                    value="<?= e($edit['proficiency'] ?? 80) ?>">


                                <div class="d-flex justify-content-between proficiency-labels">

                                    <span>Beginner</span>
                                    <span>Intermediate</span>
                                    <span>Advanced</span>
                                    <span>Expert</span>

                                </div>

                            </div>


                            <!-- PUBLIC -->
                            <div class="skill-public-option mb-3">

                                <label>

                                    <input
                                        type="checkbox"
                                        name="is_public"
                                        <?= !isset($edit['is_public']) || $edit['is_public'] ? 'checked' : '' ?>>

                                    <span class="skill-public-icon">
                                        <i class="bi bi-eye"></i>
                                    </span>

                                    <span>

                                        <strong>
                                            Public Skill
                                        </strong>

                                        <small>
                                            Show this skill on your public portfolio.
                                        </small>

                                    </span>

                                </label>

                            </div>


                            <!-- SUBMIT -->
                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">

                                <?php if ($edit): ?>

                                    <a
                                        href="<?= asset('user/skills.php') ?>"
                                        class="btn btn-light">
                                        Cancel
                                    </a>

                                <?php endif; ?>

                                <button class="btn btn-purple px-4">

                                    <i
                                        class="bi <?= $edit ? 'bi-check-lg' : 'bi-plus-lg' ?> me-1"></i>

                                    <?= $edit ? 'Update Skill' : 'Add Skill' ?>

                                </button>

                            </div>

                        </form>

                    </div>

                </div>


                <!-- =====================================================
                     RIGHT: SKILL LIST
                ====================================================== -->
                <div class="col-lg-5">

                    <div class="skills-panel">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div>

                                <h5 class="mb-1">
                                    My Skills
                                </h5>

                                <p class="text-muted small mb-0">

                                    <?= count($rows) ?>

                                    <?= count($rows) === 1
                                        ? 'skill'
                                        : 'skills' ?>

                                </p>

                            </div>


                            <div class="skills-count">

                                <?= count($rows) ?>

                            </div>

                        </div>


                        <?php if (!$rows): ?>

                            <div class="card card-soft empty-skills text-center p-4">

                                <div class="empty-skills-icon">
                                    <i class="bi bi-stars"></i>
                                </div>

                                <h6 class="mt-3">
                                    No skills added yet
                                </h6>

                                <p class="text-muted small mb-0">
                                    Add your first skill using the form.
                                </p>

                            </div>

                        <?php else: ?>

                            <div class="skills-list">

                                <?php foreach ($rows as $r): ?>

                                    <div class="skill-list-card">

                                        <div class="skill-main-icon">

                                            <i class="bi bi-lightning-charge-fill"></i>

                                        </div>


                                        <div class="skill-list-content">

                                            <div class="d-flex justify-content-between align-items-start gap-3">

                                                <div class="min-w-0">

                                                    <h6 class="skill-list-title">

                                                        <?= e($r['skill_name']) ?>

                                                    </h6>


                                                    <?php if (!empty($r['category'])): ?>

                                                        <div class="skill-category">

                                                            <i class="bi bi-folder2-open me-1"></i>

                                                            <?= e($r['category']) ?>

                                                        </div>

                                                    <?php endif; ?>

                                                </div>


                                                <div class="skill-percentage">

                                                    <?= e($r['proficiency']) ?>%

                                                </div>

                                            </div>


                                            <div class="skill-progress">

                                                <div
                                                    class="skill-progress-bar"
                                                    style="width:<?= (int)$r['proficiency'] ?>%;"></div>

                                            </div>


                                            <div class="skill-list-footer">

                                                <span class="skill-level">

                                                    <?php
                                                    $level = (int)$r['proficiency'];

                                                    if ($level >= 90) {
                                                        echo 'Expert';
                                                    } elseif ($level >= 75) {
                                                        echo 'Advanced';
                                                    } elseif ($level >= 50) {
                                                        echo 'Intermediate';
                                                    } else {
                                                        echo 'Beginner';
                                                    }
                                                    ?>

                                                </span>


                                                <span class="skill-visibility">

                                                    <?php if (!empty($r['is_public'])): ?>

                                                        <i class="bi bi-eye me-1"></i>
                                                        Public

                                                    <?php else: ?>

                                                        <i class="bi bi-eye-slash me-1"></i>
                                                        Private

                                                    <?php endif; ?>

                                                </span>

                                            </div>


                                            <div class="skill-actions">

                                                <a
                                                    class="btn btn-sm btn-outline-secondary"
                                                    href="?edit=<?= $r['id'] ?>">
                                                    <i class="bi bi-pencil me-1"></i>
                                                    Edit
                                                </a>


                                                <a
                                                    data-confirm="Delete this skill?"
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

        const proficiency =
            document.getElementById('proficiency');

        const proficiencyValue =
            document.getElementById('proficiencyValue');

        if (proficiency && proficiencyValue) {

            proficiency.addEventListener('input', () => {

                proficiencyValue.textContent =
                    proficiency.value + '%';

            });

        }

    });
</script>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>