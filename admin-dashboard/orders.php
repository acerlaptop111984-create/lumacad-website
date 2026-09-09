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

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$sql = "SELECT o.*, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.user_id";
if ($filter != 'all') {
    $sql .= " WHERE o.status = ?";
}
$sql .= " ORDER BY o.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    if ($filter != 'all') {
        $stmt->execute([$filter]);
    } else {
        $stmt->execute();
    }
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    logError('Failed to fetch orders', [
        'filter' => $filter,
        'error' => $e->getMessage()
    ]);
    $orders = [];
}

$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$inprogress_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'in_progress'")->fetchColumn();
$ready_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'ready'")->fetchColumn();
$completed_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn();
$cancelled_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    if (!empty($new_status)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            $stmt->execute([$new_status, $order_id]);
            header("Location: orders.php?updated=1");
            exit();
        } catch (PDOException $e) {
            logError('Failed to update order status', [
                'order_id' => $order_id,
                'new_status' => $new_status,
                'error' => $e->getMessage()
            ]);
            header("Location: orders.php?error=1");
            exit();
        }
    }
}

if (isset($_GET['delete_order']) && isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        header("Location: orders.php?deleted=1");
        exit();
    } catch (PDOException $e) {
        logError('Failed to delete order', [
            'order_id' => $order_id,
            'error' => $e->getMessage()
        ]);
        header("Location: orders.php?error=1");
        exit();
    }
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
    <title>Manage Orders - Lumacad</title>
    <style>
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                <h1> Manage Orders</h1>
                <p>View and manage all customer orders.</p>
            </div>
            <div>
                <a href="add-order.php" class="btn-add"> Add Order</a>
            </div>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success"> Order status updated successfully!</div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success"> Order deleted successfully!</div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"> An error occurred. Please try again.</div>
        <?php endif; ?>

        <div class="filter-tabs">
            <a href="orders.php?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">All (<?php echo $total_orders; ?>)</a>
            <a href="orders.php?filter=pending" class="filter-tab <?php echo $filter == 'pending' ? 'active' : ''; ?>">Pending (<?php echo $pending_orders; ?>)</a>
            <a href="orders.php?filter=in_progress" class="filter-tab <?php echo $filter == 'in_progress' ? 'active' : ''; ?>">In Progress (<?php echo $inprogress_orders; ?>)</a>
            <a href="orders.php?filter=ready" class="filter-tab <?php echo $filter == 'ready' ? 'active' : ''; ?>">Ready (<?php echo $ready_orders; ?>)</a>
            <a href="orders.php?filter=completed" class="filter-tab <?php echo $filter == 'completed' ? 'active' : ''; ?>">Completed (<?php echo $completed_orders; ?>)</a>
            <a href="orders.php?filter=cancelled" class="filter-tab <?php echo $filter == 'cancelled' ? 'active' : ''; ?>">Cancelled (<?php echo $cancelled_orders; ?>)</a>
        </div>

        <div class="admin-table-wrap">
            <?php if (count($orders) > 0): ?>
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
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo str_pad($order['order_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($order['full_name'] ?? 'Guest'); ?></td>
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
                                    <form action="" method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <select name="new_status" onchange="this.form.submit()" style="padding: 0.2rem 0.4rem; border-radius: 5px; border: 1px solid var(--teal-light); font-family: var(--font-body); font-size: 0.8rem;">
                                            <option value="">Change</option>
                                            <option value="pending">Pending</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="ready">Ready</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                    <a href="?delete_order=1&order_id=<?php echo $order['order_id']; ?>" class="action-delete" onclick="return confirm('Delete this order?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">No orders found.</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>