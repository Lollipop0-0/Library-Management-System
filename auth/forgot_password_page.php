<?php
include '../db/db_conn.php';
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../user/user_feed.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Reset Your Account</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">


    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .forgot-password-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            text-align: center;
            padding: 2rem 1rem;
            border-bottom: none;
        }

        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 0 0.25rem rgba(106, 17, 203, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            padding: 12px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.4);
        }

        .back-to-login {
            color: #6a11cb;
            text-decoration: none;
        }

        .back-to-login:hover {
            text-decoration: underline;
        }

        .steps {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
            position: relative;
        }

        .steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e9ecef;
            z-../auth/login: 1;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-../auth/login: 2;
        }

        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #6c757d;
        }

        .step.active .step-icon {
            background-color: #6a11cb;
            color: white;
        }

        .step-text {
            font-size: 0.8rem;
            color: #6c757d;
            text-align: center;
        }

        .step.active .step-text {
            color: #6a11cb;
            font-weight: bold;
        }

        .success-message {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card forgot-password-card">
                    <div class="card-header">
                        <h2><i class="fas fa-key me-2"></i>Forgot Password</h2>
                        <p class="mb-0">Enter your email to reset your password</p>
                    </div>

                    <div class="card-body p-4">
                        <!-- Steps Indicator -->
                        <div class="steps">
                            <div class="step active">
                                <div class="step-icon">1</div>
                                <div class="step-text">Enter Email</div>
                            </div>
                            <div class="step">
                                <div class="step-icon">2</div>
                                <div class="step-text">Check Email</div>
                            </div>
                            <div class="step">
                                <div class="step-icon">3</div>
                                <div class="step-text">Reset Password</div>
                            </div>
                        </div>

                        <!-- Forgot Password Form -->
                        <form id="forgotPasswordForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" name="email" id="email"
                                        placeholder="Enter your email address" required>
                                </div>
                                <div class="form-text">
                                    We'll send a password reset link to this email.
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                                </button>
                            </div>
                        </form>

                        <!-- Success Message (Initially Hidden) -->
                        <div class="success-message" id="successMessage">
                            <div class="success-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3>Check Your Email</h3>
                            <p class="text-muted">
                                We've sent a password reset link to your email address.
                                Please check your inbox and follow the instructions.
                            </p>
                            <div class="mt-4">
                                <a href="../auth/login.php" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Home
                                </a>
                                <a href="#" class="btn btn-primary">
                                    <i class="fas fa-redo me-1"></i>Resend Email
                                </a>
                            </div>
                        </div>

                        <div class="text-center">
                            <a href="../auth/login.php" class="back-to-login">
                                <i class="fas fa-arrow-left me-1"></i>Back to Home
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Additional Help Section -->
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <p class="mb-0">
                            Need help? <a href="contact.html">Contact Support</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/forgot_password.js"></script>
    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            if (!email) {
                alert('Please enter your email address');
                return;
            }
            document.getElementById('forgotPasswordForm').style.display = 'none';
            document.getElementById('successMessage').style.display = 'block';
            const steps = document.querySelectorAll('.step');
            steps[0].classList.remove('active');
            steps[1].classList.add('active');
        });      
    </script>
</body>

</html>