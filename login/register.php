<?php
require_once '../database/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        $pdo = getConnection();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $error = "Email already registered. Please use another email.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'customer')");
            if ($stmt->execute([$full_name, $email, $hashed_password])) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Registration failed. Please try again.";
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
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>Register - Lumacad Wash & Fold</title>
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
        <a href="#" class="book-now">BOOK NOW</a>
    </header>

    <main style="background: var(--teal-light); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; padding-top: 80px;">

        <div style="background: var(--primary-white); padding: 3rem 2.5rem; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 450px; width: 100%;">

            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-family: var(--font-heading); font-size: 2rem; color: var(--primary-teal); margin-bottom: 0.3rem;">Create Account</h1>
                <p style="color: var(--gray-medium); font-family: var(--font-body); font-size: 0.95rem;">Register to start booking</p>
            </div>

            <?php if ($error): ?>
                <div style="background: #ffe6e6; color: #cc0000; padding: 10px; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-family: var(--font-body);">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="background: #e6ffe6; color: #006600; padding: 10px; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-family: var(--font-body);">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                </div>
                <button type="submit" class="btn-submit" style="margin-top: 0.5rem;">REGISTER</button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="color: var(--gray-medium); font-family: var(--font-body); font-size: 0.9rem;">
                    Already have an account? <a href="login.php" style="color: var(--primary-teal); font-weight: 600; text-decoration: none;">Log in here</a>
                </p>
                <a href="../homepage/index.php" style="color: var(--gray-medium); font-family: var(--font-body); font-size: 0.85rem; text-decoration: none; display: inline-block; margin-top: 0.5rem;">← Back to Home</a>
            </div>
        </div>

    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-brand">
                <img src="../images/footerlogo-transparent.png" alt="Lumacad Wash & Fold" class="footer-logo">
                <p>Fresh Laundry. Delivered with care.</p>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <a href="../homepage/index.php">Home</a>
                <a href="../homepage/index.php#services">Services</a>
                <a href="../homepage/index.php#about">About</a>
                <a href="../homepage/pricing.php">Pricing</a>
                <a href="../homepage/contact.php">Contact</a>
            </div>
            <div class="footer-services">
                <h4>Our Services</h4>
                <a href="#">Wash & Fold</a>
                <a href="#">Dry Cleaning</a>
                <a href="#">Ironing</a>
            </div>
            <div class="footer-hours">
                <h4>Business Hours</h4>
                <p>Monday - Saturday</p>
                <p>7:00 AM - 6:00 PM</p>
            </div>
            <div class="footer-contact">
                <h4>Contact Us</h4>
                <p>09119988776</p>
                <p>lumacad.wash&fold@gmail.com</p>
                <p>Dumaguete City, Negros Oriental</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Lumacad Wash & Fold. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="../js/script.js"></script>
</body>

</html>