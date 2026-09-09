<?php
session_start();
require_once '../validation.php';  

if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    header("Location: ../admin-dashboard/dashboard.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

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

$message = '';
$error = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, contact_number = ?, address = ? WHERE user_id = ?");
    if ($stmt->execute([$full_name, $contact_number, $address, $user_id])) {
        $_SESSION['user_name'] = $full_name;
        $message = 'Profile updated successfully!';
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } else {
        $error = 'Failed to update profile.';
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
    <title>My Profile - Lumacad</title>
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
        .profile-form {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }
        .alert {
            width: 100%;
            max-width: 600px;
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
            <li><a href="booking.php"><span>New Booking</span></a></li>
            <li><a href="reviews.php"><span>My Reviews</span></a></li>
            <li class="active"><a href="profile.php"><span>My Profile</span></a></li>
            <li class="logout"><a href="../logout/logout.php"><span>Logout</span></a></li>
        </ul>
    </div>

    <div class="customer-main">
        <div class="page-header">
            <h1>My Profile</h1>
            <p>Manage your personal information.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="profile-form">
            <form action="" method="POST">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    <small>Email cannot be changed.</small>
                </div>
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>
                <button type="submit" name="update_profile" class="btn-submit">Save Changes</button>
            </form>
        </div>
    </div>

</body>

</html>