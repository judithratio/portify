<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/google.php';
$client = googleClient();
$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="login-wrap">
  <div class="card login-card p-4 p-md-5">
    <div class="text-center">
      <div class="brand-mark mx-auto mb-3" style="background:linear-gradient(135deg,#a66cff,#7d4fd0);color:#fff;width:56px;height:56px;font-size:1.6rem;"><i class="mdi mdi-briefcase-variant"></i></div>
      <div class="display-6 fw-bold text-purple">Portify</div>
      <p class="text-muted mb-0">Build. Manage. Showcase.</p>
    </div>
    <a class="btn btn-primary w-100 py-2 mt-4" href="<?= e($client->createAuthUrl()) ?>">
      <i class="bi bi-google me-2"></i> Continue with Google
    </a>
    <div class="small text-muted mt-4 text-center">
      Use your Google account to access your Portify portfolio.
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
