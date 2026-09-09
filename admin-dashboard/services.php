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

$services = $pdo->query("SELECT * FROM services ORDER BY service_id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_service'])) {
    $service_name = $_POST['service_name'];
    $description = $_POST['description'];
    $price_per_kg = $_POST['price_per_kg'];
    
    $stmt = $pdo->prepare("INSERT INTO services (service_name, description, price_per_kg) VALUES (?, ?, ?)");
    $stmt->execute([$service_name, $description, $price_per_kg]);
    header("Location: services.php?added=1");
    exit();
}

if (isset($_GET['delete_service']) && isset($_GET['service_id'])) {
    $service_id = $_GET['service_id'];
    $stmt = $pdo->prepare("DELETE FROM services WHERE service_id = ?");
    $stmt->execute([$service_id]);
    header("Location: services.php?deleted=1");
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
    <title>Services - Lumacad</title>
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
                <h1> Services</h1>
                <p>Manage your laundry services and pricing.</p>
            </div>
        </div>

        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success"> Service added successfully!</div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success"> Service deleted successfully!</div>
        <?php endif; ?>

        <!-- Add Service Form -->
        <div class="admin-form">
            <h3> Add New Service</h3>
            <form action="" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="service_name">Service Name</label>
                        <input type="text" id="service_name" name="service_name" placeholder="e.g. Wash & Fold" required>
                    </div>
                    <div class="form-group">
                        <label for="price_per_kg">Price per kg (₱)</label>
                        <input type="number" id="price_per_kg" name="price_per_kg" step="0.01" placeholder="e.g. 120" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="2" placeholder="Brief description of the service"></textarea>
                </div>
                <button type="submit" name="add_service" class="btn-submit">Add Service</button>
            </form>
        </div>

        <div class="admin-table-wrap" style="margin-top: 2rem;">
            <h3> Existing Services</h3>
            <?php if (count($services) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service Name</th>
                            <th>Description</th>
                            <th>Price per kg</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td>#<?php echo $service['service_id']; ?></td>
                                <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                                <td><?php echo htmlspecialchars($service['description']); ?></td>
                                <td>₱<?php echo number_format($service['price_per_kg'], 2); ?></td>
                                <td>
                                    <a href="edit-service.php?id=<?php echo $service['service_id']; ?>" class="action-edit">Edit</a>
                                    <a href="?delete_service=1&service_id=<?php echo $service['service_id']; ?>" class="action-delete" onclick="return confirm('Delete this service?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">No services added yet.</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>