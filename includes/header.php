<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
$pageTitle = $pageTitle ?? APP_NAME;
$flash = get_flash();
$isLoggedIn = is_logged_in();
$role = $_SESSION['role'] ?? null;
$homeUrl = $role === 'admin' ? asset('admin/index.php') : asset('user/index.php');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | Portify</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('public/css/style.css') ?>">
</head>
<body class="purple-admin-body">
<?php if ($isLoggedIn): ?>
  <div class="container-scroller">
    <nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="<?= $homeUrl ?>">
          <span class="brand-mark"><i class="mdi mdi-briefcase-variant"></i></span>
          <span>Portify</span>
        </a>
        <a class="navbar-brand brand-logo-mini" href="<?= $homeUrl ?>">P</a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center flex-grow-1">
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center border-0" type="button" data-toggle="offcanvas" aria-label="Toggle navigation">
          <i class="mdi mdi-menu"></i>
        </button>
        <div class="page-title d-none d-md-block">
          <span><?= e($pageTitle) ?></span>
        </div>
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown" aria-expanded="false">
              <span class="profile-avatar"><i class="mdi mdi-account"></i></span>
              <span class="profile-name d-none d-md-inline"><?= e($_SESSION['email'] ?? 'Account') ?></span>
              <i class="mdi mdi-chevron-down ms-1"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end navbar-dropdown shadow-sm border-0">
              <div class="dropdown-header text-center py-3">
                <div class="profile-avatar profile-avatar-lg mx-auto mb-2"><i class="mdi mdi-account"></i></div>
                <p class="mb-0 fw-bold"><?= e($_SESSION['email'] ?? 'Account') ?></p>
                <small class="text-muted text-uppercase"><?= e($role ?? 'user') ?></small>
              </div>
              <div class="dropdown-divider"></div>
              <?php if ($role === 'user'): ?>
                <a class="dropdown-item" href="<?= asset('user/profile.php') ?>"><i class="mdi mdi-account-outline me-2"></i> My Profile</a>
              <?php endif; ?>
              <a class="dropdown-item" href="<?= asset('logout.php') ?>"><i class="mdi mdi-logout me-2"></i> Sign Out</a>
            </div>
          </li>
        </ul>
      </div>
    </nav>
    <div class="container-fluid page-body-wrapper">
<?php else: ?>
  <div class="auth-wrapper">
<?php endif; ?>
<?php if ($flash): ?>
  <div class="global-flash">
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm" role="alert">
      <i class="mdi mdi-information-outline me-2"></i><?= e($flash['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
<?php endif; ?>
