<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>Contact - Lumacad Wash & Fold</title>
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
        <a href="#" class="book-now">BOOK NOW</a>
    </header>

    <main class="fade-in contact-main">

        <div class="contact-header">
            <span class="contact-label">GET IN TOUCH</span>
            <h1>Contact Us</h1>
            <p>Have questions about our services? We'd love to hear from you. Reach out and we'll get back to you as soon as possible.</p>
        </div>

        <section class="contact-section">
            <div class="contact-container">
                <!-- Contact Form -->
                <div class="contact-form">
                    <form action="#" method="POST">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="Enter subject">
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" placeholder="Write your message here..." required></textarea>
                        </div>
                        <button type="submit" class="btn-submit">SEND MESSAGE</button>
                    </form>
                </div>

                <div class="contact-info">
                    <div class="info-card">
                        <div class="info-icon">📍</div>
                        <div class="info-text">
                            <h3>Visit Us  :</h3>
                            <h4>Dumaguete City, Negros Oriental</h4>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">📞</div>
                        <div class="info-text">
                            <h3>Call Us  :</h3>
                            <h4>09119988776</h4>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">📧</div>
                        <div class="info-text">
                            <h3>Email Us  :</h3>
                            <h4>lumacad.wash&fold@gmail.com</h4>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">🕐</div>
                        <div class="info-text">
                            <h3>Business Hours  :</h3>
                            <h4>Monday - Saturday</h4>
                            <h4>7:00 AM - 6:00 PM</h4>
                        </div>
                    </div>
                    <div class="info-card social">
                        <div class="info-icon">📱</div>
                        <div class="info-text">
                            <h3>Follow Us  :</h3>
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