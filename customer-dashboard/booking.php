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
$pdo = getConnection();

$message = '';
$error = '';

$services = $pdo->query("SELECT * FROM services ORDER BY service_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_booking'])) {
    $service_type = $_POST['service_type'];
    $weight = $_POST['weight'];
    $pickup_address = trim($_POST['pickup_address']);
    $payment_method = $_POST['payment_method'];
    $special_instructions = trim($_POST['special_instructions']);
    
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
    </style>
</head>

<body>

    <div class="customer-sidebar">
        <div class="brand">
            <img src="../images/footerlogo-transparent.png" alt="Lumacad" class="brand-logo">
        </div>
        <ul class="menu">
            <li><a href="../homepage/index.php"><span>Homepage</span></a></li>
            <li><a href="dashboard.php"><span>Dashboard</span></a></li>
            <li><a href="orders.php"><span>My Orders</span></a></li>
            <li class="active"><a href="booking.php"><span>New Booking</span></a></li>
            <li><a href="reviews.php"><span>My Reviews</span></a></li>
            <li><a href="profile.php"><span>My Profile</span></a></li>
            <li class="logout"><a href="../logout/logout.php"><span>Logout</span></a></li>
        </ul>
    </div>

    <div class="customer-main">
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

        <?php if (!$message): ?>
            <div class="booking-form">
                <form action="" method="POST">

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
    </script>

</body>

</html>