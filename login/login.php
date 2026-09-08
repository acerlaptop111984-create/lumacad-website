<?php
session_start();
require_once '../database/config.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin-dashboard/dashboard.php");
    } else {
        header("Location: ../customer-dashboard/dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] == 'admin') {
                header("Location: ../admin-dashboard/dashboard.php");
            } else {
                header("Location: ../customer-dashboard/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>Login - Lumacad Wash & Fold</title>
</head>

<body>

    <header>
        <div class="logo">
            <img src="../images/logo-transparent.png" alt="Lumacad Wash & Fold">
        </div>
        <nav>
            <a href="../homepage/index.php">HOME</a>
            <a href="../homepage/index.php#services">SERVICES</a>
            <a href="../homepage/index.php#about">ABOUT</a>
            <a href="../homepage/pricing.php">PRICING</a>
            <a href="../homepage/contact.php">CONTACT</a>
        </nav>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] != 'admin'): ?>
            <a href="../customer-dashboard/dashboard.php" class="book-now">BOOK NOW</a>
        <?php else: ?>
            <a href="#" class="book-now" onclick="alert('Please log in first'); return false;">BOOK NOW</a>
        <?php endif; ?>
    </header>

    <main style="background: var(--teal-light); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; padding-top: 80px;">

        <div style="background: var(--primary-white); padding: 3rem 2.5rem; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 450px; width: 100%;">

            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-family: var(--font-heading); font-size: 2rem; color: var(--primary-teal); margin-bottom: 0.3rem;">Welcome Back</h1>
                <p style="color: var(--gray-medium); font-family: var(--font-body); font-size: 0.95rem;">Log in to your account</p>
            </div>

            <?php if ($error): ?>
                <div style="background: #ffe6e6; color: #cc0000; padding: 10px; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-family: var(--font-body);">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn-submit" style="margin-top: 0.5rem;">LOG IN</button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="color: var(--gray-medium); font-family: var(--font-body); font-size: 0.9rem;">
                    Don't have an account? <a href="register.php" style="color: var(--primary-teal); font-weight: 600; text-decoration: none;">Register here</a>
                </p>
                <a href="../homepage/index.php" style="color: var(--gray-medium); font-family: var(--font-body); font-size: 0.85rem; text-decoration: none; display: inline-block; margin-top: 0.5rem;">← Back to Home</a>
            </div>
        </div>

    </main>

</body>

</html>