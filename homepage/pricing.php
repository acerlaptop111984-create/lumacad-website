<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>Pricing - Lumacad Wash & Fold</title>
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
            <a href="pricing.php" class="active">PRICING</a>
            <a href="contact.php">CONTACT</a>
        </nav>

        <div class="header-right">
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] != 'admin'): ?>
                <a href="../customer-dashboard/dashboard.php" class="book-now">BOOK NOW</a>
            <?php else: ?>
                <a href="../login/login.php" class="book-now">BOOK NOW</a>
            <?php endif; ?>
            
            <button class="hamburger-btn" onclick="toggleSidebar()" aria-label="Toggle menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </header>

    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="sidebar-nav" id="sidebarNav">
        <div class="sidebar-header">
            <img src="../images/footerlogo-transparent.png" alt="Lumacad" class="sidebar-logo">
            <button class="sidebar-close" onclick="toggleSidebar()">✕</button>
        </div>
        
        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] != 'admin'): ?>
            <div class="sidebar-user-welcome">
                <div>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <span class="user-role">Customer</span>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="../homepage/index.php">
                        <span class="menu-text">Homepage</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li>
                    <a href="../customer-dashboard/dashboard.php">
                        <span class="menu-text">Dashboard</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li>
                    <a href="../customer-dashboard/orders.php">
                        <span class="menu-text">My Orders</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li>
                    <a href="../customer-dashboard/booking.php">
                        <span class="menu-text">New Booking</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li>
                    <a href="../customer-dashboard/reviews.php">
                        <span class="menu-text">My Reviews</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li>
                    <a href="../customer-dashboard/profile.php">
                        <span class="menu-text">My Profile</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li class="sidebar-divider"></li>
                <li>
                    <a href="../logout/logout.php" class="sidebar-logout">
                        <span class="menu-text">Logout</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
            </ul>
        <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'): ?>
            <div class="sidebar-user-welcome">
                <div>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <span class="user-role">Administrator</span>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="../homepage/index.php">
                        <span class="menu-text">Homepage</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li>
                    <a href="../admin-dashboard/dashboard.php">
                        <span class="menu-text">Admin Dashboard</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li>
                    <a href="../admin-dashboard/orders.php">
                        <span class="menu-text">Manage Orders</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li>
                    <a href="../admin-dashboard/customers.php">
                        <span class="menu-text">Customers</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li>
                    <a href="../admin-dashboard/services.php">
                        <span class="menu-text">Services</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li>
                    <a href="../admin-dashboard/reviews.php">
                        <span class="menu-text">Reviews</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li>
                    <a href="../admin-dashboard/messages.php">
                        <span class="menu-text">Messages</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li class="sidebar-divider"></li>
                <li>
                    <a href="../logout/logout.php" class="sidebar-logout"
                        <span class="menu-text">Logout</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
            </ul>
        <?php else: ?>
            <ul class="sidebar-menu">
                <li>
                    <a href="../login/login.php">
                        <span class="menu-text">Login</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
                <li>
                    <a href="../login/register.php">
                        <span class="menu-text">Register</span>
                        <span class="menu-arrow">→</span>
                    </a>
                </li>
            </ul>
        <?php endif; ?>
        
        <div class="sidebar-footer">
            <div class="sidebar-version">v1.0 | Lumacad Wash & Fold</div>
        </div>
    </div>

    <main class="fade-in pricing-main">

        <div class="pricing-header">
            <span class="pricing-label">PRICING</span>
            <h1>Simple, Fair & Transparent</h1>
            <p>We offer simple, fair, and transparent pricing for our wash and fold services. Choose the package that fits your needs and enjoy fresh, clean laundry without the hassle.</p>
        </div>

        <div class="pricing-cards-wrapper">
            <div class="pricing-cards-container">
            
                <div class="pricing-card">
                    <h3>REGULAR</h3>
                    <p class="card-subtitle">For light laundry loads</p>
                    <div class="price">₱120 <span>up to 5 kg</span></div>
                    <ul>
                        <li>Wash & Fold</li>
                        <li>Drying</li>
                        <li>Light Ironing</li>
                        <li>Laundry Detergent</li>
                    </ul>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] != 'admin'): ?>
                        <a href="../customer-dashboard/booking.php" class="btn-outline">BOOK NOW</a>
                    <?php else: ?>
                        <a href="../login/login.php" class="btn-outline">CHOOSE PLAN</a>
                    <?php endif; ?>
                </div>

                <div class="pricing-card popular">
                    <h3>FAMILY</h3>
                    <p class="card-subtitle">Perfect for everyday laundry</p>
                    <div class="price">₱200 <span>up to 10 kg</span></div>
                    <ul>
                        <li>Wash & Fold</li>
                        <li>Drying</li>
                        <li>Light Ironing</li>
                        <li>Laundry Detergent</li>
                        <li>Free Scent Booster</li>
                    </ul>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] != 'admin'): ?>
                        <a href="../customer-dashboard/booking.php" class="btn-primary">BOOK NOW</a>
                    <?php else: ?>
                        <a href="../login/login.php" class="btn-primary">CHOOSE PLAN</a>
                    <?php endif; ?>
                </div>

                <div class="pricing-card">
                    <h3>BULK</h3>
                    <p class="card-subtitle">For big loads & less worry</p>
                    <div class="price">₱350 <span>up to 20 kg</span></div>
                    <ul>
                        <li>Wash & Fold</li>
                        <li>Drying</li>
                        <li>Light Ironing</li>
                        <li>Laundry Detergent</li>
                        <li>Free Scent Booster</li>
                        <li>Priority Service</li>
                    </ul>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] != 'admin'): ?>
                        <a href="../customer-dashboard/booking.php" class="btn-outline">BOOK NOW</a>
                    <?php else: ?>
                        <a href="../login/login.php" class="btn-outline">CHOOSE PLAN</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pricing-note">
                Need more? Additional ₱20 per kg for excess weight.
            </div>
        </div>

        <div class="addons-wrapper">
            <div class="addons-container">
                <span class="addons-label">ADD-ONS</span>
                <h2>Elevate Your Laundry Care</h2>
                <p class="addons-subtitle">Add more care to your clothes.</p>

                <div class="addons-grid">
                    <div class="addon-item">
                        <h4>Premium Iron</h4>
                        <div class="addon-price">+₱30 per kg</div>
                        <p>Extra care ironing for a crisp finish.</p>
                    </div>
                    <div class="addon-item">
                        <h4>Scent Booster</h4>
                        <div class="addon-price">+₱15 per kg</div>
                        <p>Long-lasting fresh fragrance.</p>
                    </div>
                    <div class="addon-item">
                        <h4>Hypoallergenic Wash</h4>
                        <div class="addon-price">+₱20 per kg</div>
                        <p>Perfect for sensitive skin & baby clothes.</p>
                    </div>
                    <div class="addon-item">
                        <h4>Express Service</h4>
                        <div class="addon-price">+₱50 per order</div>
                        <p>Faster turnaround for urgent needs.</p>
                    </div>
                </div>

                <p class="addons-footer">All add-ons are optional and can be selected during checkout.</p>
            </div>
        </div>

        <div class="pickup-notice">
            <h2>Pickup Only</h2>
            <p>We currently do not offer delivery. Please pick up your laundry at our shop.</p>
            <p class="pickup-tagline">Easy, convenient, and hassle-free. Book online, drop off, and we'll take care of the rest.</p>
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
                <a href="index.php">Home</a>
                <a href="index.php#services">Services</a>
                <a href="index.php#about">About</a>
                <a href="pricing.php">Pricing</a>
                <a href="contact.php">Contact</a>
            </div>
            <div class="footer-services">
                <h4>Our Services</h4>
                <a href="index.php#services">Wash & Fold</a>
                <a href="index.php#services">Dry Cleaning</a>
                <a href="index.php#services">Ironing</a>
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

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarNav');
            const overlay = document.querySelector('.sidebar-overlay');
            const body = document.body;

            if (sidebar) {
                sidebar.classList.toggle('active');
            }
            if (overlay) {
                overlay.classList.toggle('active');
            }
            body.classList.toggle('sidebar-open');

            if (body.classList.contains('sidebar-open')) {
                body.style.position = 'fixed';
                body.style.width = '100%';
                body.style.top = `-${window.scrollY}px`;
            } else {
                const scrollY = body.style.top;
                body.style.position = '';
                body.style.width = '';
                body.style.top = '';
                window.scrollTo(0, parseInt(scrollY || '0') * -1);
            }
        }

        document.querySelectorAll('.sidebar-menu a').forEach(function(link) {
            link.addEventListener('click', function() {
                setTimeout(function() {
                    const sidebar = document.getElementById('sidebarNav');
                    const overlay = document.querySelector('.sidebar-overlay');
                    if (sidebar) {
                        sidebar.classList.remove('active');
                    }
                    if (overlay) {
                        overlay.classList.remove('active');
                    }
                    document.body.classList.remove('sidebar-open');
                    document.body.style.position = '';
                    document.body.style.width = '';
                    document.body.style.top = '';
                }, 150);
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const sidebar = document.getElementById('sidebarNav');
                if (sidebar && sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            }
        });
    </script>
    
    <script src="../js/script.js"></script>
</body>

</html>