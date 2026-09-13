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
$receipt = null;

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
            
            $plan_prices = [
                'Regular' => 120,
                'Family'  => 200,
                'Bulk'    => 350,
            ];
            $price_per_kg = isset($plan_prices[$service_type]) ? $plan_prices[$service_type] : 0;
            $total_price = $price_per_kg * $weight;
            
            $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
            
           
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $customer_order_number = $stmt->fetchColumn() + 1;
            
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, customer_order_number, order_number, service_type, weight, total_price, pickup_address, payment_method, special_instructions, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            
            if ($stmt->execute([$user_id, $customer_order_number, $order_number, $service_type, $weight, $total_price, $pickup_address, $payment_method, $special_instructions])) {
                $new_order_id = $pdo->lastInsertId();

                
                $stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email, u.contact_number 
                                       FROM orders o 
                                       LEFT JOIN users u ON o.user_id = u.user_id 
                                       WHERE o.order_id = ?");
                $stmt->execute([$new_order_id]);
                $receipt = $stmt->fetch();

                $message = 'Booking submitted successfully!';
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

    
        .receipt-wrapper {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .receipt-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .receipt-body {
            padding: 2rem;
        }

        .receipt-success-badge {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .receipt-success-badge .check-icon {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 0.3rem;
        }

        .receipt-success-badge h2 {
            font-family: var(--font-heading);
            color: var(--primary-teal);
            font-size: 1.3rem;
            margin: 0;
        }

        .receipt-success-badge p {
            font-family: var(--font-body);
            color: var(--gray-medium);
            font-size: 0.85rem;
            margin: 0.3rem 0 0 0;
        }

        .receipt-status {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .receipt-status span {
            display: inline-block;
            padding: 0.4rem 1.5rem;
            border-radius: 20px;
            font-family: var(--font-body);
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
        }

        .receipt-status span.pending { background: #f5a623; }
        .receipt-status span.in_progress { background: #3498db; }
        .receipt-status span.ready { background: #2ecc71; }
        .receipt-status span.completed { background: #27ae60; }
        .receipt-status span.cancelled { background: #e74c3c; }

        .receipt-section {
            margin-bottom: 1.5rem;
        }

        .receipt-section h3 {
            font-family: var(--font-body);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gray-medium);
            margin-bottom: 0.8rem;
            border-bottom: 1px solid var(--teal-light);
            padding-bottom: 0.3rem;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-family: var(--font-body);
            font-size: 0.9rem;
            border-bottom: 1px dashed #eee;
        }

        .receipt-row:last-child {
            border-bottom: none;
        }

        .receipt-row .label {
            color: var(--gray-medium);
        }

        .receipt-row .value {
            color: var(--primary-black);
            font-weight: 500;
            text-align: right;
            max-width: 60%;
        }

        .receipt-total {
            background: var(--teal-light);
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-top: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .receipt-total .label {
            font-family: var(--font-body);
            font-weight: 600;
            color: var(--primary-teal);
        }

        .receipt-total .value {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-teal);
        }

        .receipt-footer {
            background: var(--teal-light);
            padding: 1.5rem 2rem;
            text-align: center;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .receipt-footer p {
            font-family: var(--font-body);
            font-size: 0.8rem;
            color: var(--gray-medium);
            margin: 0.2rem 0;
        }

        .receipt-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .receipt-actions .btn-print {
            background: var(--primary-teal);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 30px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: var(--font-body);
            cursor: pointer;
            transition: background 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .receipt-actions .btn-print:hover {
            background: var(--teal-dark);
        }

        .receipt-actions .btn-view-orders {
            background: var(--primary-white);
            color: var(--primary-teal);
            border: 2px solid var(--primary-teal);
            padding: 0.8rem 2rem;
            border-radius: 30px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: var(--font-body);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .receipt-actions .btn-view-orders:hover {
            background: var(--teal-light);
        }

        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            header,
            .sidebar-overlay,
            .sidebar-nav,
            .receipt-actions,
            .no-print,
            .page-header {
                display: none !important;
            }

            .customer-main {
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
            }

            .receipt-container {
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                margin: 0 !important;
            }

            .receipt-status span,
            .receipt-total,
            .receipt-footer {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
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

            <?php if ($error): ?>
                <div class="alert alert-error">Error: <?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($receipt): ?>
                
                <div class="receipt-wrapper">
                    <div class="receipt-container" id="receiptContent">
                        <div class="receipt-body">
                            <div class="receipt-success-badge">
                                <h2>Booking Confirmed!</h2>
                                <p><?php echo $message; ?></p>
                            </div>

                            <div class="receipt-section">
                                <h3>Order Information</h3>
                                <div class="receipt-row">
                                    <span class="label">Order Number</span>
                                    <span class="value">
                                        #<?php echo str_pad($receipt['customer_order_number'], 4, '0', STR_PAD_LEFT); ?>
                                        <br><small style="color: var(--gray-medium); font-size: 0.75rem;"><?php echo htmlspecialchars($receipt['order_number']); ?></small>
                                    </span>
                                </div>
                                <div class="receipt-row">
                                    <span class="label">Order Date</span>
                                    <span class="value"><?php echo date('F d, Y h:i A', strtotime($receipt['created_at'])); ?></span>
                                </div>
                                <div class="receipt-row">
                                    <span class="label">Service Type</span>
                                    <span class="value"><?php echo htmlspecialchars($receipt['service_type']); ?></span>
                                </div>
                                <div class="receipt-row">
                                    <span class="label">Weight</span>
                                    <span class="value"><?php echo $receipt['weight']; ?> kg</span>
                                </div>
                                <div class="receipt-row">
                                    <span class="label">Payment Method</span>
                                    <span class="value"><?php echo ucfirst($receipt['payment_method']); ?></span>
                                </div>
                            </div>

                            <div class="receipt-section">
                                <h3>Customer Information</h3>
                                <div class="receipt-row">
                                    <span class="label">Name</span>
                                    <span class="value"><?php echo htmlspecialchars($receipt['full_name']); ?></span>
                                </div>
                                <div class="receipt-row">
                                    <span class="label">Email</span>
                                    <span class="value"><?php echo htmlspecialchars($receipt['email']); ?></span>
                                </div>
                                <?php if (!empty($receipt['contact_number'])): ?>
                                <div class="receipt-row">
                                    <span class="label">Contact</span>
                                    <span class="value"><?php echo htmlspecialchars($receipt['contact_number']); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="receipt-row">
                                    <span class="label">Pickup Address</span>
                                    <span class="value"><?php echo htmlspecialchars($receipt['pickup_address']); ?></span>
                                </div>
                            </div>

                            <?php if ($receipt['special_instructions']): ?>
                            <div class="receipt-section">
                                <h3>Special Instructions</h3>
                                <div class="receipt-row">
                                    <span class="value" style="max-width: 100%; text-align: left;"><?php echo htmlspecialchars($receipt['special_instructions']); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="receipt-total">
                                <span class="label">Total Amount</span>
                                <span class="value">₱<?php echo number_format($receipt['total_price'], 2); ?></span>
                            </div>
                        </div>

                        <div class="receipt-footer">
                            <p>Thank you for choosing Lumacad Wash & Fold!</p>
                            <p>Please keep this receipt for your reference.</p>
                            <p style="margin-top: 0.5rem; font-size: 0.7rem;">Generated on: <?php echo date('F d, Y h:i A'); ?></p>
                        </div>
                    </div>

                    <div class="receipt-actions no-print">
                        <a href="orders.php" class="btn-view-orders">📋 View My Orders</a>
                        <button onclick="printReceipt()" class="btn-print">🖨️ Print Receipt</button>
                    </div>
                </div>

            <?php else: ?>
               
                <div class="booking-form">
                    <form action="" method="POST" id="bookingForm">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="service_type">Plan</label>
                                <select id="service_type" name="service_type" required>
                                    <option value="">-- Select a plan --</option>
                                    <option value="Regular" data-price="120">Regular (₱120 — up to 5 kg)</option>
                                    <option value="Family" data-price="200">Family (₱200 — up to 10 kg)</option>
                                    <option value="Bulk" data-price="350">Bulk (₱350 — up to 20 kg)</option>
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

            if (serviceSelect) {
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
            }
        });

        function printReceipt() {
            window.print();
        }

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