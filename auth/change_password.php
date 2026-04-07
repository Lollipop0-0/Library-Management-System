<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if password change is required (from forgot password)
$requires_change = $_SESSION['requires_password_change'] ?? false;
$from_forgot = isset($_GET['from_forgot']) ? true : $requires_change;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../db/db_conn.php';
    
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];
    
    // Validate passwords
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all password fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // If it's from forgot password, skip current password verification
        if ($from_forgot) {
            // Update password and clear reset flags
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ?, requires_password_change = FALSE, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['requires_password_change'] = false;
                $success = "Password changed successfully!";
                
                // Redirect after success
                header("Location: ../controller/dashboard.php?password_changed=1");
                exit;
            } else {
                $error = "Failed to update password. Please try again.";
            }
        } else {
            // Regular password change - verify current password
            if (empty($current_password)) {
                $error = "Please enter your current password.";
            } else {
                // Verify current password
                $sql = "SELECT password FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    
                    if (password_verify($current_password, $user['password'])) {
                        // Update password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("si", $hashed_password, $user_id);
                        
                        if ($update_stmt->execute()) {
                            $success = "Password changed successfully!";
                            
                            // Redirect after success
                            header("Location: ../controller/dashboard.php?password_changed=1");
                            exit;
                        } else {
                            $error = "Failed to update password. Please try again.";
                        }
                    } else {
                        $error = "Current password is incorrect.";
                    }
                } else {
                    $error = "User not found.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $from_forgot ? 'Set New Password' : 'Change Password'; ?> - Account System</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }
        .password-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }
        .password-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .password-header h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .password-header p {
            color: #666;
            margin-bottom: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #fee;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .alert-success {
            background: #e8f5e8;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        .strength-weak { color: #e74c3c; }
        .strength-medium { color: #f39c12; }
        .strength-strong { color: #27ae60; }
    </style>
</head>
<body>
    <div class="password-container">
        <div class="password-header">
            <h2>
                <i class="fas fa-key"></i>
                <?php echo $from_forgot ? 'Set New Password' : 'Change Password'; ?>
            </h2>
            <p>
                <?php echo $from_forgot 
                    ? 'Please set your new password to secure your account.' 
                    : 'Update your password to keep your account secure.'; ?>
            </p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="passwordForm">
            <?php if (!$from_forgot): ?>
            <div class="form-group">
                <label for="current_password">
                    <i class="fas fa-lock"></i> Current Password
                </label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="new_password">
                    <i class="fas fa-lock"></i> New Password
                </label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
                <div class="password-strength" id="passwordStrength"></div>
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-lock"></i> Confirm New Password
                </label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                <div id="passwordMatch" style="font-size: 12px; margin-top: 5px;"></div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i> 
                <?php echo $from_forgot ? 'Set New Password' : 'Change Password'; ?>
            </button>
        </form>

        <?php if (!$from_forgot): ?>
        <div class="back-link">
            <a href="../controller/dashboard.php">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        $(document).ready(function() {
            // Password strength indicator
            $('#new_password').on('input', function() {
                const password = $(this).val();
                const strength = checkPasswordStrength(password);
                $('#passwordStrength').html(strength.text).attr('class', 'password-strength ' + strength.class);
            });

            // Password match indicator
            $('#confirm_password').on('input', function() {
                const newPassword = $('#new_password').val();
                const confirmPassword = $(this).val();
                
                if (confirmPassword === '') {
                    $('#passwordMatch').html('');
                } else if (newPassword === confirmPassword) {
                    $('#passwordMatch').html('<span style="color: #27ae60;">✓ Passwords match</span>');
                } else {
                    $('#passwordMatch').html('<span style="color: #e74c3c;">✗ Passwords do not match</span>');
                }
            });

            // Form submission
            $('#passwordForm').on('submit', function(e) {
                const newPassword = $('#new_password').val();
                const confirmPassword = $('#confirm_password').val();
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Password Mismatch',
                        text: 'Please make sure both passwords match.'
                    });
                    return false;
                }
                
                if (newPassword.length < 6) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Password Too Short',
                        text: 'Password must be at least 6 characters long.'
                    });
                    return false;
                }
            });

            function checkPasswordStrength(password) {
                let strength = 0;
                let text = '';
                let className = '';

                if (password.length >= 6) strength++;
                if (password.match(/[a-z]+/)) strength++;
                if (password.match(/[A-Z]+/)) strength++;
                if (password.match(/[0-9]+/)) strength++;
                if (password.match(/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/)) strength++;

                switch(strength) {
                    case 0:
                    case 1:
                    case 2:
                        text = 'Weak password';
                        className = 'strength-weak';
                        break;
                    case 3:
                        text = 'Medium password';
                        className = 'strength-medium';
                        break;
                    case 4:
                    case 5:
                        text = 'Strong password';
                        className = 'strength-strong';
                        break;
                }

                return { text: text, class: className };
            }

            // Show success message if redirected from login
            <?php if (isset($_GET['password_changed'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Password Changed!',
                    text: 'Your password has been updated successfully.',
                    timer: 3000,
                    showConfirmButton: false
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>