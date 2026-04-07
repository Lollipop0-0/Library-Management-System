<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Email Verified</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: Arial, sans-serif;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .success-icon {
            font-size: 60px;
            color: #16a34a;
        }
    </style>
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 text-center" style="max-width: 500px; width: 100%;">
            <div class="mb-3">
                <div class="success-icon">✅</div>
            </div>
            <h2 class="mb-3 text-success">Email Verified Successfully!</h2>
            <p class="text-muted mb-4">
                Your email address has been verified. You can now log in to your account and start using our services.
            </p>
            <a href="../admin/auth/login.php" class="btn btn-primary btn-lg w-100">
                Go to Login
            </a>
        </div>
    </div>
</body>

</html>