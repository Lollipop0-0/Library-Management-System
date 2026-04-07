<?php
    header('Content-Type: application/json');
    include_once 'EmailNotification.php';
    include '../db/db_conn.php';
  
    $email = $_POST['email'];

    if (empty($email)) {
        echo json_encode(['message' => 'error', 'error' => 'Email is required']);
        exit;
    }
    
    // Check if email exists in database
    $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['message' => 'error', 'error' => 'Email not found']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    $fullname = $user['fullname'];
    $userId = $user['id'];
    
    // Generate a random temporary password
    $temporaryPassword = bin2hex(random_bytes(4));
    
    // Hash the password before storing in database
    $hashedPassword = password_hash($temporaryPassword, PASSWORD_DEFAULT);
    
    // Update password in database
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateStmt->bind_param("si", $hashedPassword, $userId);
    
    if (!$updateStmt->execute()) {
        echo json_encode(['message' => 'error', 'error' => 'Failed to update password']);
        exit;
    }
    
    // Prepare email body
    $emailBody = <<<EOT
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Your New Password</title>
            </head>
            <body style="margin:0; padding:0; background-color:#f5f7fa; font-family: Arial, sans-serif;">
                <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fa; padding:20px;">
                    <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                        <tr>
                            <td style="background:#28a745; padding:20px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:24px;">Your New Password</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:30px;">
                            <p style="font-size:16px; color:#333333; margin-bottom:20px;">
                                Hi <strong>{$fullname}</strong>,
                            </p>
                            <p style="font-size:16px; color:#333333; line-height:1.5; margin-bottom:20px;">
                                As requested, here is your new temporary password. Please log in and change it immediately for security reasons.
                            </p>
                            <div style="background:#f8f9fa; padding:20px; border-radius:6px; text-align:center; border:2px dashed #28a745;">
                                <p style="font-size:14px; color:#666666; margin:0 0 10px 0;">Your temporary password:</p>
                                <p style="font-size:18px; font-weight:bold; color:#28a745; margin:0; font-family: monospace;">{$temporaryPassword}</p>
                            </div>
                            <p style="text-align:center; margin:30px 0;">
                                <a href="http://localhost/admin/auth/login.php" 
                                style="display:inline-block; padding:12px 24px; font-size:16px; font-weight:bold; 
                                        color:#ffffff; background:#28a745; text-decoration:none; border-radius:6px;">
                                Log In Now
                                </a>
                            </p>
                            <div style="background:#fff3cd; padding:15px; border-radius:6px; border-left:4px solid #ffc107;">
                                <p style="font-size:14px; color:#856404; margin:0;">
                                    <strong>Security Notice:</strong> For your security, please change this password immediately after logging in.
                                </p>
                            </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="background:#f0f0f0; text-align:center; padding:15px; font-size:12px; color:#888888;">
                            &copy; 2023 Your Company. All rights reserved.
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>
                </table>
            </body>
        </html>
    EOT;

    // Send email
    EmailNotification::sendEmail("FORGOT PASSWORD", $emailBody, 'NEW PASSWORD', $email);
    
    echo json_encode(['message' => 'SUCCESS']);
    exit;
?>