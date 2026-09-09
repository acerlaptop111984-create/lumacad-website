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

$customers = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC")->fetchAll();

if (isset($_GET['delete_customer']) && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role = 'customer'");
    $stmt->execute([$user_id]);
    header("Location: customers.php?deleted=1");
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
    <title>Customers - Lumacad</title>
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
                <h1> Customers</h1>
                <p>View and manage all registered customers.</p>
            </div>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success"> Customer deleted successfully!</div>
        <?php endif; ?>

        <div class="admin-table-wrap">
            <?php if (count($customers) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Orders</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <?php
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
                                $stmt->execute([$customer['user_id']]);
                                $order_count = $stmt->fetchColumn();
                            ?>
                            <tr>
                                <td>#<?php echo $customer['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><?php echo htmlspecialchars($customer['contact_number'] ?? 'N/A'); ?></td>
                                <td><?php echo $order_count; ?></td>
                                <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                <td>
                                    <a href="?delete_customer=1&user_id=<?php echo $customer['user_id']; ?>" class="action-delete" onclick="return confirm('Delete this customer? This will also delete their orders.')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">No customers yet.</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>