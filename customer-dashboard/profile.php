<?php
session_start();
require_once '../validation.php';  

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

$user_id = $_SESSION['user_id'];

require_once '../database/config.php';
$pdo = getConnection();

$message = '';
$error = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {

    $errors = validateProfileInput($_POST);
    
    if (empty($errors)) {
        $full_name = $_POST['full_name'];
        $contact_number = $_POST['contact_number'];
        $address = $_POST['address'];
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, contact_number = ?, address = ? WHERE user_id = ?");
            if ($stmt->execute([$full_name, $contact_number, $address, $user_id])) {
                $_SESSION['user_name'] = $full_name;
                $message = 'Profile updated successfully!';
               
                $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            } else {
                $error = 'Failed to update profile.';
                logError('Profile update failed', [
                    'user_id' => $user_id
                ]);
            }
        } catch (PDOException $e) {
            logError('Database error during profile update', [
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ]);
            $error = 'Unable to update profile. Please try again later.';
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/customer.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>My Profile - Lumacad</title>
    <style>
        .customer-main {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .customer-main .page-header {
            text-align: center;
            width: 100%;
        }
        .profile-form {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }
        .alert {
            width: 100%;
            max-width: 600px;
            text-align: center;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
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
        <div style="max-width: 600px; margin: 0 auto; width: 100%;">
            <div class="page-header">
                <h1>My Profile</h1>
                <p>Manage your personal information.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="profile-form">
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <small>Email cannot be changed.</small>
                    </div>
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>" pattern="[0-9]{10,11}" title="Please enter a valid phone number (10-11 digits)">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    <button type="submit" name="update_profile" class="btn-submit">Save Changes</button>
                </form>
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