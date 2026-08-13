<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

require_role('user');
verify_csrf();

$uid = current_user_id();

/*
|--------------------------------------------------------------------------
| Delete Certification
|--------------------------------------------------------------------------
*/
if (isset($_GET['delete'])) {
    $s = db()->prepare(
        'SELECT certificate_file FROM certifications WHERE id=? AND user_id=?'
    );
    $s->execute([
        (int)$_GET['delete'],
        $uid
    ]);

    $r = $s->fetch();

    if ($r && !empty($r['certificate_file'])) {
        delete_upload($r['certificate_file']);
    }

    db()->prepare(
        'DELETE FROM certifications WHERE id=? AND user_id=?'
    )->execute([
        (int)$_GET['delete'],
        $uid
    ]);

    flash('success', 'Certification deleted.');
    redirect('user/certifications.php');
}

/*
|--------------------------------------------------------------------------
| Add / Update Certification
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = (int)($_POST['id'] ?? 0);

        $name = trim($_POST['name'] ?? '');
        $issuingOrganization = trim($_POST['issuing_organization'] ?? '');
        $issueDate = $_POST['issue_date'] ?: null;
        $expirationDate = $_POST['expiration_date'] ?: null;
        $credentialId = trim($_POST['credential_id'] ?? '');
        $credentialUrl = trim($_POST['credential_url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isPublic = isset($_POST['is_public']) ? 1 : 0;

        $file = upload_file(
            'certificate_file',
            ['pdf'],
            'certificates'
        );

        if ($id) {

            $oldStmt = db()->prepare(
                'SELECT certificate_file FROM certifications WHERE id=? AND user_id=?'
            );

            $oldStmt->execute([
                $id,
                $uid
            ]);

            $old = $oldStmt->fetch();

            if ($file) {

                $stmt = db()->prepare('
                    UPDATE certifications SET
                        name=?,
                        issuing_organization=?,
                        issue_date=?,
                        expiration_date=?,
                        credential_id=?,
                        credential_url=?,
                        description=?,
                        certificate_file=?,
                        is_public=?
                    WHERE id=? AND user_id=?
                ');

                $stmt->execute([
                    $name,
                    $issuingOrganization,
                    $issueDate,
                    $expirationDate,
                    $credentialId,
                    $credentialUrl,
                    $description,
                    $file,
                    $isPublic,
                    $id,
                    $uid
                ]);

                if ($old && !empty($old['certificate_file'])) {
                    delete_upload($old['certificate_file']);
                }
            } else {

                $stmt = db()->prepare('
                    UPDATE certifications SET
                        name=?,
                        issuing_organization=?,
                        issue_date=?,
                        expiration_date=?,
                        credential_id=?,
                        credential_url=?,
                        description=?,
                        is_public=?
                    WHERE id=? AND user_id=?
                ');

                $stmt->execute([
                    $name,
                    $issuingOrganization,
                    $issueDate,
                    $expirationDate,
                    $credentialId,
                    $credentialUrl,
                    $description,
                    $isPublic,
                    $id,
                    $uid
                ]);
            }

            flash('success', 'Certification updated.');
        } else {

            $stmt = db()->prepare('
                INSERT INTO certifications(
                    user_id,
                    name,
                    issuing_organization,
                    issue_date,
                    expiration_date,
                    credential_id,
                    credential_url,
                    description,
                    certificate_file,
                    is_public
                )
                VALUES(?,?,?,?,?,?,?,?,?,?)
            ');

            $stmt->execute([
                $uid,
                $name,
                $issuingOrganization,
                $issueDate,
                $expirationDate,
                $credentialId,
                $credentialUrl,
                $description,
                $file,
                $isPublic
            ]);

            flash('success', 'Certification added.');
        }
    } catch (Throwable $e) {
        flash('danger', $e->getMessage());
    }

    redirect('user/certifications.php');
}

/*
|--------------------------------------------------------------------------
| Edit Certification
|--------------------------------------------------------------------------
*/
$edit = null;

if (isset($_GET['edit'])) {

    $stmt = db()->prepare(
        'SELECT * FROM certifications WHERE id=? AND user_id=?'
    );

    $stmt->execute([
        (int)$_GET['edit'],
        $uid
    ]);

    $edit = $stmt->fetch();
}

/*
|--------------------------------------------------------------------------
| Get Certifications
|--------------------------------------------------------------------------
*/
$rows = db()->prepare('
    SELECT *
    FROM certifications
    WHERE user_id=?
    ORDER BY issue_date DESC
');

$rows->execute([$uid]);
$rows = $rows->fetchAll();

$pageTitle = 'Certifications';

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
                        Certifications & Seminars
                    </h2>

                    <p class="text-muted mb-0">
                        Showcase your certifications, seminars, training, and professional credentials.
                    </p>
                </div>
<!-- 
                <a
                    class="btn btn-outline-secondary"
                    href="<?= asset('user/certifications.php') ?>">
                    <i class="bi bi-plus-lg me-1"></i>
                    New Certification
                </a> -->

            </div>


            <!-- TWO COLUMN LAYOUT -->
            <div class="row g-4 align-items-start">

                <!-- =====================================================
                     LEFT: CERTIFICATION FORM
                ====================================================== -->
                <div class="col-lg-7">

                    <div class="card card-soft p-4">

                        <div class="d-flex justify-content-between align-items-center mb-4">

                            <div>

                                <h5 class="mb-1">
                                    <?= $edit
                                        ? 'Edit Certification'
                                        : 'Add Certification' ?>
                                </h5>

                                <p class="text-muted small mb-0">
                                    <?= $edit
                                        ? 'Update your certification information.'
                                        : 'Add certificates, seminars, or professional training.' ?>
                                </p>

                            </div>

                            <?php if ($edit): ?>

                                <a
                                    href="<?= asset('user/certifications.php') ?>"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-x-lg me-1"></i>
                                    Cancel
                                </a>

                            <?php endif; ?>

                        </div>


                        <form
                            method="post"
                            enctype="multipart/form-data">

                            <?= csrf_field() ?>

                            <input
                                type="hidden"
                                name="id"
                                value="<?= e($edit['id'] ?? '') ?>"
                                ?>


                            <div class="row g-3">

                                <!-- NAME -->
                                <div class="col-md-7">

                                    <label class="form-label">
                                        Certificate / Seminar Name
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            <i class="bi bi-award"></i>
                                        </span>

                                        <input
                                            required
                                            class="form-control"
                                            name="name"
                                            value="<?= e($edit['name'] ?? '') ?>"
                                            placeholder="e.g. Google UX Design Certificate">

                                    </div>

                                </div>


                                <!-- ORGANIZATION -->
                                <div class="col-md-5">

                                    <label class="form-label">
                                        Issuing Organization
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            <i class="bi bi-building"></i>
                                        </span>

                                        <input
                                            class="form-control"
                                            name="issuing_organization"
                                            value="<?= e($edit['issuing_organization'] ?? '') ?>"
                                            placeholder="e.g. Google">

                                    </div>

                                </div>


                                <!-- ISSUE DATE -->
                                <div class="col-md-4">

                                    <label class="form-label">
                                        Date Issued
                                    </label>

                                    <input
                                        type="date"
                                        class="form-control"
                                        name="issue_date"
                                        value="<?= e($edit['issue_date'] ?? '') ?>">

                                </div>


                                <!-- EXPIRATION -->
                                <div class="col-md-4">

                                    <label class="form-label">
                                        Expiration Date
                                    </label>

                                    <input
                                        type="date"
                                        class="form-control"
                                        name="expiration_date"
                                        value="<?= e($edit['expiration_date'] ?? '') ?>">

                                </div>


                                <!-- CREDENTIAL ID -->
                                <div class="col-md-4">

                                    <label class="form-label">
                                        Credential ID
                                    </label>

                                    <input
                                        class="form-control"
                                        name="credential_id"
                                        value="<?= e($edit['credential_id'] ?? '') ?>"
                                        placeholder="Optional">

                                </div>


                                <!-- CREDENTIAL URL -->
                                <div class="col-md-6">

                                    <label class="form-label">
                                        Credential URL
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            <i class="bi bi-link-45deg"></i>
                                        </span>

                                        <input
                                            type="url"
                                            class="form-control"
                                            name="credential_url"
                                            value="<?= e($edit['credential_url'] ?? '') ?>"
                                            placeholder="https://...">

                                    </div>

                                </div>


                                <!-- PDF -->
                                <div class="col-md-6">

                                    <label class="form-label">
                                        Certificate PDF
                                    </label>

                                    <input
                                        class="form-control"
                                        type="file"
                                        name="certificate_file"
                                        accept=".pdf">

                                    <div class="form-text">
                                        PDF only, maximum 5MB.
                                        <?php if ($edit && !empty($edit['certificate_file'])): ?>
                                            Existing file will be kept if no new file is selected.
                                        <?php endif; ?>
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
                                        rows="4"
                                        placeholder="Describe what you learned, completed, or achieved..."><?= e($edit['description'] ?? '') ?></textarea>

                                </div>


                                <!-- PUBLIC -->
                                <div class="col-12">

                                    <div class="pcert-public">

                                        <label>

                                            <input
                                                type="checkbox"
                                                name="is_public"
                                                <?= !isset($edit['is_public']) || $edit['is_public'] ? 'checked' : '' ?>>

                                            <span class="pcert-public-icon">
                                                <i class="bi bi-eye"></i>
                                            </span>

                                            <span>

                                                <strong>
                                                    Public Certification
                                                </strong>

                                                <small>
                                                    Show this certification on your public portfolio.
                                                </small>

                                            </span>

                                        </label>

                                    </div>

                                </div>

                            </div>


                            <!-- SUBMIT -->
                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">

                                <?php if ($edit): ?>

                                    <a
                                        href="<?= asset('user/certifications.php') ?>"
                                        class="btn btn-light">
                                        Cancel
                                    </a>

                                <?php endif; ?>

                                <button class="btn btn-purple px-4">

                                    <i
                                        class="bi <?= $edit ? 'bi-check-lg' : 'bi-plus-lg' ?> me-1"></i>

                                    <?= $edit
                                        ? 'Update Certification'
                                        : 'Add Certification' ?>

                                </button>

                            </div>

                        </form>

                    </div>

                </div>


                <!-- =====================================================
                     RIGHT: CERTIFICATION LIST
                ====================================================== -->
                <div class="col-lg-5">

                    <div class="pcert-panel">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div>

                                <h5 class="mb-1">
                                    My Certifications
                                </h5>

                                <p class="text-muted small mb-0">

                                    <?= count($rows) ?>

                                    <?= count($rows) === 1
                                        ? 'certification'
                                        : 'certifications' ?>

                                </p>

                            </div>


                            <div class="pcert-count">
                                <?= count($rows) ?>
                            </div>

                        </div>


                        <?php if (!$rows): ?>

                            <div class="card card-soft pcert-empty text-center p-4">

                                <div class="pcert-empty-icon">
                                    <i class="bi bi-award"></i>
                                </div>

                                <h6 class="mt-3">
                                    No certifications added yet
                                </h6>

                                <p class="text-muted small mb-0">
                                    Add your first certification using the form.
                                </p>

                            </div>

                        <?php else: ?>

                            <div class="pcert-list">

                                <?php foreach ($rows as $r): ?>

                                    <div class="pcert-card">

                                        <div class="pcert-icon">

                                            <i class="bi bi-award-fill"></i>

                                        </div>


                                        <div class="pcert-content">

                                            <div class="d-flex justify-content-between align-items-start gap-2">

                                                <div class="min-w-0">

                                                    <span class="pcert-badge">

                                                        <i class="bi bi-patch-check-fill me-1"></i>
                                                        Certification

                                                    </span>

                                                    <h6 class="pcert-title">
                                                        <?= e($r['name']) ?>
                                                    </h6>

                                                    <?php if (!empty($r['issuing_organization'])): ?>

                                                        <div class="pcert-org">

                                                            <i class="bi bi-building me-1"></i>

                                                            <?= e($r['issuing_organization']) ?>

                                                        </div>

                                                    <?php endif; ?>

                                                </div>

                                            </div>


                                            <div class="pcert-meta">

                                                <?php if (!empty($r['issue_date'])): ?>

                                                    <span>

                                                        <i class="bi bi-calendar3"></i>

                                                        Issued
                                                        <?= date(
                                                            'M Y',
                                                            strtotime($r['issue_date'])
                                                        ) ?>

                                                    </span>

                                                <?php endif; ?>


                                                <?php if (!empty($r['expiration_date'])): ?>

                                                    <span>

                                                        <i class="bi bi-calendar-event"></i>

                                                        Expires
                                                        <?= date(
                                                            'M Y',
                                                            strtotime($r['expiration_date'])
                                                        ) ?>

                                                    </span>

                                                <?php endif; ?>

                                            </div>


                                            <?php if (!empty($r['credential_id'])): ?>

                                                <div class="pcert-credential">

                                                    <i class="bi bi-hash"></i>

                                                    <?= e($r['credential_id']) ?>

                                                </div>

                                            <?php endif; ?>


                                            <?php if (!empty($r['description'])): ?>

                                                <p class="pcert-description">

                                                    <?= e($r['description']) ?>

                                                </p>

                                            <?php endif; ?>


                                            <div class="pcert-footer">

                                                <span class="pcert-visibility">

                                                    <?php if (!empty($r['is_public'])): ?>

                                                        <i class="bi bi-eye me-1"></i>
                                                        Public

                                                    <?php else: ?>

                                                        <i class="bi bi-eye-slash me-1"></i>
                                                        Private

                                                    <?php endif; ?>

                                                </span>

                                            </div>


                                            <div class="pcert-actions">

                                                <?php if (!empty($r['certificate_file'])): ?>

                                                    <a
                                                        class="btn btn-sm btn-outline-secondary"
                                                        target="_blank"
                                                        href="<?= asset($r['certificate_file']) ?>">
                                                        <i class="bi bi-file-earmark-pdf me-1"></i>
                                                        View PDF
                                                    </a>

                                                <?php endif; ?>


                                                <?php if (!empty($r['credential_url'])): ?>

                                                    <a
                                                        class="btn btn-sm btn-outline-secondary"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        href="<?= e($r['credential_url']) ?>">
                                                        <i class="bi bi-box-arrow-up-right me-1"></i>
                                                        Credential
                                                    </a>

                                                <?php endif; ?>


                                                <a
                                                    class="btn btn-sm btn-outline-secondary"
                                                    href="?edit=<?= $r['id'] ?>">
                                                    <i class="bi bi-pencil me-1"></i>
                                                    Edit
                                                </a>


                                                <a
                                                    data-confirm="Delete this certification?"
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

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>