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

$reviews = $pdo->query("SELECT r.*, u.full_name FROM reviews r LEFT JOIN users u ON r.user_id = u.user_id ORDER BY r.created_at DESC")->fetchAll();

if (isset($_GET['delete_review']) && isset($_GET['review_id'])) {
    $review_id = $_GET['review_id'];
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE review_id = ?");
    $stmt->execute([$review_id]);
    header("Location: reviews.php?deleted=1");
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
    <title>Reviews - Lumacad</title>
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
                <h1> Manage Reviews</h1>
                <p>View and manage all customer reviews.</p>
            </div>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success"> Review deleted successfully!</div>
        <?php endif; ?>

        <div class="admin-table-wrap">
            <?php if (count($reviews) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td>#<?php echo $review['review_id']; ?></td>
                                <td><?php echo htmlspecialchars($review['full_name'] ?? 'Guest'); ?></td>
                                <td>
                                    <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                </td>
                                <td style="max-width: 300px;"><?php echo htmlspecialchars(substr($review['review_text'], 0, 80)) . '...'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                                <td>
                                    <a href="?delete_review=1&review_id=<?php echo $review['review_id']; ?>" class="action-delete" onclick="return confirm('Delete this review?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">No reviews yet.</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>