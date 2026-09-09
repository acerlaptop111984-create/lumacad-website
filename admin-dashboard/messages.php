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

if (isset($_GET['mark_read']) && isset($_GET['message_id'])) {
    $message_id = $_GET['message_id'];
    try {
        $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE message_id = ?");
        $stmt->execute([$message_id]);
        header("Location: messages.php?updated=1");
        exit();
    } catch (PDOException $e) {
        logError('Failed to mark message as read', [
            'message_id' => $message_id,
            'error' => $e->getMessage()
        ]);
        header("Location: messages.php?error=1");
        exit();
    }
}

if (isset($_GET['delete_message']) && isset($_GET['message_id'])) {
    $message_id = $_GET['message_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE message_id = ?");
        $stmt->execute([$message_id]);
        header("Location: messages.php?deleted=1");
        exit();
    } catch (PDOException $e) {
        logError('Failed to delete message', [
            'message_id' => $message_id,
            'error' => $e->getMessage()
        ]);
        header("Location: messages.php?error=1");
        exit();
    }
}

try {
    $messages = $pdo->query("SELECT m.*, u.full_name as user_name FROM messages m 
                              LEFT JOIN users u ON m.user_id = u.user_id 
                              ORDER BY m.created_at DESC")->fetchAll();
    
    $unread_count = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'unread'")->fetchColumn();
} catch (PDOException $e) {
    logError('Failed to fetch messages', [
        'error' => $e->getMessage()
    ]);
    $messages = [];
    $unread_count = 0;
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
    <title>Contact Messages - Lumacad</title>
    <style>
        .message-unread {
            background: #f0f7ff;
            font-weight: 600;
        }
        .message-read {
            opacity: 0.8;
        }
        .status-badge {
            padding: 0.2rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-badge.unread {
            background: #f5a623;
            color: white;
        }
        .status-badge.read {
            background: #27ae60;
            color: white;
        }
        .status-badge.replied {
            background: #3498db;
            color: white;
        }
        .message-preview {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .action-mark-read {
            color: var(--primary-teal);
            text-decoration: none;
            margin-right: 0.5rem;
        }
        .action-mark-read:hover {
            text-decoration: underline;
        }
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
                <h1>Contact Messages</h1>
                <p>View and manage all contact form submissions.</p>
            </div>
            <div>
                <span style="background: var(--primary-teal); color: white; padding: 0.3rem 1rem; border-radius: 20px; font-size: 0.9rem;">
                    <?php echo $unread_count; ?> unread
                </span>
            </div>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">Message marked as read!</div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Message deleted successfully!</div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">An error occurred. Please try again.</div>
        <?php endif; ?>

        <div class="admin-table-wrap">
            <?php if (count($messages) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                            <tr class="<?php echo $msg['status'] == 'unread' ? 'message-unread' : 'message-read'; ?>">
                                <td>#<?php echo $msg['message_id']; ?></td>
                                <td>
                                    <?php if ($msg['user_id']): ?>
                                        <a href="customers.php" style="color: var(--primary-teal); text-decoration: none;">
                                            User #<?php echo $msg['user_id']; ?>
                                        </a>
                                    <?php else: ?>
                                        Guest
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($msg['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                <td><?php echo htmlspecialchars($msg['subject'] ?? 'N/A'); ?></td>
                                <td class="message-preview"><?php echo htmlspecialchars(substr($msg['message'], 0, 50)) . '...'; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $msg['status']; ?>">
                                        <?php echo ucfirst($msg['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?></td>
                                <td>
                                    <?php if ($msg['status'] == 'unread'): ?>
                                        <a href="?mark_read=1&message_id=<?php echo $msg['message_id']; ?>" class="action-mark-read">Mark Read</a>
                                    <?php endif; ?>
                                    <a href="?delete_message=1&message_id=<?php echo $msg['message_id']; ?>" class="action-delete" onclick="return confirm('Delete this message?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">No messages yet.</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>