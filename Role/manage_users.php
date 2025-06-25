<?php
session_start();
include 'db.php';

/* ───── Admin-only guard ───── */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="css/muse.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: #f4f7fa;
            padding: 30px;
            margin: 0;
        }

        h2 {
            text-align: center;
            font-size: 24px;
            color: #0d6efd;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .top-bar {
            margin-bottom: 20px;
            text-align: left;
        }

        .top-bar a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .add-btn-wrap {
            text-align: center;
            margin-bottom: 20px;
        }

        .add-btn {
            padding: 10px 18px;
            background: #198754;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .add-btn:hover {
            background: #146c43;
        }

        table {
            width: 95%;
            margin: auto;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,.08);
            overflow: hidden;
        }

        th, td {
            padding: 14px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #0d6efd;
            color: white;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .status-active {
            color: green;
            font-weight: 600;
        }

        .status-inactive {
            color: red;
            font-weight: 600;
        }

        .toggle-icon {
            color: #0d6efd;
            cursor: pointer;
            transition: 0.2s;
        }

        .toggle-icon:hover {
            color: #084cd5;
        }

        .inactive-icon {
            opacity: 0.4;
            cursor: default;
        }

    </style>
</head>
<body>

<div class="top-bar">
    <a href="index.php"><i data-lucide="arrow-left"></i> Back to Dashboard</a>
</div>

<h2><i data-lucide="users"></i> User Management</h2>

<div class="add-btn-wrap">
    <a class="add-btn" href="add_user.php"><i data-lucide="user-plus"></i> Add New User</a>
</div>

<table>
<thead>
<tr>
    <th>ID</th>
    <th>Username</th>
    <th>Role</th>
    <th>Status</th>
    <th>Created</th>
    <th>Toggle</th>
</tr>
</thead>
<tbody>
<?php
$res = $conn->query("SELECT * FROM users ORDER BY id DESC");
if ($res->num_rows):
    while ($u = $res->fetch_assoc()):
        $isActive     = $u['status'] === 'active';
        $isMainAdmin  = $u['role'] === 'admin';
        $toggleTitle  = $isActive ? 'Deactivate' : 'Activate';
?>
<tr>
    <td><?= $u['id'] ?></td>
    <td><?= htmlspecialchars($u['username']) ?></td>
    <td><?= ucfirst($u['role']) ?></td>

    <td class="<?= $isActive ? 'status-active' : 'status-inactive' ?>">
        <?= ucfirst($u['status']) ?>
    </td>

    <td><?= date('Y-m-d', strtotime($u['created_at'])) ?></td>

    <td>
        <?php if (!$isMainAdmin): ?>
            <a href="toggle_user.php?id=<?= $u['id'] ?>" title="<?= $toggleTitle ?>">
                <i data-lucide="<?= $isActive ? 'toggle-right' : 'toggle-left' ?>"
                   class="toggle-icon"></i>
            </a>
        <?php else: ?>
            <i data-lucide="shield" class="toggle-icon inactive-icon"
               title="Admin account cannot be toggled"></i>
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
