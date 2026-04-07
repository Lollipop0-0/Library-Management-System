<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}


// Security check - ensure user is admin
if (!$_SESSION['is_admin']) {
    header('Location: ../user/user_dashboard.php');
    exit;
}

// Read admin access log
$admin_log = file_exists('../logs/admin_log.txt') ? file('../logs/admin_log.txt') : [];
$admin_log = array_slice(array_reverse($admin_log), 0, 10); // Last 10 entries
// In approve_book, reject_book, delete cases - you already have this:
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['status' => 'error', 'message' => 'Admin access required']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Account System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f8f9fa; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .admin-info { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .welcome { font-size: 24px; color: #333; margin-bottom: 10px; }
        .admin-badge { display: inline-block; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; padding: 8px 15px; border-radius: 20px; font-size: 14px; font-weight: bold; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 36px; font-weight: bold; color: #667eea; }
        .admin-features { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
        .feature-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .feature-card h3 { color: #667eea; margin-bottom: 10px; }
        .logout-btn { background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px; }
        .logout-btn:hover { background: #c82333; }
        .access-log { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin-top: 20px; }
        .log-entry { padding: 5px 0; border-bottom: 1px solid #eee; font-family: monospace; font-size: 12px; }
        .alert { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>⚙️ Administrator Dashboard</h1>
        </div>
    </div>
    
    <div class="container">
        <div class="alert">
            ⚠️ <strong>Admin Access Detected:</strong> Your administrative activities are being logged.
        </div>
        
        <div class="admin-info">
            <div class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</div>
            <p>Email: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
            <span class="admin-badge">👑 Administrator Account</span>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number">1</div>
                    <div>Active Admin</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">📊</div>
                    <div>System Overview</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">🔒</div>
                    <div>Secure Access</div>
                </div>
            </div>
            <!-- Book Management Section -->
<div class="admin-features" style="margin-top: 30px;">
    <div class="feature-card">
        <h3>📚 Book Approval Management</h3>
        <p>Review and approve/reject books submitted by users.</p>
        <button onclick="location.href='../admin/book_management.php'" style="background: #007bff; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px;">
            Manage Books
        </button>
    </div>
</div>
            <div class="admin-features">
                <div class="feature-card">
                    <h3>👥 User Management</h3>
                    <p>Manage user accounts, permissions, and access levels.</p>
                </div>
                
                <div class="feature-card">
                    <h3>📈 System Analytics</h3>
                    <p>View detailed reports and system performance metrics.</p>
                </div>
                
                <div class="feature-card">
                    <h3>⚙️ System Settings</h3>
                    <p>Configure application settings and preferences.</p>
                </div>
                
                <div class="feature-card">
                    <h3>🔐 Security Logs</h3>
                    <p>Monitor security events and access attempts.</p>
                </div>
            </div>
            
            <div class="access-log">
                <h3>Recent Admin Access Log:</h3>
                <?php if (!empty($admin_log)): ?>
                    <?php foreach ($admin_log as $log_entry): ?>
                        <div class="log-entry"><?php echo htmlspecialchars($log_entry); ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No admin access logs found.</p>
                <?php endif; ?>
            </div>
            
            <button class="logout-btn" onclick="location.href='../auth/logout.php'">Logout</button>
        </div>
    </div>
</body>
</html> 