<?php
session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    header("Location: ../admin-dashboard/dashboard.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SESSION['role'] == 'admin') {
    header("Location: ../admin-dashboard/dashboard.php");
    exit();
}

$user_name = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

require_once '../database/config.php';
$pdo = getConnection();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$user_id]);
$completed_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM reviews WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$user_id]);
$user_reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/customer.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>Dashboard - Lumacad</title>
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

        <div class="header-right">
            <a href="../homepage/index.php" class="book-now">BOOK NOW</a>
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
        
        <div class="sidebar-user-welcome">
            <div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <span class="user-role">Customer</span>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="../customer-dashboard/dashboard.php">
                    <span class="menu-text">Dashboard</span>
                    <span class="menu-arrow">→</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                <a href="../customer-dashboard/orders.php">
                    <span class="menu-text">My Orders</span>
                    <span class="menu-arrow">→</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'booking.php' ? 'active' : ''; ?>">
                <a href="../customer-dashboard/booking.php">
                    <span class="menu-text">New Booking</span>
                    <span class="menu-arrow">→</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>">
                <a href="../customer-dashboard/reviews.php">
                    <span class="menu-text">My Reviews</span>
                    <span class="menu-arrow">→</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <a href="../customer-dashboard/profile.php">
                    <span class="menu-text">My Profile</span>
                    <span class="menu-arrow">→</span>
                </a>
            </li>
            <li>
                <a href="../homepage/index.php">
                    <span class="menu-text">Homepage</span>
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
        
        <div class="sidebar-footer">
            <div class="sidebar-version">v1.0 | Lumacad Wash & Fold</div>
        </div>
    </div>

    <div class="customer-main" style="margin-left: 0; padding-top: 80px;">
        <div style="max-width: 900px; margin: 0 auto; width: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap;">
                <div>
                    <h1 style="font-family: var(--font-heading); font-size: 2rem; color: var(--primary-teal);">Dashboard</h1>
                    <p style="color: var(--gray-medium); font-family: var(--font-body);">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</p>
                </div>
            </div>

            <div class="welcome-card">
                <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
                <p>Ready to do your laundry today? Let's get started!</p>
            </div>

            <div class="quick-actions">
                <a href="../customer-dashboard/booking.php" class="btn-primary">New Booking</a>
                <a href="orders.php" class="btn-outline">My Orders</a>
                <a href="profile.php" class="btn-outline">My Profile</a>
            </div>

            <div class="stats-grid">
                <div class="stat-card teal">
                    <div class="stat-number"><?php echo $total_orders; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-number"><?php echo $pending_orders; ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-number"><?php echo $completed_orders; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>

            <div class="orders-card">
                <h3>Your Recent Orders</h3>

                <?php if (count($recent_orders) > 0): ?>
                    <?php foreach ($recent_orders as $order): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <span class="order-number">#<?php echo str_pad($order['order_id'], 4, '0', STR_PAD_LEFT); ?></span>
                                <span><?php echo htmlspecialchars($order['service_type']); ?></span>
                                <span><?php echo $order['weight']; ?> kg</span>
                                <span>₱<?php echo number_format($order['total_price'], 2); ?></span>
                            </div>
                            <div>
                                <span class="order-status <?php echo $order['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                </span>
                                <span class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-orders">
                        <p>You have no orders yet.</p>
                        <a href="../customer-dashboard/booking.php">Book your first service now →</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="reviews-card" id="reviews">
                <h3>Recent Reviews</h3>
                <?php if (count($user_reviews) > 0): ?>
                    <?php foreach ($user_reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-rating">
                                    <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                </div>
                                <div class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></div>
                            </div>
                            <div class="review-text">"<?php echo htmlspecialchars($review['review_text']); ?>"</div>
                        </div>
                    <?php endforeach; ?>
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="reviews.php" style="color: var(--primary-teal); font-family: var(--font-body); text-decoration: none; font-weight: 600;">View all your reviews →</a>
                    </div>
                <?php else: ?>
                    <div class="empty-orders">
                        <p>You haven't written any reviews yet.</p>
                        <a href="reviews.php">Write your first review →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

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

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const sidebar = document.getElementById('sidebarNav');
                if (sidebar && sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            }
        });

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
    </script>
    <script src="../js/script.js"></script>
</body>

</html>