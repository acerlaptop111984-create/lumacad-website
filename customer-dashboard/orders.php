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

    <div class="customer-sidebar">
        <div class="brand">
            <img src="../images/footerlogo-transparent.png" alt="Lumacad" class="brand-logo">
        </div>
        <ul class="menu">
            <li><a href="dashboard.php"><span>Dashboard</span></a></li>
            <li class="active"><a href="orders.php"><span>My Orders</span></a></li>
            <li><a href="booking.php"><span>New Booking</span></a></li>
            <li><a href="reviews.php"><span>My Reviews</span></a></li>
            <li><a href="profile.php"><span>My Profile</span></a></li>
            <li class="logout"><a href="../logout/logout.php"><span>Logout</span></a></li>
        </ul>
    </div>

    <div class="customer-main">
        <div class="page-header">
            <h1> My Orders</h1>
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
                                <span> <?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                            </div>
                            <?php if ($order['status'] == 'completed'): ?>
                                <a href="reviews.php?order_id=<?php echo $order['order_id']; ?>" class="btn-review"> Leave a Review</a>
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

</body>

</html>