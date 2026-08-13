<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_role('user');
verify_csrf();
$userId = current_user_id();

if (isset($_GET['delete'])) {
  $stmt = db()->prepare('SELECT image FROM projects WHERE id=? AND user_id=?');
  $stmt->execute([(int)$_GET['delete'], $userId]);
  $old = $stmt->fetch();
  if ($old) {
    delete_upload($old['image']);
    db()->prepare('DELETE FROM projects WHERE id=? AND user_id=?')->execute([(int)$_GET['delete'], $userId]);
    flash('success', 'Project deleted.');
  }
  redirect('user/projects.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $id = (int)($_POST['id'] ?? 0);
    $image = upload_file('image', ['jpg', 'jpeg', 'png', 'webp'], 'projects');

    if ($id) {
      $oldStmt = db()->prepare('SELECT image FROM projects WHERE id=? AND user_id=?');
      $oldStmt->execute([$id, $userId]);
      $old = $oldStmt->fetch();

      $sql = 'UPDATE projects SET project_type=?,title=?,description=?,role=?,tech_stack=?,subject_matter=?,medium=?,website_url=?,github_url=?,start_date=?,end_date=?,duration=?,date_created=?,is_featured=?,is_public=?';
      $params = [
        $_POST['project_type'],
        trim($_POST['title']),
        trim($_POST['description']),
        trim($_POST['role']),
        trim($_POST['tech_stack']),
        trim($_POST['subject_matter']),
        trim($_POST['medium']),
        trim($_POST['website_url']),
        trim($_POST['github_url']),
        $_POST['start_date'] ?: null,
        $_POST['end_date'] ?: null,
        trim($_POST['duration']),
        $_POST['date_created'] ?: null,
        (int)isset($_POST['is_featured']),
        (int)isset($_POST['is_public'])
      ];

      if ($image) {
        $sql .= ',image=?';
        $params[] = $image;
        if ($old) delete_upload($old['image']);
      }
      $sql .= ' WHERE id=? AND user_id=?';
      $params[] = $id;
      $params[] = $userId;
      db()->prepare($sql)->execute($params);
      flash('success', 'Project updated.');
    } else {
      $stmt = db()->prepare('INSERT INTO projects(user_id,project_type,title,description,role,tech_stack,subject_matter,medium,image,website_url,github_url,start_date,end_date,duration,date_created,is_featured,is_public) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
      $stmt->execute([
        $userId,
        $_POST['project_type'],
        trim($_POST['title']),
        trim($_POST['description']),
        trim($_POST['role']),
        trim($_POST['tech_stack']),
        trim($_POST['subject_matter']),
        trim($_POST['medium']),
        $image,
        trim($_POST['website_url']),
        trim($_POST['github_url']),
        $_POST['start_date'] ?: null,
        $_POST['end_date'] ?: null,
        trim($_POST['duration']),
        $_POST['date_created'] ?: null,
        (int)isset($_POST['is_featured']),
        (int)isset($_POST['is_public'])
      ]);
      flash('success', 'Project added.');
    }
  } catch (Throwable $e) {
    flash('danger', $e->getMessage());
  }
  redirect('user/projects.php');
}

$edit = null;
if (isset($_GET['edit'])) {
  $s = db()->prepare('SELECT * FROM projects WHERE id=? AND user_id=?');
  $s->execute([(int)$_GET['edit'], $userId]);
  $edit = $s->fetch();
}

$projectsStmt = db()->prepare('SELECT * FROM projects WHERE user_id=? ORDER BY created_at DESC');
$projectsStmt->execute([$userId]);
$projects = $projectsStmt->fetchAll();
$pageTitle = 'Projects';
require dirname(__DIR__) . '/includes/header.php';
?>

<div class="d-flex">
  <?php require dirname(__DIR__) . '/includes/sidebar.php'; ?>
  <main class="main-panel">
    <div class="content-wrapper dashboard-main">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold mb-1">Projects</h2>
          <p class="text-muted mb-0">Add general projects or creative artworks.</p>
        </div>
        <!-- <a class="btn btn-outline-secondary" href="<?= asset('user/projects.php') ?>"><i class="bi bi-plus-lg me-1"></i>New Project</a> -->
      </div>

      <div class="row g-4 align-items-start">

        <div class="col-lg-7">
          <div class="card card-soft p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div>
                <h5 class="mb-1"><?= $edit ? 'Edit Project' : 'Add New Project' ?></h5>
                <p class="text-muted small mb-0"><?= $edit ? 'Update the details of your project.' : 'Showcase a project or creative artwork.' ?></p>
              </div>
              <?php if ($edit): ?><a href="<?= asset('user/projects.php') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Cancel</a><?php endif; ?>
            </div>

            <form method="post" enctype="multipart/form-data">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">

              <div class="mb-4">
                <label class="form-label fw-semibold">Project Type</label>
                <?php $type = $edit['project_type'] ?? 'general'; ?>
                <div class="project-type-selector">
                  <label class="project-type-option"><input type="radio" name="project_type" value="general" data-project-type <?= $type === 'general' ? 'checked' : '' ?>><span class="project-type-content"><i class="bi bi-folder2-open"></i><span><strong>General Project</strong><small>Software, web, academic or professional projects</small></span></span></label>
                  <label class="project-type-option"><input type="radio" name="project_type" value="creative" data-project-type <?= $type === 'creative' ? 'checked' : '' ?>><span class="project-type-content"><i class="bi bi-palette"></i><span><strong>Creative / Artwork</strong><small>Design, illustration, photography and artwork</small></span></span></label>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-7"><label class="form-label">Title</label><input required class="form-control" name="title" value="<?= e($edit['title'] ?? '') ?>" placeholder="Enter project title"></div>
                <div class="col-md-5"><label class="form-label">Project Image</label><input class="form-control" type="file" name="image" accept=".jpg,.jpeg,.png,.webp"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3" placeholder="Describe your project..."><?= e($edit['description'] ?? '') ?></textarea></div>
                <div class="col-md-6" data-general-fields><label class="form-label">Role</label><input class="form-control" name="role" value="<?= e($edit['role'] ?? '') ?>" placeholder="e.g. Full Stack Developer"></div>
                <div class="col-md-6" data-general-fields><label class="form-label">Technologies / Tech Stack</label><input class="form-control" name="tech_stack" value="<?= e($edit['tech_stack'] ?? '') ?>" placeholder="PHP, MySQL, JavaScript..."></div>
                <div class="col-md-6 d-none" data-creative-fields><label class="form-label">Subject Matter</label><textarea class="form-control" name="subject_matter" rows="2" placeholder="What is the artwork about?"><?= e($edit['subject_matter'] ?? '') ?></textarea></div>
                <div class="col-md-6 d-none" data-creative-fields><label class="form-label">Medium</label><input class="form-control" name="medium" value="<?= e($edit['medium'] ?? '') ?>" placeholder="Digital, Acrylic, Photography..."></div>
                <div class="col-md-6"><label class="form-label">Website URL</label><input class="form-control" name="website_url" value="<?= e($edit['website_url'] ?? '') ?>" placeholder="https://..."></div>
                <div class="col-md-6" data-general-fields><label class="form-label">GitHub URL</label><input class="form-control" name="github_url" value="<?= e($edit['github_url'] ?? '') ?>" placeholder="https://github.com/..."></div>
                <div class="col-md-4"><label class="form-label">Start Date</label><input type="date" class="form-control" name="start_date" value="<?= e($edit['start_date'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">End Date</label><input type="date" class="form-control" name="end_date" value="<?= e($edit['end_date'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Duration</label><input class="form-control" name="duration" value="<?= e($edit['duration'] ?? '') ?>" placeholder="e.g. 2 weeks"></div>
                <div class="col-md-4"><label class="form-label">Date Created</label><input type="date" class="form-control" name="date_created" value="<?= e($edit['date_created'] ?? '') ?>"></div>
                <div class="col-md-8 d-flex align-items-end">
                  <div class="project-options"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_featured" <?= !empty($edit['is_featured']) ? 'checked' : '' ?>><span class="form-check-label">Featured</span></label><label class="form-check"><input class="form-check-input" type="checkbox" name="is_public" <?= !isset($edit['is_public']) || $edit['is_public'] ? 'checked' : '' ?>><span class="form-check-label">Public</span></label></div>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <?php if ($edit): ?><a href="<?= asset('user/projects.php') ?>" class="btn btn-light">Cancel</a><?php endif; ?>
                <button class="btn btn-purple px-4"><i class="bi <?= $edit ? 'bi-check-lg' : 'bi-plus-lg' ?> me-1"></i><?= $edit ? 'Update Project' : 'Add Project' ?></button>
              </div>
            </form>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="projects-panel">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h5 class="mb-1">My Projects</h5>
                <p class="text-muted small mb-0"><?= count($projects) ?> <?= count($projects) === 1 ? 'project' : 'projects' ?></p>
              </div>
              <div class="project-count"><?= count($projects) ?></div>
            </div>

            <?php if (!$projects): ?>
              <div class="card card-soft empty-projects text-center p-4">
                <div class="empty-project-icon"><i class="bi bi-folder2-open"></i></div>
                <h6 class="mt-3">No projects yet</h6>
                <p class="text-muted small mb-0">Add your first project using the form.</p>
              </div>
            <?php else: ?>
              <div class="projects-list">
                <?php foreach ($projects as $p): ?>
                  <div class="project-list-card">
                    <?php if ($p['image']): ?><img class="project-list-image" src="<?= asset($p['image']) ?>" alt="<?= e($p['title']) ?>"><?php else: ?><div class="project-list-image project-placeholder"><i class="bi bi-folder"></i></div><?php endif; ?>
                    <div class="project-list-content">
                      <div class="d-flex justify-content-between align-items-start gap-2">
                        <div><span class="project-type-badge"><?php if ($p['project_type'] === 'creative'): ?><i class="bi bi-palette me-1"></i><?php else: ?><i class="bi bi-folder me-1"></i><?php endif; ?><?= e(ucfirst($p['project_type'])) ?></span>
                          <h6 class="project-list-title"><?= e($p['title']) ?></h6>
                        </div>
                        <?php if ($p['is_featured']): ?><i class="bi bi-star-fill text-warning" title="Featured"></i><?php endif; ?>
                      </div>
                      <?php if ($p['description']): ?><p class="project-list-description"><?= e($p['description']) ?></p><?php endif; ?>
                      <div class="project-list-meta"><span><i class="bi bi-<?= $p['is_public'] ? 'globe2' : 'lock' ?>"></i><?= $p['is_public'] ? 'Public' : 'Private' ?></span><?php if ($p['duration']): ?><span><i class="bi bi-clock"></i><?= e($p['duration']) ?></span><?php endif; ?></div>
                      <div class="project-list-actions"><a class="btn btn-sm btn-outline-secondary" href="?edit=<?= $p['id'] ?>"><i class="bi bi-pencil me-1"></i>Edit</a><a data-confirm="Delete this project?" class="btn btn-sm btn-outline-danger" href="?delete=<?= $p['id'] ?>"><i class="bi bi-trash3 me-1"></i>Delete</a></div>
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
    const syncProjectType = () => {
      const selectedType = document.querySelector('[data-project-type]:checked')?.value;
      document.querySelectorAll('[data-general-fields]').forEach(field => field.classList.toggle('d-none', selectedType !== 'general'));
      document.querySelectorAll('[data-creative-fields]').forEach(field => field.classList.toggle('d-none', selectedType !== 'creative'));
    };
    document.querySelectorAll('[data-project-type]').forEach(radio => radio.addEventListener('change', syncProjectType));
    syncProjectType();
  });
</script>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>