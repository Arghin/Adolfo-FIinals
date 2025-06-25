<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

/* Auto-inactivate users idle > 3 days */
$conn->query("
    UPDATE users
    SET status = 'inactive'
    WHERE status = 'active'
      AND last_login IS NOT NULL
      AND last_login < NOW() - INTERVAL 3 DAY
");

$user      = $_SESSION['user'];
$isAdmin   = $user['role'] === 'admin';
$selfId    = (int)$user['id'];

/* Non-admin permission flags */
$canEdit   = $user['can_edit']   ?? 0;
$canDelete = $user['can_delete'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/in.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f7fa;
            padding: 30px;
        }

        h2 {
            text-align: center;
            color: #0d6efd;
            font-size: 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .top-bar {
            text-align: center;
            margin-bottom: 20px;
        }

        .top-bar a {
            text-decoration: none;
            color: #0d6efd;
            font-weight: 500;
            margin: 0 8px;
        }

        .action-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .action-links a {
            background: #28a745;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .action-links a:hover {
            background: #218838;
        }

        table {
            width: 95%;
            margin: auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 14px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #0d6efd;
            color: white;
        }

        tr:hover {
            background: #f1f3f5;
        }

        .action-icons a {
            margin: 0 5px;
            color: #0d6efd;
            transition: 0.2s;
        }

        .action-icons a:hover {
            color: #084cd5;
        }

        td[style*="color:green"],
        td[style*="color:red"] {
            font-weight: 600;
        }
    </style>
</head>
<body>

<h2><i data-lucide="users"></i> User List</h2>

<div class="top-bar">
    Welcome, <strong><?= htmlspecialchars($user['username']) ?></strong>
    (<?= htmlspecialchars($user['role']) ?>) |
    <a href="edit_profile.php"><i data-lucide="settings"></i> Edit Profile</a> |
    <a href="logout.php"><i data-lucide="log-out"></i> Logout</a>
</div>

<?php if ($isAdmin): ?>
    <div class="action-links">
        <a href="manage_users.php"><i data-lucide="users"></i> Manage Users</a>
        <a href="view_logs.php"><i data-lucide="scroll-text"></i> View Logs</a>
    </div>
<?php endif; ?>

<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Role</th>
        <th>Status</th>
        <th>Created At</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
<?php
$rows = $conn->query("SELECT * FROM users ORDER BY id DESC");
if ($rows->num_rows):
    while ($u = $rows->fetch_assoc()):
        $targetId   = (int)$u['id'];
        $targetRole = $u['role'];
?>
        <tr>
            <td><?= $targetId ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($targetRole) ?></td>
            <td style="color:<?= $u['status'] === 'active' ? 'green' : 'red' ?>;">
                <?= htmlspecialchars($u['status']) ?>
            </td>
            <td><?= $u['created_at'] ?? 'N/A' ?></td>
            <td class="action-icons">
                <!-- View -->
                <a href="view_user.php?id=<?= $targetId ?>" title="View">
                    <i data-lucide="eye"></i>
                </a>

                <!-- Edit -->
                <?php if (
                        $isAdmin ||
                        $targetId === $selfId ||
                        ($canEdit && $targetRole !== 'admin')
                     ): ?>
                    <a href="<?= ($isAdmin && $targetId !== $selfId)
                                   ? "edit_user.php?id=$targetId"
                                   : "edit_profile.php" ?>"
                       title="Edit">
                        <i data-lucide="pencil"></i>
                    </a>
                <?php endif; ?>

                <!-- Delete -->
                <?php if (
                        $isAdmin ||
                        ($canDelete && $targetRole !== 'admin')
                     ): ?>
                    <a href="delete_user.php?id=<?= $targetId ?>"
                       title="Delete"
                       onclick="return confirm('Delete this user?')">
                        <i data-lucide="trash-2"></i>
                    </a>
                <?php endif; ?>
            </td>
        </tr>
<?php
    endwhile;
else:
    echo '<tr><td colspan="6">No users found.</td></tr>';
endif;
?>
    </tbody>
</table>

<script>
    lucide.createIcons();
</script>
</body>
</html>
