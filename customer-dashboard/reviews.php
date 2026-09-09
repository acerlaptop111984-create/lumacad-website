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

$review_message = '';
$review_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $rating = $_POST['rating'];
    $review_text = trim($_POST['review_text']);
    
    if (empty($review_text)) {
        $review_error = 'Please write your review.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, customer_name, rating, review_text) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $user_name, $rating, $review_text])) {
            $review_message = 'Review submitted successfully!';
        } else {
            $review_error = 'Failed to submit review.';
        }
    }
}

if (isset($_GET['delete_review']) && isset($_GET['review_id'])) {
    $review_id = $_GET['review_id'];
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE review_id = ? AND user_id = ?");
    if ($stmt->execute([$review_id, $user_id])) {
        header("Location: reviews.php?deleted=1");
        exit();
    }
}

$stmt = $pdo->prepare("SELECT * FROM reviews WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$user_reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/customer.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>My Reviews - Lumacad</title>
</head>

<body>

    <header>
        <div class="logo">
            <img src="../images/logo-transparent.png" alt="Lumacad Wash & Fold">
        </div>
        <nav>
            <a href="../homepage/index.php">HOME</a>
            <a href="../homepage/index.php#services">SERVICES</a>
            <a href="../homepage/index.php#about">ABOUT</a>
            <a href="../homepage/pricing.php">PRICING</a>
            <a href="../homepage/contact.php">CONTACT</a>
        </nav>

        <div class="header-right">
            <a href="../homepage/index.php" class="book-now">BOOK NOW</a>
            <button class="hamburger-btn" onclick="toggleSidebar()" aria-label="Toggle menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </header>

    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="sidebar-nav" id="sidebarNav">
        <div class="sidebar-header">
            <img src="../images/footerlogo-transparent.png" alt="Lumacad" class="sidebar-logo">
            <button class="sidebar-close" onclick="toggleSidebar()">✕</button>
        </div>
        
        <div class="sidebar-user-welcome">
            <div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <span class="user-role">Customer</span>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="../customer-dashboard/dashboard.php">
                    <span class="menu-text">Dashboard</span>
                    <span class="menu-arrow">→</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                <a href="../customer-dashboard/orders.php">
                    <span class="menu-text">My Orders</span>
                    <span class="menu-arrow">→</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'booking.php' ? 'active' : ''; ?>">
                <a href="../customer-dashboard/booking.php">
                    <span class="menu-text">New Booking</span>
                    <span class="menu-arrow">→</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>">
                <a href="../customer-dashboard/reviews.php">
                    <span class="menu-text">My Reviews</span>
                    <span class="menu-arrow">→</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <a href="../customer-dashboard/profile.php">
                    <span class="menu-text">My Profile</span>
                    <span class="menu-arrow">→</span>
                </a>
            </li>
            <li>
                <a href="../homepage/index.php">
                    <span class="menu-text">Homepage</span>
                    <span class="menu-arrow">→</span>
                </a>
            </li>
            <li class="sidebar-divider"></li>
            <li>
                <a href="../logout/logout.php" class="sidebar-logout">
                    <span class="menu-text">Logout</span>
                    <span class="menu-arrow">→</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <div class="sidebar-version">v1.0 | Lumacad Wash & Fold</div>
        </div>
    </div>

    <div class="customer-main" style="margin-left: 0; padding-top: 80px;">
        <div style="max-width: 700px; margin: 0 auto; width: 100%;">
            <div class="page-header">
                <h1>My Reviews</h1>
                <p>Manage your reviews and share your experience.</p>
            </div>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">Review deleted successfully!</div>
            <?php endif; ?>

            <?php if ($review_message): ?>
                <div class="alert alert-success"><?php echo $review_message; ?></div>
            <?php endif; ?>

            <?php if ($review_error): ?>
                <div class="alert alert-error"><?php echo $review_error; ?></div>
            <?php endif; ?>

            <div class="write-review">
                <h3>Write a Review</h3>
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Rating</label>
                        <div class="star-rating">
                            <input type="radio" name="rating" value="5" id="star5"><label for="star5">★</label>
                            <input type="radio" name="rating" value="4" id="star4"><label for="star4">★</label>
                            <input type="radio" name="rating" value="3" id="star3"><label for="star3">★</label>
                            <input type="radio" name="rating" value="2" id="star2"><label for="star2">★</label>
                            <input type="radio" name="rating" value="1" id="star1" checked><label for="star1">★</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="review_text">Your Review</label>
                        <textarea id="review_text" name="review_text" rows="3" placeholder="Share your experience with us..." required></textarea>
                    </div>
                    <button type="submit" name="submit_review" class="btn-submit">Submit Review</button>
                </form>
            </div>

            <div class="my-reviews">
                <h3>Your Reviews</h3>
                <?php if (count($user_reviews) > 0): ?>
                    <?php foreach ($user_reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-rating">
                                    <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                </div>
                                <div class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></div>
                            </div>
                            <div class="review-text">"<?php echo htmlspecialchars($review['review_text']); ?>"</div>
                            <div class="review-actions">
                                <a href="edit-review.php?id=<?php echo $review['review_id']; ?>" class="btn-edit">Edit</a>
                                <a href="?delete_review=1&review_id=<?php echo $review['review_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this review?')">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-reviews">You haven't written any reviews yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarNav');
            const overlay = document.querySelector('.sidebar-overlay');
            const body = document.body;

            if (sidebar) {
                sidebar.classList.toggle('active');
            }
            if (overlay) {
                overlay.classList.toggle('active');
            }
            body.classList.toggle('sidebar-open');

            if (body.classList.contains('sidebar-open')) {
                body.style.position = 'fixed';
                body.style.width = '100%';
                body.style.top = `-${window.scrollY}px`;
            } else {
                const scrollY = body.style.top;
                body.style.position = '';
                body.style.width = '';
                body.style.top = '';
                window.scrollTo(0, parseInt(scrollY || '0') * -1);
            }
        }

        document.querySelectorAll('.sidebar-menu a').forEach(function(link) {
            link.addEventListener('click', function() {
                setTimeout(function() {
                    const sidebar = document.getElementById('sidebarNav');
                    const overlay = document.querySelector('.sidebar-overlay');
                    if (sidebar) {
                        sidebar.classList.remove('active');
                    }
                    if (overlay) {
                        overlay.classList.remove('active');
                    }
                    document.body.classList.remove('sidebar-open');
                    document.body.style.position = '';
                    document.body.style.width = '';
                    document.body.style.top = '';
                }, 150);
            });
        });
    </script>
    <script src="../js/script.js"></script>
</body>

</html>