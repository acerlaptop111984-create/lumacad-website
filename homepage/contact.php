<?php
session_start();
require_once '../database/config.php';

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message_text = trim($_POST['message']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Validation
    $errors = array();
    if (empty($full_name)) $errors[] = "Full name is required.";
    if (empty($email)) $errors[] = "Email address is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
    if (empty($message_text)) $errors[] = "Message is required.";
    
    if (empty($errors)) {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("INSERT INTO messages (user_id, full_name, email, subject, message, status) VALUES (?, ?, ?, ?, ?, 'unread')");
            if ($stmt->execute([$user_id, $full_name, $email, $subject, $message_text])) {
                $message = "Your message has been sent successfully! We'll get back to you soon.";
            } else {
                $error = "Failed to send message. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "Database error. Please try again.";
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

// If user is logged in, pre-fill their details
if (isset($_SESSION['user_id'])) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) {
            $user_full_name = $user['full_name'];
            $user_email = $user['email'];
        }
    } catch (PDOException $e) {
        // Ignore
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>Contact - Lumacad Wash & Fold</title>
    <style>
        .contact-main .alert {
            max-width: 900px;
            margin: 1rem auto;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            font-family: var(--font-body);
        }
        .contact-main .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .contact-main .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .contact-main .contact-section {
            padding: 0 2rem 3rem 2rem;
        }
        .contact-main .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            gap: 3rem;
            align-items: stretch;
        }
        .contact-main .contact-form {
            flex: 2;
            background: var(--primary-white);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--teal-light);
        }
        .contact-main .contact-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            justify-content: center;
        }
        .contact-main .info-card {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            background: var(--primary-white);
            padding: 0.8rem 1.2rem;
            border-radius: 10px;
            border: 1px solid var(--teal-light);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .contact-main .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        .contact-main .info-icon {
            font-size: 1.3rem;
            line-height: 1;
            flex-shrink: 0;
            width: 30px;
            text-align: center;
        }
        .contact-main .info-text {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.3rem 0.8rem;
            flex: 1;
        }
        .contact-main .info-text h3 {
            font-size: 0.85rem;
            color: var(--primary-black);
            font-family: var(--font-body);
            font-weight: 600;
            margin: 0;
        }
        .contact-main .info-text h4 {
            font-size: 0.85rem;
            color: var(--primary-teal);
            font-family: var(--font-body);
            font-weight: 400;
            margin: 0;
        }
        .contact-main .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 0.2rem;
        }
        .contact-main .social-link {
            color: var(--primary-teal);
            text-decoration: none;
            font-size: 0.85rem;
            font-family: var(--font-body);
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .contact-main .social-link:hover {
            color: var(--teal-dark);
        }
        .contact-main .form-group {
            margin-bottom: 1.2rem;
        }
        .contact-main .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary-black);
            font-family: var(--font-body);
            margin-bottom: 0.4rem;
        }
        .contact-main .form-group input,
        .contact-main .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid var(--teal-light);
            border-radius: 8px;
            font-size: 1rem;
            font-family: var(--font-body);
            transition: border-color 0.3s ease;
            background: var(--primary-white);
            color: var(--primary-black);
            box-sizing: border-box;
        }
        .contact-main .form-group input:focus,
        .contact-main .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-teal);
        }
        .contact-main .form-group input:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
        }
        .contact-main .btn-submit {
            background-color: var(--primary-teal);
            color: var(--primary-white);
            border: none;
            padding: 0.9rem 2.5rem;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            font-family: var(--font-body);
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            margin-top: 0.5rem;
        }
        .contact-main .btn-submit:hover {
            background-color: var(--teal-dark);
            transform: translateY(-3px);
            box-shadow: 0 0 20px rgba(31, 101, 112, 0.3);
        }
        .contact-main .contact-header {
            background: var(--teal-light);
            padding: 110px 2rem 0.5rem 2rem;
            text-align: center;
        }
        .contact-main .contact-header .contact-label {
            font-size: 0.85rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--primary-teal);
            font-weight: 600;
            font-family: var(--font-body);
        }
        .contact-main .contact-header h1 {
            font-size: 2.5rem;
            color: var(--primary-teal);
            font-family: var(--font-heading);
            margin: 0.3rem 0 0.2rem 0;
        }
        .contact-main .contact-header p {
            font-size: 1rem;
            color: var(--gray-medium);
            font-family: var(--font-body);
            max-width: 600px;
            margin: 0 auto 0.5rem auto;
        }
        .contact-main .form-row {
            display: flex;
            gap: 1.5rem;
        }
        .contact-main .form-row .form-group {
            flex: 1;
        }
        @media (max-width: 768px) {
            .contact-main .contact-container {
                flex-direction: column;
            }
            .contact-main .form-row {
                flex-direction: column;
                gap: 0;
            }
            .contact-main .contact-header {
                padding: 100px 1rem 0.5rem 1rem;
            }
            .contact-main .contact-section {
                padding: 0 1rem 2rem 1rem;
            }
            .contact-main .contact-form {
                padding: 1.5rem;
            }
            header {
                padding: 0.8rem 1rem;
            }
            nav {
                position: static;
                transform: none;
                gap: 0.5rem;
                flex-wrap: wrap;
                justify-content: center;
            }
            .book-now {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>

    <header>
        <div class="logo">
            <img src="../images/logo-transparent.png" alt="Lumacad Wash & Fold">
        </div>
        <nav>
            <a href="index.php">HOME</a>
            <a href="index.php#services">SERVICES</a>
            <a href="index.php#about">ABOUT</a>
            <a href="pricing.php">PRICING</a>
            <a href="contact.php" class="active">CONTACT</a>
        </nav>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] != 'admin'): ?>
            <a href="../customer-dashboard/dashboard.php" class="book-now">BOOK NOW</a>
        <?php else: ?>
            <a href="../login/login.php" class="book-now">BOOK NOW</a>
        <?php endif; ?>
    </header>

    <main class="fade-in contact-main">

        <div class="contact-header">
            <span class="contact-label">GET IN TOUCH</span>
            <h1>Contact Us</h1>
            <p>Have questions about our services? We'd love to hear from you. Reach out and we'll get back to you as soon as possible.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <section class="contact-section">
            <div class="contact-container">
                <!-- Contact Form -->
                <div class="contact-form">
                    <form action="" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" 
                                    value="<?php echo isset($user_full_name) ? htmlspecialchars($user_full_name) : ''; ?>"
                                    <?php echo isset($_SESSION['user_id']) ? 'disabled' : ''; ?> 
                                    placeholder="Enter your full name" required>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <input type="hidden" name="full_name" value="<?php echo htmlspecialchars($user_full_name); ?>">
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" 
                                    value="<?php echo isset($user_email) ? htmlspecialchars($user_email) : ''; ?>"
                                    <?php echo isset($_SESSION['user_id']) ? 'disabled' : ''; ?> 
                                    placeholder="Enter your email address" required>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email); ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="Enter subject">
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" placeholder="Write your message here..." required></textarea>
                        </div>
                        <button type="submit" name="send_message" class="btn-submit">SEND MESSAGE</button>
                    </form>
                </div>

                <div class="contact-info">
                    <div class="info-card">
                        <div class="info-icon">📍</div>
                        <div class="info-text">
                            <h3>Visit Us :</h3>
                            <h4>Dumaguete City, Negros Oriental</h4>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">📞</div>
                        <div class="info-text">
                            <h3>Call Us :</h3>
                            <h4>09119988776</h4>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">📧</div>
                        <div class="info-text">
                            <h3>Email Us :</h3>
                            <h4>lumacad.wash&fold@gmail.com</h4>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">🕐</div>
                        <div class="info-text">
                            <h3>Business Hours :</h3>
                            <h4>Monday - Saturday</h4>
                            <h4>7:00 AM - 6:00 PM</h4>
                        </div>
                    </div>
                    <div class="info-card social">
                        <div class="info-icon">📱</div>
                        <div class="info-text">
                            <h3>Follow Us :</h3>
                            <div class="social-links">
                                <a href="#" class="social-link">Facebook</a>
                                <a href="#" class="social-link">Instagram</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-brand">
                <img src="../images/footerlogo-transparent.png" alt="Lumacad Wash & Fold" class="footer-logo">
                <p>Fresh Laundry. Delivered with care.</p>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <a href="index.php">Home</a>
                <a href="index.php#services">Services</a>
                <a href="index.php#about">About</a>
                <a href="pricing.php">Pricing</a>
                <a href="contact.php">Contact</a>
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