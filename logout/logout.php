<?php

session_start();

if (isset($_POST['confirm_logout']) && $_POST['confirm_logout'] == 'yes') {
    session_destroy();
    header("Location: ../homepage/index.php?logout=success");
    exit();
}
if (isset($_POST['cancel_logout'])) {
    $return_page = $_POST['return_page'] ?? '../homepage/index.php';
    header("Location: " . $return_page);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../homepage/index.php");
    exit();
}

$return_page = '../homepage/index.php';
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        $return_page = '../admin-dashboard/dashboard.php';
    } else {
        $return_page = '../customer-dashboard/dashboard.php';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Logout - Lumacad</title>
</head>
<body style="background: var(--teal-light); min-height: 100vh; display: flex; align-items: center; justify-content: center;">

    <div style="background: var(--primary-white); padding: 3rem 2.5rem; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 450px; width: 100%; text-align: center;">

        <h1 style="font-family: var(--font-heading); font-size: 2rem; color: var(--primary-teal); margin-bottom: 0.5rem;">Logout</h1>

        <p style="color: var(--gray-medium); font-family: var(--font-body); font-size: 1rem; margin-bottom: 2rem;">
            Are you sure you want to logout?
        </p>

        <div style="display: flex; gap: 1rem; justify-content: center;">
            <form action="" method="POST">
                <input type="hidden" name="return_page" value="<?php echo $return_page; ?>">
                <button type="submit" name="confirm_logout" value="yes" style="background: var(--primary-teal); color: white; border: none; padding: 0.8rem 2rem; border-radius: 30px; font-size: 1rem; font-weight: 600; font-family: var(--font-body); cursor: pointer; transition: background 0.3s ease;">
                    Yes, Logout
                </button>
            </form>

            <form action="" method="POST">
                <input type="hidden" name="return_page" value="<?php echo $return_page; ?>">
                <button type="submit" name="cancel_logout" value="yes" style="background: var(--primary-white); color: var(--primary-teal); border: 2px solid var(--primary-teal); padding: 0.8rem 2rem; border-radius: 30px; font-size: 1rem; font-weight: 600; font-family: var(--font-body); cursor: pointer; transition: all 0.3s ease;">
                    Cancel
                </button>
            </form>
        </div>
    </div>

</body>
</html>