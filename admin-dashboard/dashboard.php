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

$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$inprogress_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'in_progress'")->fetchColumn();
$ready_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'ready'")->fetchColumn();
$completed_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn();
$cancelled_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetchColumn();

$total_revenue = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status = 'completed'")->fetchColumn();
if ($total_revenue == null) $total_revenue = 0;

$total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();

$total_reviews = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

$recent_orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>Admin Dashboard - Lumacad</title>
</head>

<body>

    <div class="admin-sidebar">
        <div class="brand">
            <img src="../images/footerlogo-transparent.png" alt="Lumacad" class="brand-logo">
        </div>
        <ul class="menu">
            <li><a href="../homepage/index.php"><span>Homepage</span></a></li>
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
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! </p>
            </div>
            <div>
                <span class="date-display"> <?php echo date('F d, Y'); ?></span>
            </div>
        </div>

        <div class="admin-stats">
            <div class="stat-card teal">
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label"> Total Orders</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-number"><?php echo $pending_orders; ?></div>
                <div class="stat-label"> Pending</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-number"><?php echo $inprogress_orders; ?></div>
                <div class="stat-label"> In Progress</div>
            </div>
            <div class="stat-card green">
                <div class="stat-number"><?php echo $completed_orders; ?></div>
                <div class="stat-label"> Completed</div>
            </div>
        </div>

        <div class="admin-stats">
            <div class="stat-card yellow">
                <div class="stat-number"><?php echo $ready_orders; ?></div>
                <div class="stat-label"> Ready for Pickup</div>
            </div>
            <div class="stat-card red">
                <div class="stat-number"><?php echo $cancelled_orders; ?></div>
                <div class="stat-label"> Cancelled</div>
            </div>
            <div class="stat-card purple">
                <div class="stat-number">₱<?php echo number_format($total_revenue); ?></div>
                <div class="stat-label"> Total Revenue</div>
            </div>
            <div class="stat-card pink">
                <div class="stat-number"><?php echo $total_customers; ?></div>
                <div class="stat-label"> Customers</div>
            </div>
        </div>

        <div class="admin-table-wrap">
            <div class="table-header">
                <h3> Recent Orders</h3>
                <a href="orders.php" class="btn-view-all">View All →</a>
            </div>

            <?php if (count($recent_orders) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Weight</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo str_pad($order['order_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td>Customer <?php echo $order['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['service_type']); ?></td>
                                <td><?php echo $order['weight']; ?> kg</td>
                                <td>₱<?php echo number_format($order['total_price'], 2); ?></td>
                                <td>
                                    <span class="status <?php echo $order['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="../admin-dashboard/orders.php" class="action-view">View</a>
                                    <a href="../admin-dashboard/orders.php" class="action-edit">Edit</a>
                                    <a href="../admin-dashboard/orders.php" class="action-delete">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">No orders yet.</p>
            <?php endif; ?> 
        </div>
    </div>

</body>

</html>