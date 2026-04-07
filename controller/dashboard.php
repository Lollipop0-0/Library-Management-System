<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/+login.php');
    exit;
}

// Route to appropriate dashboard based on role
if ($_SESSION['is_admin']) {
    header('Location: ../admin/admin_dashboard.php');
} else {    
    header(header: 'Location: ../user/user_dashboard.php');
}
exit;
?>
