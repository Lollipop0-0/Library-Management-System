<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../controller/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include database connection
    include '../db/db_conn.php';

    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Updated query to include requires_password_change
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);
    // In the login success section, after setting session variables:
    if ($user['requires_password_change']) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo 'change_password_required';
        } else {
            header('Location: ../auth/change_password.php?from_forgot=1');
        }
        exit;
    }
    // Check if query was successful and has results
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_admin'] = ($user['role'] === 'admin');
            $_SESSION['requires_password_change'] = $user['requires_password_change'];

            // Log admin access
            if ($_SESSION['is_admin']) {
                file_put_contents(
                    '../logs/admin_log.txt',
                    date('Y-m-d H:i:s') . " - Admin login: " . $user['email'] . PHP_EOL,
                    FILE_APPEND
                );
            }

            // For AJAX response - check if password change is required
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                if ($user['requires_password_change']) {
                    echo 'change_password_required';
                } else {
                    echo '../controller/dashboard.php';
                }
                exit;
            } else {
                if ($user['requires_password_change']) {
                    header('Location: ../auth/change_password.php');
                } else {
                    header('Location: ../controller/dashboard.php');
                }
                exit;
            }
        }
    }

    // For AJAX response
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        if (!$result || $result->num_rows === 0) {
            echo 'email_not_found';
        } else {
            echo 'invalid_password';
        }
        exit;
    }

    $error = (!$result || $result->num_rows === 0) ? "Email not found!" : "Invalid password!";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Libris Mind Verse</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../assets/libris.png">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #d4af37;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --light-bg: #f8f9fa;
            --dark-color: #2c3e50;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-color);
        }

        .auth-container {
            background: white;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            margin: 2rem;
        }

        .auth-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .logo img {
            height: 45px;
        }

        .auth-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .auth-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #444;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 1.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .auth-links {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .auth-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .auth-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #666;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }

        .divider span {
            padding: 0 1rem;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .auth-container {
                margin: 1rem;
            }

            .auth-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="logo">
                <img src="../assets/libris.png" alt="Libris">
                <span>LIBRIS</span>
            </div>
            <h2>Login to Your Account</h2>
        </div>

        <div class="auth-body">
            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="auth-links">
                <a href="../auth/forgot_password_page.php" class="auth-link">
                    <i class="fas fa-key"></i> Forgot Password?
                </a>
                <div class="divider">
                    <span>OR</span>
                </div>
                <a href="../auth/register.php" class="auth-link">
                    <i class="fas fa-user-plus"></i> Don't have an account? Register here
                </a>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            <?php if (isset($_GET['logout'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Logged Out',
                    text: 'You have been logged out successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
            <?php endif; ?>

            $('#loginForm').on('submit', function (e) {
                e.preventDefault();

                $.ajax({
                    url: '../auth/login.php',
                    type: 'POST',
                    data: {
                        email: $('#email').val(),
                        password: $('#password').val()
                    },
                    success: function (response) {
                        if (response.includes('../controller/dashboard.php')) {
                            window.location.href = '../controller/dashboard.php';
                        } else {
                            let errorTitle = 'Login Failed';
                            let errorText = 'Invalid email or password';
                            let showRegister = false;

                            if (response === 'email_not_found') {
                                errorTitle = 'Email Not Found';
                                errorText = 'This email is not registered in our system.';
                                showRegister = true;
                            } else if (response === 'invalid_password') {
                                errorTitle = 'Invalid Password';
                                errorText = 'The password you entered is incorrect.';
                                showRegister = false;
                            }

                            Swal.fire({
                                title: errorTitle,
                                text: errorText,
                                icon: 'error',
                                showCancelButton: showRegister,
                                confirmButtonText: 'Try Again',
                                cancelButtonText: 'Register',
                                reverseButtons: true
                            }).then((result) => {
                                if (!result.isConfirmed && showRegister) {
                                    // User clicked "Register" when email not found
                                    window.location.href = '../auth/register.php';
                                }
                            });
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>