<?php
require_once __DIR__ . '/config/config.php';
if (is_logged_in()) {
    redirect(($_SESSION['role'] ?? '') === 'admin' ? 'admin/index.php' : 'user/index.php');
}
redirect('login.php');
