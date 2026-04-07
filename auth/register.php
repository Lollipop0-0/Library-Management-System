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
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $fullname = $conn->real_escape_string($_POST['fullname']);

    // Check if email already exists
    $check_email = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'This email is already registered.']);
        exit;
    }
    $stmt->close();

    // Set role - automatically detect if it's the admin email
    $role = ($email === 'admin@gmail.com') ? 'admin' : 'user';

    // Use prepared statement for insertion
    $sql = "INSERT INTO users (email, password, fullname, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $email, $password, $fullname, $role);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Registration successful! You can now login.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Libris Mind Verse</title>
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
            <h2>Create Account</h2>
        </div>

        <div class="auth-body">
            <form id="registerForm">
                <div class="form-group">
                    <label for="fullname">Full Name:</label>
                    <input type="text" id="fullname" name="fullname" required placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required placeholder="Create a password">
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>

            <div class="auth-links">
                <a href="../auth/login.php" class="auth-link">
                    <i class="fas fa-sign-in-alt"></i> Already have an account? Login here
                </a>
            </div>
        </div>
    </div>
    <script src="../js/register.js"></script>
    <script>
        $(document).ready(function () {
            $('#registerForm').on('submit', function (e) {
                e.preventDefault();

                // Get form values
                const formData = {
                    fullname: $('#fullname').val(),
                    email: $('#email').val(),
                    password: $('#password').val()
                };

                // Show confirmation dialog
                Swal.fire({
                    title: 'Confirm Registration',
                    html: `
                        <p>Please confirm your details:</p>
                        <br>
                        <p><strong>Name:</strong> ${formData.fullname}</p>
                        <p><strong>Email:</strong> ${formData.email}</p>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, register!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Proceed with registration
                        $.ajax({
                            url: '../auth/register.php',
                            type: 'POST',
                            data: formData,
                            dataType: 'json',
                            success: function (response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 2000
                                    }).then(function () {
                                        window.location.href = '../auth/login.php?registered=1';
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: response.message
                                    });
                                }
                            },
                            error: function () {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong! Please try again.'
                                });
                            }
                        });
                    }
                });
            });
        });
        
    </script>
</body>

</html>