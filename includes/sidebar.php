<?php
$sidebarRole = $_SESSION['role'] ?? 'user';
$currentPath = basename($_SERVER['PHP_SELF'] ?? '');
$nav = $sidebarRole === 'admin' ? [
    ['admin/index.php', 'Dashboard', 'mdi-view-dashboard-outline'],
    ['admin/users.php', 'Users', 'mdi-account-multiple-outline'],
] : [
    ['user/index.php', 'Dashboard', 'mdi-view-dashboard-outline'],
    ['user/profile.php', 'Profile', 'mdi-account-outline'],
    ['user/projects.php', 'Projects', 'mdi-folder-multiple-outline'],
    ['user/experience.php', 'Experience', 'mdi-briefcase-outline'],
    ['user/education.php', 'Education', 'mdi-school-outline'],
    ['user/skills.php', 'Skills', 'mdi-lightning-bolt-outline'],
    ['user/certifications.php', 'Certifications', 'mdi-certificate-outline'],
    ['user/visibility.php', 'Visibility', 'mdi-eye-outline'],
    ['user/resume.php', 'Resume / CV', 'mdi-file-document-outline'],
];
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item nav-category">Main Menu</li>
    <?php foreach ($nav as [$href, $label, $icon]): ?>
      <?php $active = $currentPath === basename($href); ?>
      <li class="nav-item <?= $active ? 'active' : '' ?>">
        <a class="nav-link" href="<?= asset($href) ?>">
          <i class="mdi <?= $icon ?> menu-icon"></i>
          <span class="menu-title"><?= e($label) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
    
    <!-- USER NAVBAR -->
    <?php if ($sidebarRole === 'user'): ?>
      <li class="nav-item nav-category mt-3">Quick Actions</li>
      <li class="nav-item">
        <a class="nav-link" target="_blank" href="<?= asset('portfolio.php?u=' . (int)current_user_id()) ?>">
          <i class="mdi mdi-open-in-new menu-icon"></i><span class="menu-title">View Portfolio</span>
        </a>
      </li>
    <?php endif; ?>
  </ul>
</nav>
