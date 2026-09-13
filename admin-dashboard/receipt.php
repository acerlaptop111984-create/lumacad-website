<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../customer-dashboard/dashboard.php");
    exit();
}

require_once '../database/config.php';
$pdo = getConnection();

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

$stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email, u.contact_number, u.address 
                       FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.user_id 
                       WHERE o.order_id = ?");
$stmt->execute([$order_id]);
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
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>Receipt #<?php echo str_pad($receipt['order_id'], 4, '0', STR_PAD_LEFT); ?> - Lumacad Admin</title>
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

            .admin-sidebar,
            .receipt-actions,
            .no-print,
            .admin-main .page-header {
                display: none !important;
            }

            .admin-main {
                margin-left: 0 !important;
                padding: 0 !important;
                background: white !important;
                width: 100% !important;
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

    <div class="admin-sidebar">
        <div class="brand">
            <img src="../images/footerlogo-transparent.png" alt="Lumacad" class="brand-logo">
        </div>
        <ul class="menu">
            <li><a href="dashboard.php"><span>Dashboard</span></a></li>
            <li><a href="orders.php"><span>Orders</span></a></li>
            <li><a href="customers.php"><span>Customers</span></a></li>
            <li><a href="services.php"><span>Services</span></a></li>
            <li><a href="reviews.php"><span>Reviews</span></a></li>
            <li><a href="messages.php"><span>Messages</span></a></li>    
            <li class="logout"><a href="../logout/logout.php"><span>Logout</span></a></li>
        </ul>
    </div>

    <div class="admin-main">
        <div class="page-header">
            <div>
                <h1>Receipt</h1>
                <p>Order #<?php echo str_pad($receipt['order_id'], 4, '0', STR_PAD_LEFT); ?> — Print for records</p>
            </div>
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
                            <span class="value"><?php echo htmlspecialchars($receipt['full_name'] ?? 'Guest'); ?></span>
                        </div>
                        <div class="receipt-row">
                            <span class="label">Email</span>
                            <span class="value"><?php echo htmlspecialchars($receipt['email'] ?? 'N/A'); ?></span>
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
                    <p>This serves as your official receipt.</p>
                    <p style="margin-top: 0.5rem; font-size: 0.7rem;">Generated on: <?php echo date('F d, Y h:i A'); ?></p>
                </div>
            </div>

            <div class="receipt-actions no-print">
                <a href="orders.php" class="btn-back">← Back to Orders</a>
                <button onclick="printReceipt()" class="btn-print">🖨️ Print Receipt</button>
            </div>
        </div>
    </div>

    <script>
        function printReceipt() {
            window.print();
        }
    </script>

</body>

</html>