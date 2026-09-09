<?php
session_start();
require_once '../validation.php';  

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SESSION['role'] == 'admin') {
    header("Location: ../admin-dashboard/dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

require_once '../database/config.php';
$pdo = getConnection();

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$sql = "SELECT * FROM orders WHERE user_id = ?";
if ($filter == 'pending') {
    $sql .= " AND status = 'pending'";
} elseif ($filter == 'in_progress') {
    $sql .= " AND status = 'in_progress'";
} elseif ($filter == 'ready') {
    $sql .= " AND status = 'ready'";
} elseif ($filter == 'completed') {
    $sql .= " AND status = 'completed'";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'in_progress'");
$stmt->execute([$user_id]);
$inprogress_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'ready'");
$stmt->execute([$user_id]);
$ready_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$user_id]);
$completed_orders = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/customer.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>My Orders - Lumacad</title>
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
            <div class="page-header">
                <h1>My Orders</h1>
                <p>View and track all your laundry orders.</p>
            </div>

            <div class="filter-tabs">
                <a href="orders.php?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">All (<?php echo $total_orders; ?>)</a>
                <a href="orders.php?filter=pending" class="filter-tab <?php echo $filter == 'pending' ? 'active' : ''; ?>">Pending (<?php echo $pending_orders; ?>)</a>
                <a href="orders.php?filter=in_progress" class="filter-tab <?php echo $filter == 'in_progress' ? 'active' : ''; ?>">In Progress (<?php echo $inprogress_orders; ?>)</a>
                <a href="orders.php?filter=ready" class="filter-tab <?php echo $filter == 'ready' ? 'active' : ''; ?>">Ready (<?php echo $ready_orders; ?>)</a>
                <a href="orders.php?filter=completed" class="filter-tab <?php echo $filter == 'completed' ? 'active' : ''; ?>">Completed (<?php echo $completed_orders; ?>)</a>
            </div>

            <div class="orders-list">
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-card-header">
                                <div>
                                    <span class="order-number">#<?php echo str_pad($order['order_id'], 4, '0', STR_PAD_LEFT); ?></span>
                                    <span class="order-service"><?php echo htmlspecialchars($order['service_type']); ?></span>
                                </div>
                                <span class="order-status <?php echo $order['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                </span>
                            </div>
                            <div class="order-card-body">
                                <div class="order-details">
                                    <span>Weight: <?php echo $order['weight']; ?> kg</span>
                                    <span>Total: ₱<?php echo number_format($order['total_price'], 2); ?></span>
                                    <span><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <?php if ($order['status'] == 'completed'): ?>
                                    <a href="reviews.php?order_id=<?php echo $order['order_id']; ?>" class="btn-review">Leave a Review</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-orders">
                        <p>You have no orders.</p>
                        <a href="../customer-dashboard/booking.php">Book your first service now →</a>
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

