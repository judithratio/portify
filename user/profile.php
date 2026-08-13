<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_role('user');
verify_csrf();
$userId = current_user_id();
$profile = get_profile($userId);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $image = upload_file('profile_image', ['jpg', 'jpeg', 'png', 'webp'], 'profile');
    $sql = 'UPDATE profiles SET full_name=?,address=?,phone=?,professional_title=?,bio=?,professional_summary=?,github_url=?,linkedin_url=?,facebook_url=?,website_url=?';
    $params = [
      trim($_POST['full_name'] ?? ''),
      trim($_POST['address'] ?? ''),
      trim($_POST['phone'] ?? ''),
      trim($_POST['professional_title'] ?? ''),
      trim($_POST['bio'] ?? ''),
      trim($_POST['professional_summary'] ?? ''),
      trim($_POST['github_url'] ?? ''),
      trim($_POST['linkedin_url'] ?? ''),
      trim($_POST['facebook_url'] ?? ''),
      trim($_POST['website_url'] ?? '')
    ];
    if ($image) {
      $sql .= ',profile_image=?';
      $params[] = $image;
    }
    $sql .= ' WHERE user_id=?';
    $params[] = $userId;
    db()->prepare($sql)->execute($params);
    flash('success', 'Profile updated.');
    redirect('user/profile.php');
  } catch (Throwable $e) {
    flash('danger', $e->getMessage());
    redirect('user/profile.php');
  }
}
$pageTitle = 'Profile';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="d-flex"><?php require dirname(__DIR__) . '/includes/sidebar.php'; ?><main class="main-panel"><div class="content-wrapper dashboard-main">
    <h2>Personal Details</h2>
    <p class="text-muted">Add only the details you want included in your portfolio and resume.</p>
    <form method="post" enctype="multipart/form-data" class="card card-soft p-4">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-8"><label class="form-label">Full Name</label><input class="form-control" name="full_name" value="<?= e($profile['full_name']) ?>"></div>
        <div class="col-md-4"><label class="form-label">Professional Title</label><input class="form-control" name="professional_title" value="<?= e($profile['professional_title']) ?>"></div>
        <div class="col-12"><label class="form-label">Address</label><input class="form-control" name="address" value="<?= e($profile['address']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Email Address</label><input class="form-control" value="<?= e($_SESSION['email']) ?>" disabled></div>
        <div class="col-md-6"><label class="form-label">Phone Number</label><input class="form-control" name="phone" value="<?= e($profile['phone']) ?>"></div>
        <div class="col-12"><label class="form-label">Bio / About Me</label><textarea class="form-control" rows="4" name="bio"><?= e($profile['bio']) ?></textarea></div>
        <div class="col-12"><label class="form-label">Career Objective / Professional Summary</label><textarea class="form-control" rows="4" name="professional_summary"><?= e($profile['professional_summary']) ?></textarea></div>
        <div class="col-md-6"><label class="form-label">GitHub</label><input class="form-control" name="github_url" value="<?= e($profile['github_url']) ?>"></div>
        <div class="col-md-6"><label class="form-label">LinkedIn</label><input class="form-control" name="linkedin_url" value="<?= e($profile['linkedin_url']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Facebook</label><input class="form-control" name="facebook_url" value="<?= e($profile['facebook_url']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Personal Website</label><input class="form-control" name="website_url" value="<?= e($profile['website_url']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Profile Image</label><input class="form-control" type="file" name="profile_image" accept=".jpg,.jpeg,.png,.webp" data-preview="#profilePreview"></div>
        <div class="col-md-6"><?php if ($profile['profile_image']): ?><img id="profilePreview" class="profile-photo" src="<?= asset($profile['profile_image']) ?>"><?php else: ?><div id="profilePreview" class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:110px;height:110px">No photo</div><?php endif; ?></div>
      </div>
      <button class="btn btn-purple mt-4">Save Profile</button>
    </form>
  </div></main>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>