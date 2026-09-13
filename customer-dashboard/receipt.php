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

require_once '../database/config.php';
$pdo = getConnection();

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

$stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email, u.contact_number 
                       FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.user_id 
                       WHERE o.order_id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $user_id]);
$receipt = $stmt->fetch();

if (!$receipt) {
    header("Location: orders.php");
    exit();
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
    <title>Receipt - Lumacad</title>
    <style>
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

        .receipt-success-badge h2 {
            font-family: var(--font-heading);
            color: var(--primary-teal);
            font-size: 1.5rem;
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

        .receipt-actions .btn-back {
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

        .receipt-actions .btn-back:hover {
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
            <li>
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
                <h1>Receipt</h1>
                <p>Your official receipt for this order.</p>
            </div>

            <div class="receipt-wrapper">
                <div class="receipt-container" id="receiptContent">
                    <div class="receipt-body">
                        <div class="receipt-success-badge">
                            <h2>Lumacad Wash & Fold</h2>
                            <p>Official Receipt</p>
                        </div>

                       

                        <div class="receipt-section">
                            <h3>Order Information</h3>
                            <div class="receipt-row">
                                <span class="label">Order Number</span>
                                <span class="value"><?php echo htmlspecialchars($receipt['order_number']); ?></span>
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
                    <a href="orders.php" class="btn-back">← Back to Orders</a>
                    <button onclick="printReceipt()" class="btn-print">🖨️ Print Receipt</button>
                </div>
            </div>
        </div>
    </div>

    <script>
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