<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['is_admin']) {
    header('Location: ../admin/admin_dashboard.php');
    exit;
}

header('Location: ../user/user_feed.php');
exit;
?>