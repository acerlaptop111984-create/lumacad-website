<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SESSION['role'] == 'admin') {
    header("Location: ../admin-dashboard/dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

require_once '../database/config.php';
require_once '../validation.php';

$pdo = getConnection();

$message = '';
$error = '';

try {
    $services = $pdo->query("SELECT * FROM services ORDER BY service_name")->fetchAll();
} catch (PDOException $e) {
    logError('Failed to fetch services', [
        'error' => $e->getMessage()
    ]);
    $services = [];
    $error = "Unable to load services. Please try again later.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_booking'])) {
    $errors = validateBookingInput($_POST);
    
    if (empty($errors)) {
        $service_type = trim($_POST['service_type']);
        $weight = trim($_POST['weight']);
        $pickup_address = trim($_POST['pickup_address']);
        $payment_method = trim($_POST['payment_method']);
        $special_instructions = trim($_POST['special_instructions']);
        
        try {
            $stmt = $pdo->prepare("SELECT price_per_kg FROM services WHERE service_name = ?");
            $stmt->execute([$service_type]);
            $service = $stmt->fetch();
            $price_per_kg = $service ? $service['price_per_kg'] : 0;
            $total_price = $price_per_kg * $weight;
            
            $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
            
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, service_type, weight, total_price, pickup_address, payment_method, special_instructions, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            
            if ($stmt->execute([$user_id, $order_number, $service_type, $weight, $total_price, $pickup_address, $payment_method, $special_instructions])) {
                $message = 'Booking submitted successfully! Your order number is: ' . $order_number;
            } else {
                $error = 'Failed to submit booking. Please try again.';
                logError('Booking submission failed', [
                    'user_id' => $user_id,
                    'order_number' => $order_number,
                    'service_type' => $service_type
                ]);
            }
        } catch (PDOException $e) {
            logError('Database error during booking', [
                'user_id' => $user_id,
                'service_type' => $service_type,
                'error' => $e->getMessage()
            ]);
            $error = 'Unable to process your booking. Please try again later.';
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
    <title>New Booking - Lumacad</title>
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
        .booking-form {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
        }
        .alert {
            width: 100%;
            max-width: 700px;
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
        <div style="max-width: 700px; margin: 0 auto; width: 100%;">
            <div class="page-header">
                <h1>New Booking</h1>
                <p>Schedule your laundry pickup in just a few clicks.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                    <br><a href="orders.php" style="color: var(--primary-teal); font-weight: 600; text-decoration: none;">View my orders →</a>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">Error: <?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!$message && empty($services)): ?>
                <div class="alert alert-error">No services available at the moment. Please check back later.</div>
            <?php elseif (!$message): ?>
                <div class="booking-form">
                    <form action="" method="POST" id="bookingForm">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="service_type">Service Type</label>
                                <select id="service_type" name="service_type" required>
                                    <option value="">-- Select a service --</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?php echo htmlspecialchars($service['service_name']); ?>" data-price="<?php echo $service['price_per_kg']; ?>">
                                            <?php echo htmlspecialchars($service['service_name']); ?> 
                                            (₱<?php echo number_format($service['price_per_kg'], 2); ?>/kg)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="weight">Estimated Weight (kg)</label>
                                <input type="number" id="weight" name="weight" min="1" step="1" placeholder="e.g. 5" required>
                                <small>Please estimate. Final weight will be confirmed at pickup.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="pickup_address">Pickup Address</label>
                            <textarea id="pickup_address" name="pickup_address" rows="3" placeholder="Enter your complete address (e.g. 123 Main St., Barangay, City)" required></textarea>
                            <small>Our team will pick up your laundry from this address.</small>
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="maya">Maya</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="special_instructions">Special Instructions (Optional)</label>
                            <textarea id="special_instructions" name="special_instructions" rows="2" placeholder="Any special requests? (e.g. delicate items, no perfume, etc.)"></textarea>
                        </div>

                        <div class="booking-summary">
                            <h3>Order Summary</h3>
                            <div class="summary-row">
                                <span>Service:</span>
                                <span id="summary_service">-</span>
                            </div>
                            <div class="summary-row">
                                <span>Estimated Weight:</span>
                                <span id="summary_weight">0 kg</span>
                            </div>
                            <div class="summary-row">
                                <span>Pickup Address:</span>
                                <span id="summary_address">-</span>
                            </div>
                            <div class="summary-row total">
                                <span>Estimated Total:</span>
                                <span id="summary_price">₱0.00</span>
                            </div>
                            <div class="summary-row pickup-note">
                                <span>Exact weight will be confirmed at pickup. Price may change.</span>
                            </div>
                        </div>

                        <button type="submit" name="submit_booking" class="btn-submit">Book Now</button>

                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const serviceSelect = document.getElementById('service_type');
            const weightInput = document.getElementById('weight');
            const pickupAddress = document.getElementById('pickup_address');
            const summaryService = document.getElementById('summary_service');
            const summaryWeight = document.getElementById('summary_weight');
            const summaryAddress = document.getElementById('summary_address');
            const summaryPrice = document.getElementById('summary_price');

            function updateSummary() {
                const service = serviceSelect.options[serviceSelect.selectedIndex];
                const weight = parseFloat(weightInput.value) || 0;
                const pricePerKg = service.dataset.price || 0;
                const total = pricePerKg * weight;

                summaryService.textContent = service.value || '-';
                summaryWeight.textContent = weight + ' kg';
                summaryAddress.textContent = pickupAddress.value || '-';
                summaryPrice.textContent = '₱' + total.toFixed(2);
            }

            serviceSelect.addEventListener('change', updateSummary);
            weightInput.addEventListener('input', updateSummary);
            pickupAddress.addEventListener('input', updateSummary);
        });

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