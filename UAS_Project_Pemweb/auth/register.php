<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | SubsPilot</title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Shared design tokens (colors, typography scale) + auth page styles -->
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=20260710a">
    <link rel="stylesheet" href="../assets/css/auth.css?v=20260710a">
</head>

<body class="auth-body">

<div class="auth-silhouette">
    <i class="fa-solid fa-wallet a1"></i>
    <i class="fa-solid fa-credit-card a2"></i>
    <i class="fa-solid fa-coins a3"></i>
    <i class="fa-solid fa-chart-line a4"></i>
    <i class="fa-solid fa-calendar-days a5"></i>
    <i class="fa-solid fa-bell a6"></i>
</div>

<div class="auth-card p-4">

    <div class="text-center mb-4">
        <i class="fa-solid fa-wallet auth-logo"></i>
        <h2 class="mt-3 fw-bold">SubsPilot</h2>
        <p class="text-muted">Create your account</p>
    </div>

    <form action="register-process.php" method="POST">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-4">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <button class="btn btn-primary w-100">Register</button>

        <div class="text-center mt-3">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </form>

</div>

</body>
</html>
